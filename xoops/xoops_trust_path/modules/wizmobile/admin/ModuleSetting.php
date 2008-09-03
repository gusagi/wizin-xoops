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
$db =& XoopsDatabaseFactory::getDatabaseConnection();
$frontDirname = str_replace( '_wizmobile_action', '', strtolower(get_class($this)) );
$tplFile = 'db:' . $frontDirname . '_admin_module_setting.html';

// register and redirect
$method = getenv( 'REQUEST_METHOD' );
if ( strtolower($method) === 'post' ) {
    $gTicket = new XoopsGTicket();
    if ( ! $gTicket->check(true, $this->_sFrontDirName, false) ) {
        $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
            sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
    }
    $moduleTable = $db->prefix( $this->_sFrontDirName . '_module' );
    $modulesTable = $db->prefix( 'modules' );
    $insertModules = array();
    $updateModules = array();
    $deleteModules = array();
    $existsModules = array();
    $now = date( 'Y-m-d H:i:s' );
    // TODO : use ORM
    $sql = "SELECT `mid` FROM `$modulesTable`";
    if ( $resource = $db->query($sql) ) {
        while ( $result = $db->fetchArray($resource) ) {
            if ( $result !== false && ! empty($result) ) {
                $existsModules[] = $result['mid'];
            }
        }
    }
    $denyAccessModules = $this->getDenyAccessModules();
    $requestModules = ( ! empty($_REQUEST['wmm_mid']) && is_array($_REQUEST['wmm_mid']) ) ?
        $_REQUEST['wmm_mid']: array();
    $insertModules = array_diff( $requestModules, $denyAccessModules );
    $updateModules = array_intersect( $requestModules, $denyAccessModules );
    $deleteModules = array_merge( array_diff($denyAccessModules, $existsModules),
        array_diff($denyAccessModules, $requestModules) );
    $insertModules = array_map( 'intval', $insertModules );
    $updateModules = array_map( 'intval', $updateModules );
    $deleteModules = array_map( 'intval', $deleteModules );
    $sqlArray = array();
    foreach ( $insertModules as $wmm_mid ) {
        $sqlArray[] = "INSERT INTO `$moduleTable` (`wmm_mid`, `wmm_init_datetime`, `wmm_update_datetime`) VALUES ($wmm_mid, '$now', '$now');";
    }
    if ( ! empty($updateModules) ) {
        $sqlArray[] = "UPDATE `$moduleTable` SET `wmm_update_datetime` = '$now' WHERE `wmm_mid` IN ( " .
            implode( ',', $updateModules ) . " ) AND `wmm_delete_datetime` = '0000-00-00 00:00:00';";
    }
    if ( ! empty($deleteModules) ) {
        $sqlArray[] = "UPDATE `$moduleTable` SET `wmm_delete_datetime` = '$now' WHERE `wmm_mid` IN ( " .
            implode( ',', $deleteModules ) . " ) AND `wmm_delete_datetime` = '0000-00-00 00:00:00';";
    }
    foreach ( $sqlArray as $sql ) {
        if ( ! $db->query($sql) ) {
            $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
                $this->_sFrontDirName . '/admin/admin.php?act=ModuleSetting', 3,
                sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_MODULE_SETTING_FAILED')) );
        }
    }
    WizXc_Util::clearCompiledCache();
    $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
        $this->_sFrontDirName . '/admin/admin.php?act=ModuleSetting', 3,
        sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_MODULE_SETTING_SUCCESS')) );
}

// get block list
$modulesTable = $db->prefix( 'modules' );
$modules = array();
// TODO : use ORM
$sql = "SELECT ";
$sql .= " $modulesTable.`mid`, $modulesTable.`name`, ";
$sql .= " $modulesTable.`dirname` ";
$sql .= " FROM `$modulesTable` ";
$sql .= " WHERE ";
$sql .= " $modulesTable.`isactive` = 1 ;";
if ( $resource = $db->query($sql) ) {
    while ( $result = $db->fetchArray($resource) ) {
        if ( $result !== false && ! empty($result) ) {
            $modules[] = $result;
        }
    }
}
$denyAccessModules = $this->getDenyAccessModules();

//
// render admin view
//
// call header
require_once XOOPS_ROOT_PATH . '/header.php';

// display main templates
$xoopsTpl->assign( 'modules', $modules );
$xoopsTpl->assign( 'denyAccessModules', $denyAccessModules );
$xoopsTpl->display( $tplFile );

// call footer
require_once XOOPS_ROOT_PATH . '/footer.php';
