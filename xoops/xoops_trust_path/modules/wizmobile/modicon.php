<?php
/**
 * PHP Versions 4
 *
 * @package  WizMobile
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 *
 */

if ( file_exists(dirname(__FILE__) . '/images/modicon.png') ) {
    header("Content-type: image/png");
    readfile( dirname(__FILE__) . '/images/modicon.png' );
}
exit();
