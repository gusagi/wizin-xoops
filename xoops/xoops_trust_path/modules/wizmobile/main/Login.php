<?php
/**
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

// direct access protect
$scriptFileName = getenv( 'SCRIPT_FILENAME' );
if ( $scriptFileName === __FILE__ ) {
    exit();
}

// init process
$xcRoot =& XCube_Root::getSingleton();
$wizMobile =& WizMobile::getSingleton();
$renderTarget =& $xcRoot->mContext->mModule->getRenderTarget();
$frontDirname = str_replace( '_wizmobile_action', '', strtolower(get_class($this)) );
$tplFile = $frontDirname . '_main_login.html';
$renderTarget->setTemplateName( $tplFile );

// if login disabled
$configs = $this->getConfigs();
if ( empty($configs['login']) || $configs['login']['wmc_value'] !== '1' ) {
    $wizMobile->denyAccessLoginPage();
}

// check login and redirect
$method = getenv( 'REQUEST_METHOD' );
if ( strtolower($method) === 'post' ) {
    $this->simpleLogin();
}

// include language file of user module
$language = empty( $GLOBALS['xoopsConfig']['language'] ) ? 'english' : $GLOBALS['xoopsConfig']['language'];
if( file_exists( XOOPS_ROOT_PATH . '/modules/user/language/' . $language . '/blocks.php' ) ) {
    require_once XOOPS_ROOT_PATH . '/modules/user/language/' . $language . '/blocks.php';
}

// login check and get "user" module config
if ( isset($GLOBALS['xoopsUser']) && is_object($GLOBALS['xoopsUser']) ) {
    $xcRoot->mController->executeForward( XOOPS_URL );
} else {
    $config_handler =& xoops_gethandler( 'config' );
    $userModuleConfig =& $config_handler->getConfigsByDirname('user');
}

// call header
require_once XOOPS_ROOT_PATH . '/header.php';

// display main templates
$renderTarget->setAttribute( 'block', $userModuleConfig );

// call footer
require_once XOOPS_ROOT_PATH . '/footer.php';
