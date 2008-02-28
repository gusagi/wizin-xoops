<?php
/**
 * WizMobile module init script for XOOPS Cube Legacy2.1
 *
 * PHP Versions 4
 *
 * @package  WizMobile
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

$scriptFileName = getenv( 'SCRIPT_FILENAME' );
if ( $scriptFileName === __FILE__ ) {
    exit();
}

$initScript = XOOPS_TRUST_PATH . '/modules/wizxc/init.php';
if ( file_exists($initScript) && is_readable($initScript) ) {
    require_once $initScript;
}

require dirname( __FILE__ ) . '/class/WizMobile.class.php';
$wizMobile =& WizMobile::getSingleton();
