<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

if ( !defined('LEGACY_CONTROLLER_STATE_PUBLIC') ) {
    include_once( XOOPS_ROOT_PATH . '/modules/legacy/kernel/Legacy_Controller.class.php' );
}
var_dump( 999 );

if( ! class_exists( 'Legacy_WizMobileController' ) ) {
    class Legacy_WizMobileController extends Legacy_Controller
    {
        function executeRedirect($url, $time = 1, $message = null, $addRedirect = true)
        {
            ob_start();
            $sessionName = ini_get( 'session.name' );
            if ( strpos($url, $sessionName) > 0 ) {
                $sessionIdLength = strlen( session_id() );
                $delstr = $sessionName . '=';
                $delstr = "/(.*)(" . $delstr . ")(\w{" . $sessionIdLength . "})(.*)/i";
                $url = preg_replace( $delstr, '${1}${4}', $url );
                if ( substr($url, -1, 1) === '?' ) {
                    $url = substr( $url, 0, strlen($url) - 1 );
                }
            }
            parent::executeRedirect( $url, $time, $message, $addRedirect );
        }

        function executeForward( $url, $time = 0, $message = null )
        {
            ob_start();
            $sessionName = ini_get( 'session.name' );
            if ( ! empty($_GET[$sessionName]) || ! empty($_POST[$sessionName]) ) {
                if ( ! strpos($url, $sessionName) && strpos($url, XOOPS_URL) === 0 ) {
                    if ( ! strstr($url, '?') ) {
                        $url .= '?' . SID;
                    } else {
                        $url .= '&' . SID;
                    }
                }
            }
            parent::executeForward( $url, $time, $message );
        }
    }
}
