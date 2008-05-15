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

if ( ! class_exists('WizMobile_Action') ) {
    require_once XOOPS_TRUST_PATH . '/modules/wizxc/class/WizXc_Action.class.php';

    class WizMobile_Action extends WizXc_Action
    {
        function _setup()
        {
            $this->_sModuleDir = XOOPS_TRUST_PATH . '/modules/wizmobile';
            $this->_mFrontDirName = str_replace( '_wizmobile_action', '', strtolower(get_class($this)) );
        }

        function simpleLogin( &$xoopsUser )
        {
            $xcRoot = XCube_Root::getSingleton();
            $wizMobile =& WizMobile::getSingleton();
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $user = & Wizin_User::getSingleton();
            $user->checkClient( true );
            if ( ! $user->bIsMobile ) {
                header( "HTTP/1.1 404 Not Found" );
                exit();
            }
            $loginTable = $db->prefix( $this->_mFrontDirName . '_login' );
            $uniqId = md5( $user->sUniqId . XOOPS_SALT );
            // TODO : use ORM
            $sql = "SELECT `wml_uid` FROM `$loginTable` WHERE CAST(`wml_uniqid` AS BINARY) = '$uniqId' AND `wml_delete_datetime` = '0000-00-00 00:00:00';";
            if ( $resource = $db->query($sql) ) {
                $result = $db->fetchArray( $resource );
                if ( $result !== false && ! empty($result) ) {
                    /** This code block copied from "User_LegacypageFunctions" >> */
                    $handler =& xoops_gethandler('user');
                    $user =& $handler->get( $result['wml_uid'] );
                    $xoopsUser = $user;

                    //
                    // Regist to session
                    //
                    $xcRoot->mSession->regenerate();
                    $_SESSION = array();
                    $_SESSION['xoopsUserId'] = $xoopsUser->get('uid');
                    $_SESSION['xoopsUserGroups'] = $xoopsUser->getGroups();
                    /** This code block copied from "User_LegacypageFunctions" << */
                }
            }
            return ;
        }

        function registerUniqId()
        {
            $xcRoot = XCube_Root::getSingleton();
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $gTicket = new XoopsGTicket();
            if ( ! $gTicket->check(true, $this->_mFrontDirName, false) ) {
                $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
                    $this->_mFrontDirName . '/index.php?act=Setting', 1,
                    sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
            }
            $user = & Wizin_User::getSingleton();
            $user->checkClient( true );
            if ( ! $user->bIsMobile ) {
                header( "HTTP/1.1 404 Not Found" );
                exit();
            }
            $loginTable = $db->prefix( $this->_mFrontDirName . '_login' );
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
                    $this->_mFrontDirName . '/index.php?act=Setting', 1,
                    sprintf(Wizin_Util::constant('WIZMOBILE_MSG_REGISTER_UNIQID_SUCCESS'), $mode) );
            } else {
                $xcRoot->mController->executeRedirect( XOOPS_URL . '/modules/' .
                    $this->_mFrontDirName . '/index.php?act=Setting', 1,
                    sprintf(Wizin_Util::constant('WIZMOBILE_MSG_REGISTER_UNIQID_FAILED'), $mode) );
            }
            exit();
        }

        function getBlocks()
        {
            $xcRoot = XCube_Root::getSingleton();
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $newblocksTable = $db->prefix( 'newblocks' );
            $modulesTable = $db->prefix( 'modules' );
            $blocks = array();
            // TODO : use ORM
            $sql = "SELECT ";
            $sql .= " $newblocksTable.`bid`, $newblocksTable.`name` AS block_name, ";
            $sql .= " $newblocksTable.`title`, $newblocksTable.`dirname`, $newblocksTable.`visible`, ";
            $sql .= " $newblocksTable.`isactive`, $modulesTable.`name` AS module_name ";
            $sql .= " FROM `$newblocksTable` LEFT JOIN ";
            $sql .= " `$modulesTable` ON $modulesTable.`mid` = $newblocksTable.`mid` ";
            $sql .= " WHERE ";
            $sql .= " $newblocksTable.`visible` = 1 AND ";
            $sql .= " $newblocksTable.`isactive` = 1 ;";
            if ( $resource = $db->query($sql) ) {
                while ( $result = $db->fetchArray($resource) ) {
                    if ( $result !== false && ! empty($result) ) {
                        $blocks[] = $result;
                    }
                }
            }
            return $blocks;
        }

        function getNondisplayBlocks()
        {
            $nondisplayBlocks = array();
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $blockTable = $db->prefix( $this->_mFrontDirName . '_block' );
            // TODO : use ORM
            $sql = "SELECT `wmb_bid` FROM `$blockTable` WHERE `wmb_delete_datetime` = '0000-00-00 00:00:00';";
            if ( $resource = $db->query($sql) ) {
                while ( $result = $db->fetchArray($resource) ) {
                    if ( $result !== false && ! empty($result) ) {
                        $nondisplayBlocks[] = intval( $result['wmb_bid'] );
                    }
                }
            }
            return $nondisplayBlocks;
        }

        function updateNonDisplayBlocks()
        {
            $xcRoot = XCube_Root::getSingleton();
            $gTicket = new XoopsGTicket();
            if ( ! $gTicket->check(true, $this->_mFrontDirName, false) ) {
                $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
                    sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
            }
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $blockTable = $db->prefix( $this->_mFrontDirName . '_block' );
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
            $insertBlocks = array_diff( $_REQUEST['wmb_bid'], $nonDisplayBlocks );
            $updateBlocks = array_intersect( $_REQUEST['wmb_bid'], $nonDisplayBlocks );
            $deleteBlocks = array_merge( array_diff($nonDisplayBlocks, $existsBlocks),
                array_diff($nonDisplayBlocks, $_REQUEST['wmb_bid']) );
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
                    $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
                        sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_FAILED')) );
                }
            }
            $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
                sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_SUCCESS')) );
        }

        function getConfigs()
        {
            static $configs;
            if ( isset($configs) ) {
                return $configs;
            }
            $xcRoot = XCube_Root::getSingleton();
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $configTable = $db->prefix( $this->_mFrontDirName . '_config' );
            $configs = array();
            // TODO : use ORM
            $sql = "SELECT * FROM `$configTable` WHERE `wmc_delete_datetime` = '0000-00-00 00:00:00';";
            if ( $resource = $db->query($sql) ) {
                while ( $result = $db->fetchArray($resource) ) {
                    if ( $result !== false && ! empty($result) ) {
                        $wmc_item = $result['wmc_item'];
                        $configs[$wmc_item] = $result;
                    }
                }
            }
            return $configs;
        }

        function updateConfigs()
        {
            $xcRoot = XCube_Root::getSingleton();
            $gTicket = new XoopsGTicket();
            if ( ! $gTicket->check(true, $this->_mFrontDirName, false) ) {
                $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
                    sprintf(Wizin_Util::constant('WIZMOBILE_ERR_TICKET_NOT_FOUND')) );
            }
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            $configTable = $db->prefix( $this->_mFrontDirName . '_config' );
            $now = date( 'Y-m-d H:i:s' );
            $allowItems = array( 'login', 'theme', 'lookup', 'othermobile' );
            $sqlArray = array();
            foreach ( $_REQUEST['wmc_item'] as $wmc_item => $wmc_value ) {
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
                    $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
                        sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_FAILED')) );
                }
            }
            $xcRoot->mController->executeRedirect( WIZXC_CURRENT_URI, 3,
                sprintf(Wizin_Util::constant('WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_SUCCESS')) );
        }

        function getMobileThemes()
        {
            $themes = array();
            if ( $handler = opendir(XOOPS_THEME_PATH) ) {
                while ( ($dirname = readdir($handler)) !== false ) {
                    if ( $dirname === '.' || $dirname === '..' ) {
                        continue;
                    }

                    $themeDir = XOOPS_THEME_PATH . "/" . $dirname;
                    if ( is_dir($themeDir) ) {
                        if ( file_exists($themeDir . '/.legacy_wizmobilerendersystem') ) {
                            $themes[] = $dirname;
                        }
                    }
                }
                closedir($handler);
            }
            return $themes;
        }

    }
}

$className = $frontDirname . "_" . 'WizMobile_Action';
if ( ! class_exists($className) ) {
    eval( "class $className extends WizMobile_Action {}" );
}
