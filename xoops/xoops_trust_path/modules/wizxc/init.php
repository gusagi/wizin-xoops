<?php
/**
 * WizXC module init script for XOOPS Cube Legacy2.1
 *
 * PHP Versions 4
 *
 * @package  WizXC
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

$scriptFileName = getenv( 'SCRIPT_FILENAME' );
if ( $scriptFileName === __FILE__ ) {
    exit();
}

require dirname( __FILE__ ) . '/class/wizxc.class.php';
$wizXc =& WizXC::getSingleton();
