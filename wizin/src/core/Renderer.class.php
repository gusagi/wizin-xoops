<?php
/**
 * Wizin framework renderer class extends Smarty
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Core_Renderer')) {
    require dirname(dirname(__FILE__)) . '/Wizin.class.php';
    require WIZIN_ROOT_PATH . '/src/Wizin_Renderer.php';

    /**
     * Wizin framework core renderer class
     *
     */
    class Wizin_Core_Renderer extends Wizin_Renderer
    {
    }
}
