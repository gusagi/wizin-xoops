<?php
/**
 * entry point script for "Wizin" module series on XOOPS Cube Legacy2.1
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

define( '_LEGACY_PREVENT_LOAD_CORE_', 1 );
require dirname( dirname(dirname(__FILE__)) ) . '/mainfile.php';
require dirname( __FILE__ ) . '/mytrustdirname.php';

if ( defined('XOOPS_TRUST_PATH') ) {
    $frontFile = __FILE__;
    $trustFile = XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/' . basename( __FILE__ );
    if ( file_exists($trustFile) ) {
        require $trustFile;
    }
}