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

if (! defined('WIZIN_ROOT_PATH')) {
    define('WIZIN_ROOT_PATH', dirname(dirname(__FILE__)));
    //set_include_path(get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)));
    require_once WIZIN_ROOT_PATH . '/src/Wizin_StdClass.php';
    require_once WIZIN_ROOT_PATH . '/src/Wizin_Ref.php';
    require_once WIZIN_ROOT_PATH . '/src/Wizin_Util.class.php';
    require_once WIZIN_ROOT_PATH . '/src/Wizin_Function.php';

    if (class_exists('Wizin_StdClass')) {
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
                // REQUEST_URI
                $requestUri = getenv('REQUEST_URI');
                if (empty($requestUri)) {
                    // set path
                    $scriptName = getenv('SCRIPT_NAME');
                    $requestUri = $scriptName;
                    // add path_info
                    $pathInfo = getenv('PATH_INFO');
                    if (! empty($pathInfo)) {
                        // Some IIS + PHP configurations puts the script-name in the path-info.
                        // No need to append it twice !
                        if ($pathInfo != $scriptName) {
                            $requestUri .= $pathInfo;
                        }
                    }
                    // add query_string
                    $queryString = getenv('QUERY_STRING');
                    if ($queryString !== false && $queryString !== '') {
                        $requestUri .= '?' . $queryString;
                    }
                    putenv('REQUEST_URI=' . $requestUri);
                    $_SERVER['REQUEST_URI'] = $requestUri;
                }
                // DIRECTORY_SEPARATOR
                if (! defined('DS')) {
                    define('DS', DIRECTORY_SEPARATOR);
                }
                // WIZIN_WORK_DIR
                if (! defined('WIZIN_WORK_DIR')) {
                    define('WIZIN_WORK_DIR', WIZIN_ROOT_PATH .DS .'work');
                }
                // WIZIN_CACHE_DIR
                if (! defined('WIZIN_CACHE_DIR')) {
                    define('WIZIN_CACHE_DIR', WIZIN_WORK_DIR .DS .'cache');
                }
                // WIZIN_COMPILE_DIR
                if (! defined('WIZIN_COMPILE_DIR')) {
                    define('WIZIN_COMPILE_DIR', WIZIN_WORK_DIR .DS .'compile');
                }
                // WIZIN_PEAR_DIR
                if (! defined('WIZIN_PEAR_DIR')) {
                    define('WIZIN_PEAR_DIR', WIZIN_ROOT_PATH .DS .'lib' .DS .'PEAR');
                }
                // WIZIN_UPLOAD_DIR
                if (! defined('WIZIN_UPLOAD_DIR')) {
                    define('WIZIN_UPLOAD_DIR', WIZIN_WORK_DIR .DS .'uploads');
                }
                // WIZIN_URL
                if (! defined('WIZIN_URL')) {
                    define('WIZIN_URL', 'http://' . getenv('SERVER_NAME') . '/');
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
                if (! isset($instance)) {
                    $instance = new Wizin();
                }
                return $instance;
            }

            /**
             * return string for something salt
             *
             * @param string $salt
             * @return string $prefix
             */
            function salt($seed = '')
            {
                static $salt;
                if (! isset($salt)) {
                    if ($seed === '') {
                        $seed = md5(__FILE__);
                    }
                    $salt = substr(md5($seed), 0, 8);
                }
                return $salt;
            }
        }
    }
}
