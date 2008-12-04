<?php
/**
 * Wizin framework renderer class read script
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

$renderer = 'smarty';
$phpVersion = intval( PHP_VERSION );
if ( $phpVersion >= 5 && extension_loaded('spl') ) {
    if ( defined('WIZIN_RENDERER') ) {
        $rendererPath = dirname( __FILE__ ) . '/renderer/' . ucfirst( WIZIN_RENDERER ) . '.class.php';
        if ( file_exists($rendererPath) ) {
            $renderer = WIZIN_RENDERER;
        }
    }
}
require dirname( __FILE__ ) . '/renderer/' . ucfirst( $renderer ) . '.class.php';
