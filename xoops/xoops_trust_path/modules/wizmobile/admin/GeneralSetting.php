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
$tplFile = 'db:' . $frontDirname . '_admin_general_setting.html';

// register and redirect
$method = getenv( 'REQUEST_METHOD' );
if ( strtolower($method) === 'post' ) {
    $gTicket = new XoopsGTicket();
    if ( ! $gTicket->check(true, $this->_sFrontDirName, false) ) {
        $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
            sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
    }
    $db =& XoopsDatabaseFactory::getDatabaseConnection();
    $configTable = $db->prefix( $this->_sFrontDirName . '_config' );
    $now = date( 'Y-m-d H:i:s' );
    $allowItems = array( 'login', 'theme', 'template_set', 'lookup', 'othermobile', 'pager', 'content_type' );
    $sqlArray = array();
    $requestItems = ( ! empty($_REQUEST['wmc_item']) && is_array($_REQUEST['wmc_item']) ) ?
        $_REQUEST['wmc_item']: array();
    foreach ( $requestItems as $wmc_item => $wmc_value ) {
        if ( ! in_array($wmc_item, $allowItems) ) {
            continue;
        }
        $wmc_item = mysql_real_escape_string( $wmc_item );
        $wmc_value = mysql_real_escape_string( $wmc_value );
        $sql = "SELECT * FROM `$configTable` WHERE `wmc_item` = '$wmc_item';";
        if ( $resource = $db->query($sql) ) {
            if ( $result = $db->fetchArray($resource) ) {
                $sqlArray[] = "UPDATE `$configTable` SET `wmc_value` = '$wmc_value', `wmc_update_datetime` = '$now' " .
                    " WHERE `wmc_config_id` = " . $result['wmc_config_id'] . ";";
            } else {
                $sqlArray[] = "INSERT INTO `$configTable` (`wmc_item`, `wmc_value`, `wmc_init_datetime`, `wmc_update_datetime`) " .
                    " VALUES ( '$wmc_item', '$wmc_value', '$now', '$now' );";
            }
        }
    }
    foreach ( $sqlArray as $sql ) {
        if ( ! $db->query($sql) ) {
            $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
                $this->_sFrontDirName . '/admin/admin.php?act=GeneralSetting', 3,
                sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_FAILED')) );
        }
    }
    WizXc_Util::clearCompiledCache();
    $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
        $this->_sFrontDirName . '/admin/admin.php?act=GeneralSetting', 3,
        sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_SUCCESS')) );
}

// get module config
$configs = $this->getConfigs();
$themes = $this->getMobileThemes();
$templateSets = $this->getTemplateSet();

//
// render admin view
//
// call header
require_once XOOPS_ROOT_PATH . '/header.php';

// display main templates
$xoopsTpl->assign( 'configs', $configs );
$xoopsTpl->assign( 'themes', $themes );
$xoopsTpl->assign( 'tplsets', $templateSets );
$xoopsTpl->display( $tplFile );

// call footer
require_once XOOPS_ROOT_PATH . '/footer.php';
