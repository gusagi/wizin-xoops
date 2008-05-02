<?php
/**
 * WizMobile module preload script for XOOPS Cube Legacy2.1
 *
 * PHP Versions 4.4.X or upper version
 *
 * @package  WizMobile
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

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

$initScript = dirname( dirname(__FILE__) ) . '/init.php';
if ( file_exists($initScript) && is_readable($initScript) ) {
    require_once $initScript;
}

if ( ! class_exists('CallWizMobile') ) {
    class CallWizMobile extends XCube_ActionFilter
    {
        function preBlockFilter()
        {
            $wizMobile =& WizMobile::getSingleton();
            preg_match( "/(\w+)\.class\.php/", strtolower(basename(__FILE__)), $matches );
            $frontDirname = str_replace( '_' . $matches[1], '', get_class($this) );
            $actionScript = dirname( dirname(__FILE__) ) . '/class/WizMobile_Action.class.php';
            if ( file_exists($actionScript) ) {
                require_once $actionScript;
                if ( class_exists($className) ) {
                    $wizMobileAction = new $className();
                    $wizMobile->setActionClass( $wizMobileAction );
                }
            }
            parent::preBlockFilter();
        }

        function postFilter()
        {
            // test code >>
            if ( strpos($_SERVER['REQUEST_URI'], 'PHPSESSID') !== false ) {
                $urlArray = explode( 'PHPSESSID', WIZXC_CURRENT_URI );
                $url = $urlArray[0];
                if ( substr($url, -1, 1) === '?' || substr($url, -1, 1) === '&' ) {
                    $url = substr( $url, 0, strlen($url) - 1 );
                }
                header( "HTTP/1.1 404 Not Found" );
                exit();
            }
            // test code <<
            $wizMobile =& WizMobile::getSingleton();
            $user = & Wizin_User::getSingleton();
            if ( $user->bIsMobile ) {
                $xcRoot =& XCube_Root::getSingleton();
                // exchange theme
                $wizMobile->exchangeTheme();
                // regenerate session id
                $xcRoot->mDelegateManager->add( 'Site.CheckLogin.Success', array($wizMobile, 'directLoginSuccess'), XCUBE_DELEGATE_PRIORITY_FINAL );
                $xcRoot->mDelegateManager->add( 'Site.CheckLogin.Fail', array($wizMobile, 'directLoginFail') );
                $xcRoot->mDelegateManager->add( 'Site.Logout.Success', array($wizMobile, 'directLogout'),
                    XCUBE_DELEGATE_PRIORITY_FINAL+1 );
                $xcRoot->mDelegateManager->add( 'Site.Logout.Fail', array($wizMobile, 'directLogout'),
                    XCUBE_DELEGATE_PRIORITY_FINAL+1 );
                $xcRoot->mDelegateManager->add( 'Legacy_AdminControllerStrategy.SetupBlock',
                    array($wizMobile, 'denyAccessAdminArea'), XCUBE_DELEGATE_PRIORITY_FIRST );
                // insert session_id
                if ( ! $user->bIsBot ) {
                    // check session
                    $wizMobile->checkMobileSession();
                } else {
                    $wizMobile->checkSessionFixation();
                }
            } else {
                $wizMobile->checkSessionFixation();
            }
            parent::postFilter();
        }
    }
}

$mod_dir = basename( dirname(dirname($frontFile)) );
preg_match( "/(\w+)\.class\.php/", strtolower(basename(__FILE__)), $matches );
$className = $mod_dir . "_" . $matches[1];
if ( ! class_exists($className) ) {
    eval( "class $className extends CallWizMobile {}" );
}
