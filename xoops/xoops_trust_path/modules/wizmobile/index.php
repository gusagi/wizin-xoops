<?php
/**
 * WizMobile module index script for XOOPS Cube Legacy2.1
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

require dirname( __FILE__ ) . '/init.php';
$wizMobile =& WizMobile::getSingleton();
$wizMobile->execute();
