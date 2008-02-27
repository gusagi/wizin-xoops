<?php
/**
 * call WizXC module init process preload
 *
 * PHP Versions 4
 *
 * @package  WizXC
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

if ( ! class_exists('CallWizXC') && defined('XOOPS_TRUST_PATH') ) {
    class CallWizXC extends XCube_ActionFilter
    {
        function preFilter()
        {
            $initScript = XOOPS_TRUST_PATH . '/modules/wizxc/init.php';
            if ( file_exists($initScript) && is_readable($initScript) ) {
                require_once $initScript;
            }
            parent::preFilter();
        }

        function preBlockFilter()
        {
            parent::preBlockFilter();
        }

        function postFilter()
        {
            parent::postFilter();
        }
    }
}
