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

    /**
     * Wizin framework YAML parser class
     *
     * @access public
     */
    class Wizin_Parser_Yaml extends Wizin_StdClass
    {
        /**
         * constructor
         *
         */
        function __construct()
        {
            Wizin::getSingleton();
        }

        /**
         *
         * @return object $instance
         */
        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Parser_Yaml();
            }
            return $instance;
        }

        /**
         * parse yaml file
         *
         * @param string $file
         * @return array $return
         */
        function parse( $file )
        {
            if ( file_exists($file) && is_readable($file) ) {
                $suffix = Wizin_Util::cipher( $file );
                $this->_sYamlFile = $file;
                $this->_sCacheFile = WIZIN_CACHE_DIR . '/wizin_yaml_' . $suffix;
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

        /**
         * check cached data
         *
         * @return boolean $return
         */
        function _isCached()
        {
            clearstatcache();
            $return = ( file_exists($this->_sCacheFile) && (filemtime($this->_sYamlFile) <= filemtime($this->_sCacheFile)) );
            return $return;
        }

        /**
         * load cached data
         *
         * @return array $return
         */
        function _loadCache()
        {
            $return = unserialize( file_get_contents($this->_sCacheFile) );
            return $return;
        }

        /**
         * call Spyc::YAMLLoad
         *
         * @return array $data
         */
        function _loadSpyc()
        {
            if ( ! class_exists('Spyc') ) {
                require_once WIZIN_ROOT_PATH . '/lib/spyc/spyc.php';
            }
            $data = Spyc::YAMLLoad( $this->_sYamlFile );
            $fp = fopen( $this->_sCacheFile, 'wb' );
            fwrite( $fp, serialize($data) );
            fclose( $fp );
            return $data;
        }
    }
}
