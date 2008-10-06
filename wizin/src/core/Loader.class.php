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
        public function load( $output = true )
        {
            $this->_include();
            $this->_init();
            $this->_callController( $output );
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
            // renderer class
            require WIZ_TRUST_PATH . '/wizin/src/core/Renderer.class.php';
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
            // check client
            $user =& Wizin_User::getSingleton();
            $user->checkClient();
            // set mb_internal_encoding
            mb_internal_encoding( WIZ_SITE_ENCODING );
            // set mbstring.http_input
            ini_set( 'mbstring.http_input', 'pass' );
            // set mbstring.http_output
            ini_set( 'mbstring.http_output', 'pass' );
        }

        /**
         * Call controller, and run main process.
         *
         */
        protected function _callController( $output )
        {
            $controller =& Wizin_Core_Controller::getSingleton();
            $controller->execute( $output );
        }
    }
}