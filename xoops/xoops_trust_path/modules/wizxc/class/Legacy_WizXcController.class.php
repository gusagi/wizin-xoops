<?php

if (!defined('XOOPS_ROOT_PATH')) exit();

if ( !defined('LEGACY_CONTROLLER_STATE_PUBLIC') ) {
    include_once( XOOPS_ROOT_PATH . '/modules/legacy/kernel/Legacy_Controller.class.php' );
}

if( ! class_exists( 'Legacy_WizXcController' ) ) {
    class Legacy_WizXcController extends Legacy_Controller
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
                if ( strstr($url, '?&') ) {
                    $url = str_replace( '?&', '?', $url );
                }
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
            if ( ! empty($_REQUEST[$sessionName]) ) {
                if ( ! strpos($url, $sessionName) && strpos($url, XOOPS_URL) === 0 ) {
                    if ( !strstr($url, '?') ) {
                        $connector = '?';
                    } else {
                        $connector = '&';
                    }
                    if ( strstr($url, '#') ) {
                        $urlArray = explode( '#', $url );
                        $url = $urlArray[0] . $connector . SID;
                        if ( ! empty($urlArray[1]) ) {
                            $url .= '#' . $urlArray[1];
                        }
                    } else {
                        $url .= $connector . SID;
                    }
                }
            }
            parent::executeForward( $url, $time, $message );
        }
    }
}
