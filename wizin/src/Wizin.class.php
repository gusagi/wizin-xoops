<?php
/**
 * Wizin framework root object class
 *
 * PHP Versions 4
 *
 * @package  giftbox.in
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! defined('WIZIN_ROOT_PATH') ) {
    define( 'WIZIN_ROOT_PATH', dirname(dirname(__FILE__)) );
    //set_include_path( get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)) );
    require_once WIZIN_ROOT_PATH . '/src/Wizin_StdClass.php';
    require_once WIZIN_ROOT_PATH . '/src/Wizin_Ref.php';
    require_once WIZIN_ROOT_PATH . '/src/Wizin_Util.class.php';

    if ( class_exists('Wizin_StdClass') ) {
        /**
         * Wizin framework root object class
         *
         * @access public
         *
         */
        class Wizin extends Wizin_StdClass
        {
            /**
             * Wizin class constructor
             *
             * @access public
             */
            function __construct()
            {
                //
                // REQUEST_URI
                $requestUri = getenv( 'REQUEST_URI' );
                if ( empty($requestUri) ) {
                    // set path
                    $scriptName = getenv( 'SCRIPT_NAME' );
                    $requestUri = $scriptName;
                    // add path_info
                    $pathInfo = getenv( 'PATH_INFO' );
                    if ( ! empty($pathInfo) ) {
                        // Some IIS + PHP configurations puts the script-name in the path-info.
                        // No need to append it twice !
                        if ( $pathInfo != $scriptName ) {
                            $requestUri .= $pathInfo;
                        }
                    }
                    // add query_string
                    $queryString = getenv( 'QUERY_STRING' );
                    if ( $queryString !== false && $queryString !== '' ) {
                        $requestUri .= '?' . $queryString;
                    }
                    putenv( 'REQUEST_URI=' . $requestUri );
                    $_SERVER['REQUEST_URI'] = $requestUri;
                }
                //
                // WIZIN_CACHE_DIR
                if ( ! defined('WIZIN_CACHE_DIR') ) {
                    define( 'WIZIN_CACHE_DIR', WIZIN_ROOT_PATH . '/work/cache' );
                }

                //
                // WIZIN_COMPILE_DIR
                if ( ! defined('WIZIN_COMPILE_DIR') ) {
                    define( 'WIZIN_COMPILE_DIR', WIZIN_ROOT_PATH . '/work/compile' );
                }

                //
                // WIZIN_PEAR_DIR
                if ( ! defined('WIZIN_PEAR_DIR') ) {
                    define( 'WIZIN_PEAR_DIR', WIZIN_ROOT_PATH . '/lib/PEAR' );
                }

                //
                // WIZIN_UPLOAD_DIR
                if ( ! defined('WIZIN_UPLOAD_DIR') ) {
                    define( 'WIZIN_UPLOAD_DIR', WIZIN_ROOT_PATH . '/work/uploads' );
                }

                //
                // WIZ_SITE_ROOT
                if ( ! defined('WIZ_SITE_ROOT') ) {
                    define( 'WIZ_SITE_ROOT', WIZIN_ROOT_PATH );
                }

            }

            /**
             *
             * @access public
             * @return object $instance
             */
            function &getSingleton()
            {
                static $instance;
                if ( ! isset($instance) ) {
                    $instance = new Wizin();
                }
                return $instance;
            }
        }
    }
}
