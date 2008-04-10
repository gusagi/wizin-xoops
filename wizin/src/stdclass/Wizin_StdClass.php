<?php
/**
 * Wizin framework standard class read script
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

$phpVersion = floatval( PHP_VERSION );
if ( $phpVersion < 4.4 ) {
    exit( 'Sorry, this framework over PHP4.4.X' );
} else if ( $phpVersion < 5.0 ) {
    require_once 'src/stdclass/Wizin_StdClass_4x.class.php';
} else if ( $phpVersion < 5.2 ) {
    require_once 'src/stdclass/Wizin_StdClass_50x.class.php';
} else {
    require_once 'src/stdclass/Wizin_StdClass_52x.class.php';
}
