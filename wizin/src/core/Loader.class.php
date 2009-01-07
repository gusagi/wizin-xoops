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
    class Wizin_Core_Loader extends Wizin_StdClass
    {
        /**
         * Site load function.
         *
         */
        public function load( $execute = true )
        {
            $this->_include( $execute );
            $this->_define();
            $this->_init();
            if ( $execute ) {
                $this->_callApplication();
            }
        }

        /**
         * Inculude require files.
         *
         */
        protected function _include( $execute )
        {
            $srcRootPath = dirname( dirname(__FILE__) );
            // user class
            require $srcRootPath . '/Wizin_User.class.php';
            // controller class
            require $srcRootPath . '/core/App.class.php';
            // session class
            require $srcRootPath . '/core/Session.class.php';
            if ( $execute ) {
                // renderer class
                require $srcRootPath . '/core/Renderer.class.php';
            }
            // controller class
            require $srcRootPath . '/core/Controller.class.php';
            if ( $execute ) {
                // view class
                require $srcRootPath . '/core/View.class.php';
                // filter class
                require $srcRootPath . '/Wizin_Filter.php';
            }
        }

        /**
         * define constant
         *
         */
        protected function _define()
        {
            // define default application
            if ( ! defined('WIZIN_DEFAULT_APP') ) {
                define( 'WIZIN_DEFAULT_APP', 'Wizin_Core_App' );
            }
            // define default controller
            if ( ! defined('WIZIN_DEFAULT_CONTROLLER') ) {
                define( 'WIZIN_DEFAULT_CONTROLLER', 'Wizin_Core_Controller' );
            }
            // define default view
            if ( ! defined('WIZIN_DEFAULT_VIEW') ) {
                define( 'WIZIN_DEFAULT_VIEW', 'Wizin_Core_View' );
            }
            // define system encoding
            if ( ! defined('WIZ_SYS_ENCODING') ) {
                define( 'WIZ_SYS_ENCODING', mb_internal_encoding() );
            }
            // define site hostname
            if ( ! defined('WIZ_SITE_HOST') ) {
                define( 'WIZ_SITE_HOST', 'wizin.jp' );
            }
            // define site root path
            if ( ! defined('WIZ_SITE_ROOT') ) {
                define( 'WIZ_SITE_ROOT', WIZ_SYS_ROOT_PATH . '/wizin' );
            }
            // define site title
            if ( ! defined('WIZ_SITE_TITLE') ) {
                define( 'WIZ_SITE_TITLE', 'Wizin' );
            }
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
            mb_internal_encoding( WIZ_SYS_ENCODING );
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
            set_include_path( get_include_path() . PATH_SEPARATOR . WIZIN_PEAR_DIR );
        }

        /**
         * Call application, and run main process.
         *
         */
        protected function _callApplication()
        {
            $app =& call_user_func( array(WIZIN_DEFAULT_APP, 'getSingleton') );
            $app->execute();
        }
    }
}
