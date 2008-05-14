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

if ( ! class_exists('WizMobile_Updater') ) {
    require_once XOOPS_TRUST_PATH . '/modules/wizxc/class/WizXc_Updater.class.php';

    class WizMobile_Updater extends WizXc_Updater
    {
        var $_mMilestone = array( '020' => 'updateTo020' );

        function updateTo020()
        {
            // thumbnail directory permission check
            $thumbnailDir = XOOPS_ROOT_PATH . '/uploads/wizmobile';
            if ( ! file_exists($thumbnailDir) || ! is_dir($thumbnailDir) ) {
                $this->mLog->addError( "Failed to update : Prease cleate '" . $thumbnailDir . "' directory.");
                return false;
            }
            if ( ! is_writable($thumbnailDir) ) {
                $this->mLog->addError( "Failed to update : " . $thumbnailDir . " needs writable permission. Prease check it's permission.");
                return false;
            }
            /** This code block copied from "Legacy_ModuleInstaller" >> */
            //
            // Add a permission which administrators can manage.
            //
            $gpermHandler =& xoops_gethandler('groupperm');
            $adminPerm =& $gpermHandler->create();
            $adminPerm->setVar('gperm_groupid', XOOPS_GROUP_ADMIN);
            $adminPerm->setVar('gperm_itemid', $this->_mTargetXoopsModule->getVar('mid'));
            $adminPerm->setVar('gperm_modid', 1);
            $adminPerm->setVar('gperm_name', 'module_admin');
            if (!$gpermHandler->insert($adminPerm)) {
                $this->mLog->addError( _AD_LEGACY_ERROR_COULD_NOT_SET_ADMIN_PERMISSION );
                return false;
            }
            $memberHandler =& xoops_gethandler( 'member' );
            $groupObjects =& $memberHandler->getGroups();
            //
            // Add a permission all group members and guest can read.
            //
            foreach ( $groupObjects as $group ) {
                $readPerm =& $this->_createPermission( $group->getVar('groupid') );
                $readPerm->setVar( 'gperm_name', 'module_read' );
                if ( ! $gpermHandler->insert($readPerm) ) {
                    $this->mLog->addError( _AD_LEGACY_ERROR_COULD_NOT_SET_READ_PERMISSION );
                }
            }
            /** This code block copied from "Legacy_ModuleInstaller" << */

            //
            // Create tables
            //
            $sqlFilePath = dirname( dirname(__FILE__) ) . '/sql/mysql.020.sql';
            if ( file_exists($sqlFilePath) && is_readable($sqlFilePath) ) {
                WizXc_Util::createTableByFile( $this->_mTargetXoopsModule, $this->mLog, $sqlFilePath );
            }

            $this->_mTargetXoopsModule->set('version', '20');
            return $this->executeAutomaticUpgrade();
        }
    }
}

$mod_dir = basename( dirname($frontFile) );
$installerClass = ucfirst($mod_dir) . "_WizMobile_Updater";
if ( ! class_exists($installerClass) ) {
    eval( "class $className extends WizMobile_Updater {}" );
}
