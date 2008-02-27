<?php
/**
 * Wizin framework session class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi <gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

if ( ! class_exists('Wizin_Session') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';

    class Wizin_Session extends Wizin_StdClass
    {

        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Session();
            }
            return $instance;
        }

        function overrideSessionIni( $useCookie = true )
        {
            if ( $useCookie ) {
                ini_set( 'session.use_cookies', "1" );
                ini_set( 'session.use_only_cookies', "1" );
            } else {
                ini_set( 'session.use_cookies', "0" );
                ini_set( 'session.use_only_cookies', "0" );
            }
            ini_set( 'session.use_trans_sid', "0" );
        }

        function regenerateId()
        {
            $saveHandler = ini_get( 'session.save_handler' );
            if ( $saveHandler === 'files' ) {

            }
            session_regenerate_id();
        }

    }
}
