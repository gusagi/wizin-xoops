<?php
/**
 * Wizin framework core page class
 *
 * PHP Version 5.2 or Upper version
 *
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 * @link http://www.gusagi.com/
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 *
 */

if ( ! class_exists('Wizin_Core_Page') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    /**
     * Wizin framework core page class
     *
     */
    abstract class Wizin_Core_Page extends Wizin_StdClass
    {
    }
}
