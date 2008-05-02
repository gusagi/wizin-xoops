<?php
/**
 * PHP Versions 4
 *
 * @package  WizXc
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license GNU General Public License Version2
 *
 */

/**
 * GNU General Public License Version2
 *
 * Copyright (C) 2008  < Makoto Hashiguchi a.k.a. gusagi >
 * All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

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
