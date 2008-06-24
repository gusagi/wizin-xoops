<?php
/**
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

if ( ! class_exists('WizXc_Updater') ) {
    require_once XOOPS_ROOT_PATH . '/modules/legacy/admin/class/ModuleUpdater.class.php';
    require_once dirname( __FILE__ ) . '/WizXc_Util.class.php';

    class WizXc_Updater extends Legacy_ModulePhasedUpgrader
    {
        function executeUpgrade()
        {
            //
            // clear theme cache
            //
            if ( $handler = opendir(XOOPS_COMPILE_PATH) ) {
                while ( ($file = readdir($handler)) !== false ) {
                    if ( $file === '.' || $file === '..' ) {
                        continue;
                    }
                    if ( substr($file, -4) === '.php' ) {
                        unlink( XOOPS_COMPILE_PATH . '/' . $file );
                    }
                }
                closedir($handler);
            }
            parent::executeUpgrade();
        }

        /**
         * Updates all of module templates.
         *
         * @access protected
         * @note You may do custom
         */
        function _updateModuleTemplates()
        {
            parent::_updateModuleTemplates();
            $myTrustDirFile = XOOPS_ROOT_PATH . '/modules/' . $this->_mTargetXoopsModule->getVar( 'dirname' ) . '/mytrustdirname.php';
            if ( file_exists($myTrustDirFile) && is_readable($myTrustDirFile) ) {
                include $myTrustDirFile;
                $templatesDir = XOOPS_TRUST_PATH . '/modules/' . $mytrustdirname . '/templates';
                if ( file_exists($templatesDir) && is_dir($templatesDir) ) {
                    WizXc_Util::installD3Templates( $this->_mTargetXoopsModule, $this->mLog, $templatesDir );
                }
            }
        }
    }
}
