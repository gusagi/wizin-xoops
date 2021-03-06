<?php
/**
 * Wizin framework utility class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Util')) {
    require dirname(__FILE__) . '/Wizin.class.php';

    /**
     * Wizin framework utility class
     *
     * @access public
     *
     */
    class Wizin_Util
    {
        /**
         * call user function with reference args
         *
         * @param string $function
         * @param array $args
         */
        function callUserFuncArrayReference($function, $args = array())
        {
            $result = null;
            $process = null;
            $param = array();
            if (is_array($args)) {
                for ($index = 0; $index < count($args); $index ++) {
                    $param[] =& $args[$index];
                }
            }
            call_user_func_array($function, $param);
        }

        /**
         * define constant
         *
         * @param string $name
         * @param string $value
         * @param string $prefix
         */
        function define($name, $value = '', $prefix = '')
        {
            if (! defined(strtoupper($prefix . '_' . $name))) {
                define(strtoupper($prefix . '_' . $name), $value);
            }
        }

        /**
         * return constant
         *
         * @param string $name
         * @param string $prefix
         * @return string
         */
        function constant($name, $prefix = '')
        {
            if (defined(strtoupper($prefix . '_' . $name))) {
                return constant(strtoupper($prefix . '_' . $name));
            } else {
                $null = null;
                return $null;
            }
        }

        /**
         * return ciphered string
         *
         * @param string $string
         * @return string $code
         */
        function cipher($string = '')
        {
            $string = md5($string ."\t" .Wizin::salt());
            $number = hexdec($string);
            $code = base_convert(floatval($number), 10, 36);
            return $code;
        }

        /**
         * get file list under directory
         *
         * @param string $directory
         * @return array
         */
        function getFilesUnderDir($directory = '')
        {
            static $files;
            if (! isset($files)) {
                $files = array();
            }
            // if $directory is empty, return empty array
            if (empty($directory)) {
                return $files;
            }
            // directory check
            if (substr($directory, -1, 1) === '/') {
                $directory = substr($directory, 0, strlen($directory) - 1);
            }
            if (file_exists($directory) && is_dir($directory)) {
                if ($handler = opendir($directory)) {
                    while (($file = readdir($handler)) !== false) {
                        if ($file === '.' || $file === '..') {
                            continue;
                        }
                        $filePath = $directory . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($filePath)) {
                            Wizin_Util::getFilesUnderDir($filePath);
                        } else {
                            if (! in_array($filePath, $files)) {
                                $files[] = $filePath;
                            }
                        }
                    }
                    closedir($handler);
                }
            }
            return $files;
        }

        /**
         * return string for something salt
         *
         * @param string $salt
         * @return string $prefix
         */
        function fprefix($salt = '')
        {
            static $prefix;
            if (! isset($prefix)) {
                if (empty($salt)) {
                    $salt = Wizin::salt();
                }
                $hostSeed = getenv('SERVER_NAME');
                $replaceArray = array('/' => '%', '.' => '%%');
                $prefix = strtr($hostSeed, $replaceArray) . '_' .$salt;
            }
            return $prefix;
        }

        /**
         * Redirect to $url
         *
         * @param string $url
         */
        function redirect($url = '')
        {
            if (empty($url)) {
                return false;
            }
            $sessionName = ini_get('session.name');
            $sessionId = session_id();
            if (! empty($sessionId) && (isset($_GET[$sessionName]) || isset($_POST[$sessionName]))) {
                $sid = session_name() . '=' . session_id();
                $pattern = '(' . $sid . ')(&|&amp;)?';
                $url = preg_replace('/' . $pattern . '/', '', $url);
                if (substr($url, -1, 1) === '?' || substr($url, -1, 1) === '&') {
                    $url = substr($url, 0, strlen($url) - 1);
                }
                if (strpos($url, '?') !== false) {
                    $url .= '&' . $sid;
                } else {
                    $url .= '?' . $sid;
                }
            }
            header('Location: ' . $url);
            exit();
        }


        function stripslashesRecursive($value = '')
        {
            $value = is_array ($value) ?
                array_map(array('Wizin_Util', 'stripslashesRecursive'), $value) :
                stripslashes($value);
            return $value;
        }
    }
}
