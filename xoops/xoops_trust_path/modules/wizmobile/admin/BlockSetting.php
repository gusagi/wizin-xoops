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
$xoopsTpl = WizXc_Util::getXoopsTpl();
$frontDirname = str_replace( '_wizmobile_action', '', strtolower(get_class($this)) );
$tplFile = 'db:' . $frontDirname . '_admin_block_setting.html';

// register and redirect
$method = getenv( 'REQUEST_METHOD' );
if ( strtolower($method) === 'post' ) {
    $gTicket = new XoopsGTicket();
    if ( ! $gTicket->check(true, $this->_sFrontDirName, false) ) {
        $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
            sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
    }
    $db =& XoopsDatabaseFactory::getDatabaseConnection();
    $blockTable = $db->prefix( $this->_sFrontDirName . '_block' );
    $newblocksTable = $db->prefix( 'newblocks' );
    $insertBlocks = array();
    $updateBlocks = array();
    $deleteBlocks = array();
    $existsBlocks = array();
    $now = date( 'Y-m-d H:i:s' );
    // TODO : use ORM
    $sql = "SELECT `bid` FROM `$newblocksTable`";
    if ( $resource = $db->query($sql) ) {
        while ( $result = $db->fetchArray($resource) ) {
            if ( $result !== false && ! empty($result) ) {
                $existsBlocks[] = $result['bid'];
            }
        }
    }
    $nonDisplayBlocks = $this->getNondisplayBlocks();
    $requestBlocks = ( ! empty($_REQUEST['wmb_bid']) && is_array($_REQUEST['wmb_bid']) ) ?
        $_REQUEST['wmb_bid']: array();
    $insertBlocks = array_diff( $requestBlocks, $nonDisplayBlocks );
    $updateBlocks = array_intersect( $requestBlocks, $nonDisplayBlocks );
    $deleteBlocks = array_merge( array_diff($nonDisplayBlocks, $existsBlocks),
        array_diff($nonDisplayBlocks, $requestBlocks) );
    $insertBlocks = array_map( 'intval', $insertBlocks );
    $updateBlocks = array_map( 'intval', $updateBlocks );
    $deleteBlocks = array_map( 'intval', $deleteBlocks );
    $sqlArray = array();
    foreach ( $insertBlocks as $wmb_bid ) {
        $sqlArray[] = "INSERT INTO `$blockTable` (`wmb_bid`, `wmb_init_datetime`, `wmb_update_datetime`) VALUES ($wmb_bid, '$now', '$now');";
    }
    if ( ! empty($updateBlocks) ) {
        $sqlArray[] = "UPDATE `$blockTable` SET `wmb_update_datetime` = '$now' WHERE `wmb_bid` IN ( " .
            implode( ',', $updateBlocks ) . " ) AND `wmb_delete_datetime` = '0000-00-00 00:00:00';";
    }
    if ( ! empty($deleteBlocks) ) {
        $sqlArray[] = "UPDATE `$blockTable` SET `wmb_delete_datetime` = '$now' WHERE `wmb_bid` IN ( " .
            implode( ',', $deleteBlocks ) . " ) AND `wmb_delete_datetime` = '0000-00-00 00:00:00';";
    }
    foreach ( $sqlArray as $sql ) {
        if ( ! $db->query($sql) ) {
            $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
                $this->_sFrontDirName . '/admin/admin.php?act=BlockSetting', 3,
                sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_FAILED')) );
        }
    }
    $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
        $this->_sFrontDirName . '/admin/admin.php?act=BlockSetting', 3,
        sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_SUCCESS')) );
}

// get block list
$blocks = $this->getBlocks();
$nonDisplayBlocks = $this->getNondisplayBlocks();

//
// render admin view
//
// call header
require_once XOOPS_ROOT_PATH . '/header.php';

// display main templates
$xoopsTpl->assign( 'blocks', $blocks );
$xoopsTpl->assign( 'nonDisplayBlocks', $nonDisplayBlocks );
$xoopsTpl->display( $tplFile );

// call footer
require_once XOOPS_ROOT_PATH . '/footer.php';
