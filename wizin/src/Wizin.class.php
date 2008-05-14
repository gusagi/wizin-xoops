<?php
/**
 * Wizin framework root object class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! defined('WIZIN_LOADED') ) {
    define( 'WIZIN_LOADED', true );
    define( 'WIZIN_ROOT_PATH', dirname(dirname(__FILE__)) );
    set_include_path( get_include_path() . PATH_SEPARATOR . dirname(dirname(__FILE__)) );
    require_once 'src/Wizin_StdClass.php';
    require_once 'src/Wizin_Ref.php';
    require_once 'src/Wizin_Util.class.php';

    if ( class_exists('Wizin_StdClass') ) {
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
}
