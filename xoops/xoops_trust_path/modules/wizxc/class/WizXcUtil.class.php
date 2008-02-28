<?php
/**
 *
 * PHP Versions 4
 *
 * @package  WizXc
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

if ( ! class_exists('WizXcUtil') ) {
    class WizXcUtil
    {
        function sessionDestroy()
        {
            $xcRoot =& XCube_Root::getSingleton();
            $xcRoot->mSession->destroy();
            $xcRoot->mSession->start();
            $_SESSION = array();
        }
    }
}
