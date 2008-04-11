<?php
/**
 * Wizin framework YAML parser class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if( ! class_exists( 'Wizin_Parser_Yaml' ) ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    require_once 'lib/spyc/spyc.php';

    /**
     * Enter description here...
     *
     */
    class Wizin_Parser_Yaml extends Wizin_StdClass
    {
        function __construct()
        {
            if ( defined('WIZIN_CACHE_DIR') ) {
                $this->_sCacheDir = WIZIN_CACHE_DIR;
            } else {
                $this->_sCacheDir = dirname( dirname(dirname(__FILE__)) ) . '/work/cache';
            }
        }

        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Parser_Yaml();
            }
            return $instance;
        }

        function parse( $file )
        {
            if ( file_exists($file) && is_readable($file) ) {
                $prefix = Wizin_Util::getPrefix();
                $this->_sYamlFile = $file;
                $this->_sCacheFile = $this->_sCacheDir . '/' . $prefix . md5( $file );
                if ( $this->_isCached() ) {
                    $return = $this->_loadCache();
                } else {
                    $return = $this->_loadSpyc();
                }
                return $return;
            } else {
                return array();
            }
        }

        function _isCached()
        {
            clearstatcache();
            $return = ( file_exists($this->_sCacheFile) && (filemtime($this->_sYamlFile) <= filemtime($this->_sCacheFile)) );
            return $return;
        }

        function _loadCache()
        {
            $return = unserialize( file_get_contents($this->_sCacheFile) );
            return $return;
        }

        function _loadSpyc()
        {
            $data = Spyc::YAMLLoad( $this->_sYamlFile );
            $fp = fopen( $this->_sCacheFile, 'wb' );
            fwrite( $fp, serialize($data) );
            fclose( $fp );
            return $data;
        }
    }
}
