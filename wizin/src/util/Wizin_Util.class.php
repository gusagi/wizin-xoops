<?php
/**
 * Wizin framework utility class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi <gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

if ( ! class_exists('Wizin_Util') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';

    /**
     * @access public
     *
     */
    class Wizin_Util extends Wizin_StdClass
    {
        function getPrefix( $salt = '' )
        {
            static $prefix;
            if ( ! isset($prefix) ) {
                if ( empty($salt) ) {
                    $salt = getenv( 'SERVER_NAME' );
                }
                $hostSalt = getenv( 'SERVER_NAME' );
        	    $replaceArray = array( '/' => '%%', '.' => '##' );
        	    $prefix = strtr( $hostSalt, $replaceArray ) . '_' . substr( md5($salt), 0, 8 ) . '_';
            }
    	    return $prefix;
        }
    }
}
