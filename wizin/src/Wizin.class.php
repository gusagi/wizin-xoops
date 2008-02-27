<?php
/**
 * Wizin framework root object class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi <gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

if ( ! defined('WIZIN_LOADED') ) {
    define( 'WIZIN_LOADED', true );
    set_include_path( get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)) );
    require_once 'src/stdclass/Wizin_StdClass.php';
    require_once 'src/util/Wizin_Util.class.php';

    /**
     * @access public
     *
     */
    class Wizin extends Wizin_StdClass
    {

        /**
         * @access public
         * @return Wizin
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
