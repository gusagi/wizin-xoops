<?php
/**
 * Wizin framework filter class read script
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

$phpVersion = intval( PHP_VERSION );
if ( $phpVersion < 5 ) {
    require_once dirname( __FILE__ ) . '/filter/Php4x.class.php';
} else {
    require_once dirname( __FILE__ ) . '/filter/Php5x.class.php';
}
