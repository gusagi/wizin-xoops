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

if(! class_exists('Wizin_Parser_Yaml')) {
    require dirname(dirname(__FILE__)) . '/Wizin_Cache.php';

    /**
     * Wizin framework YAML parser class
     *
     * @access public
     */
    class Wizin_Parser_Yaml extends Wizin_StdClass
    {
        /**
         *
         * @return object $instance
         */
        function &getSingleton()
        {
            static $instance;
            if (! isset($instance)) {
                $instance = new Wizin_Parser_Yaml();
            }
            return $instance;
        }

        /**
         * parse yaml file
         *
         * @param string $file
         * @return array $data
         */
        function parse($file)
        {
            if (file_exists($file) && is_readable($file)) {
                $cacheObject = new Wizin_Cache('wizin_yaml_', Wizin_Util::cipher($file));
                if (! $cacheObject->isCached($file)) {
                    if (! class_exists('Spyc')) {
                        require_once WIZIN_ROOT_PATH . '/lib/spyc/spyc.php';
                    }
                    $data = Spyc::YAMLLoad($file);
                    $cacheObject->save($data);
                } else {
                    $data = $cacheObject->load();
                }
                return $data;
            } else {
                return array();
            }
        }
    }
}
