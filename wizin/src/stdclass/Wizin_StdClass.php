<?php
/**
 * Wizin framework standard class read script
 *
 * PHP Versions 5
 *
 * @package  Wizin
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

$phpVersion = floatval( PHP_VERSION );
if ( $phpVersion < 4.3 ) {
    exit( 'Sorry, this framework over PHP4.3X' );
} else if ( $phpVersion < 5.0 ) {
    require_once 'src/stdclass/Wizin_StdClass_4x.class.php';
} else if ( $phpVersion < 5.2 ) {
    require_once 'src/stdclass/Wizin_StdClass_50x.class.php';
} else {
    require_once 'src/stdclass/Wizin_StdClass_52x.class.php';
}
