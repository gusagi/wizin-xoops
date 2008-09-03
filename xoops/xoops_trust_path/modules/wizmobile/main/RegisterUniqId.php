<?php
/**
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

// direct access protect
$scriptFileName = getenv( 'SCRIPT_FILENAME' );
if ( $scriptFileName === __FILE__ ) {
    exit();
}

// init process
$xcRoot =& XCube_Root::getSingleton();
$wizMobile =& WizMobile::getSingleton();
$configs = $this->getConfigs();
if ( ! is_object($xcRoot->mContext->mXoopsUser) ) {
    $xcRoot->mController->executeRedirect( XOOPS_URL . '/user.php', 1, _NOPERM );
    exit();
}
$renderTarget =& $xcRoot->mContext->mModule->getRenderTarget();
$frontDirname = str_replace( '_wizmobile_action', '', strtolower(get_class($this)) );
$tplFile = $frontDirname . '_main_register_uniqid.html';
$renderTarget->setTemplateName( $tplFile );

// if login disabled
if ( empty($configs['login']) || $configs['login']['wmc_value'] !== '1' ) {
    $xcRoot->mController->executeForward( XOOPS_URL );
}

// register and redirect
$method = getenv( 'REQUEST_METHOD' );
if ( strtolower($method) === 'post' ) {
    $db =& XoopsDatabaseFactory::getDatabaseConnection();
    $gTicket = new XoopsGTicket();
    if ( ! $gTicket->check(true, $this->_sFrontDirName, false) ) {
        $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
            $this->_sFrontDirName . '/index.php?act=Setting', 1,
            sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
    }
    $user = & Wizin_User::getSingleton();
    $user->checkClient( true );
    if ( ! $user->bIsMobile ) {
        $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
            $this->_sFrontDirName . '/index.php?act=Setting', 1,
            sprintf(Wizin_Util::constant('WIZMOBILE_MSG_REGISTER_UNIQID_FAILED'),
            Wizin_Util::constant('WIZMOBILE_LANG_REGISTER')) );
    }
    if ( $user->sUniqId === '' ) {
        $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
            $this->_sFrontDirName . '/index.php?act=Setting', 1,
            Wizin_Util::constant('WIZMOBILE_MSG_CANNOT_GET_UNIQID') );
    }
    $loginTable = $db->prefix( $this->_sFrontDirName . '_login' );
    $uid = $xcRoot->mContext->mXoopsUser->get( 'uid' );
    $uniqId = md5( $user->sUniqId . XOOPS_SALT );
    $now = date( 'Y-m-d H:i:s' );
    // TODO : use ORM
    $mode = Wizin_Util::constant( 'WIZMOBILE_LANG_REGISTER' );
    $sql = "SELECT `wml_uniqid` FROM `$loginTable` WHERE `wml_uid` = '$uid'";
    if ( $resource = $db->query($sql) ) {
        $result = $db->fetchArray( $resource );
        if ( $result !== false && ! empty($result) ) {
            $mode = Wizin_Util::constant( 'WIZMOBILE_LANG_UPDATE' );
        }
    }
    if ( $mode === Wizin_Util::constant('WIZMOBILE_LANG_REGISTER') ) {
        $sql = "INSERT INTO `$loginTable` ( `wml_uid`, `wml_uniqid`, `wml_init_datetime`, `wml_update_datetime` ) VALUES ( '$uid', '$uniqId', '$now', '$now' );";
    } else if ( $mode === Wizin_Util::constant('WIZMOBILE_LANG_UPDATE') ) {
        $sql = "UPDATE `$loginTable` SET `wml_uniqid` = '$uniqId', `wml_update_datetime` = '$now' WHERE `wml_uid` = '$uid';";
    }
    if ( $db->query($sql) ) {
        $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
            $this->_sFrontDirName . '/index.php?act=Setting', 1,
            sprintf(Wizin_Util::constant('WIZMOBILE_MSG_REGISTER_UNIQID_SUCCESS'), $mode) );
    } else {
        $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
            $this->_sFrontDirName . '/index.php?act=Setting', 1,
            sprintf(Wizin_Util::constant('WIZMOBILE_MSG_REGISTER_UNIQID_FAILED'), $mode) );
    }
}

// call header
require_once XOOPS_ROOT_PATH . '/header.php';

// call footer
require_once XOOPS_ROOT_PATH . '/footer.php';

