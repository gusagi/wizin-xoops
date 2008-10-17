<?php
/**
 * Wizin framework core loader class
 *
 * PHP Version 5.2 or Upper version
 *
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link http://www.gusagi.com/
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 *
 */

if ( ! class_exists('Wizin_Core_Loader') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    /**
     * Loader class
     *
     */
    class Wizin_Core_Loader
    {
        /**
         * Site load function.
         *
         */
        public function load( $execute = true )
        {
            $this->_include();
            $this->_init();
            if ( $execute ) {
                $this->_callController();
            }
        }

        /**
         * Inculude require files.
         *
         */
        protected function _include()
        {
            // wizin framework root class
            require WIZ_TRUST_PATH . '/wizin/src/Wizin.class.php';
            // user class
            require WIZ_TRUST_PATH . '/wizin/src/Wizin_User.class.php';
            // controller class
            require WIZ_TRUST_PATH . '/wizin/src/core/Controller.class.php';
            // session class
            require WIZ_TRUST_PATH . '/wizin/src/core/Session.class.php';
            // renderer class
            require WIZ_TRUST_PATH . '/wizin/src/core/Renderer.class.php';
            // page class
            require WIZ_TRUST_PATH . '/wizin/src/core/Page.class.php';
            // filter class
            require WIZ_TRUST_PATH . '/wizin/src/Wizin_Filter.php';
        }

        /**
         * Run site init process.
         *
         */
        protected function _init()
        {
            // start output buffering
            ob_start();
            // get 'Wizin' singleton object
            $wizin =& Wizin::getSingleton();
            // set mb_internal_encoding
            mb_internal_encoding( WIZ_SYSTEM_ENCODING );
            // set mbstring.http_input
            ini_set( 'mbstring.http_input', 'pass' );
            // set mbstring.http_output
            ini_set( 'mbstring.http_output', 'pass' );
            // set timezone
            $timezone = date_default_timezone_get();
            if ( empty($timezone) ) {
                $timezone = 'Asia/Tokyo';
            }
            @ date_default_timezone_set( $timezone );
            // set PEAR path
            set_include_path( WIZIN_PEAR_DIR . PATH_SEPARATOR . get_include_path() );
        }

        /**
         * Call controller, and run main process.
         *
         */
        protected function _callController()
        {
            $controller =& Wizin_Core_Controller::getSingleton();
            $controller->execute();
        }
    }
}