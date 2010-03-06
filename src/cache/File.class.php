<?php
/**
 * Wizin framework file cache class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Cache')) {
    require dirname(dirname(__FILE__)) . '/Wizin.class.php';

    /**
     * Wizin framework file cache class
     *
     */
    class Wizin_Cache extends Wizin_StdClass
    {
        function __construct($prefix = '', $suffix = '', $cacheDir = '')
        {
            Wizin::getSingleton();
            if (empty($prefix)) {
                $prefix = 'wizin_cache_';
            }
            if (empty($suffix)) {
                $suffix = Wizin_Util::cipher();
            }
            $cache = $prefix . $suffix;
            if (empty($cacheDir) || ! file_exists($cacheDir) ||
                    ! is_dir($cacheDir) || ! is_writable($cacheDir)) {
                $cacheDir = WIZIN_CACHE_DIR;
            }
            $this->_sCacheFile = $cacheDir . '/' . $cache;
            clearstatcache();
        }

        function isCached($file)
        {
            $return = true;
            if (! file_exists($this->_sCacheFile)) {
                $return = false;
            } else if (is_array($file) && ! empty($file)) {
                foreach ($file as $target) {
                    if (! file_exists($file) || ! is_file($file)) {
                        continue;
                    }
                    if (filemtime($file) > filemtime($this->_sCacheFile)) {
                        $return = false;
                        unlink($this->_sCacheFile);
                        break;
                    }
                }
            } else if (is_string($file)) {
                if (filemtime($file) > filemtime($this->_sCacheFile)) {
                    $return = false;
                }
            }
            return $return;
        }

        function save($data = '')
        {
            $fp = fopen($this->_sCacheFile, 'wb');
            fwrite($fp, serialize($data));
            fclose($fp);
        }

        function load()
        {
            return unserialize(file_get_contents($this->_sCacheFile));
        }

        function clear()
        {
            if (file_exists($this->_sCacheFile)) {
                unlink($this->_sCacheFile);
            }
        }

    }
}
