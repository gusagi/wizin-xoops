<?php
/**
 *
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

if ( ! class_exists('WizXc_Util') ) {
    require_once XOOPS_ROOT_PATH."/class/xoopsblock.php";
    require_once XOOPS_ROOT_PATH."/class/template.php";

    class WizXc_Util
    {
        function sessionDestroy()
        {
            $xcRoot =& XCube_Root::getSingleton();
            $xcRoot->mSession->destroy();
            $xcRoot->mSession->start();
            $_SESSION = array();
        }

        function installD3Templates( $module, &$log, $templatesDir )
        {
            $tplHandler =& xoops_gethandler('tplfile');
            $mid = $module->getVar( 'mid' );
            $dirname = $module->getVar( 'dirname' );

            if ( $handler = @ opendir($templatesDir . '/') ) {
                while ( ($fileName = readdir($handler)) !== false ) {
                    if ( strcmp(substr($fileName , 0 , 1), '.') === 0 ) {
                        continue ;
                    }
                    $filePath = $templatesDir . '/' . $fileName ;
                    if ( is_file($filePath) && is_readable($filePath) ) {

                        //
                        // Create template file object, then store it.
                        //
                        $tplfile =& $tplHandler->create();
                        $tplfile->setVar( 'tpl_refid', $mid );
                        $tplfile->setVar( 'tpl_lastimported', 0 );
                        $tplfile->setVar( 'tpl_lastmodified', time() );

                        if ( preg_match("/\.css$/i", $fileName) ) {
                            $tplfile->setVar( 'tpl_type', 'css' );
                        } else {
                            $tplfile->setVar( 'tpl_type', 'module' );
                        }

                        $source = file( $filePath );
                        $source = implode( '', $source );
                        $tplfile->setVar( 'tpl_source', $source, true );
                        $tplfile->setVar( 'tpl_module', $dirname );
                        $tplfile->setVar( 'tpl_tplset', 'default' );
                        $tplFileName = $dirname . '_' . $fileName;
                        $tplfile->setVar( 'tpl_file', $tplFileName, true );
                        $tplfile->setVar( 'tpl_desc', '', true );

                        if ( $tplHandler->insert($tplfile) ) {
                            $log->addReport( XCube_Utils::formatMessage(_AD_LEGACY_MESSAGE_TEMPLATE_INSTALLED, $fileName) );
                        } else {
                            $log->addError( XCube_Utils::formatMessage(_AD_LEGACY_ERROR_COULD_NOT_INSTALL_TEMPLATE, $fileName) );
                            return false;
                        }
                    }
                }
            }
            xoops_template_clear_module_cache( $module, $mid );
        }

        function createTableByFile( &$module, &$log, $filePath )
        {
            require_once XOOPS_MODULE_PATH . '/legacy/admin/class/Legacy_SQLScanner.class.php';
            $scanner =& new Legacy_SQLScanner();
            $scanner->setDB_PREFIX( XOOPS_DB_PREFIX );
            $scanner->setDirname( $module->get('dirname') );
            if ( ! $scanner->loadFile($filePath) ) {
                $log->addError( XCube_Utils::formatMessage(_AD_LEGACY_ERROR_SQL_FILE_NOT_FOUND, basename($filePath)) );
                return false;
            }
            $scanner->parse();
            $sqls = $scanner->getSQL();
            $db =& XoopsDatabaseFactory::getDatabaseConnection();
            foreach ( $sqls as $sql ) {
                if ( ! $db->query($sql) ) {
                    $log->addError( $db->error() );
                    return;
                }
            }
        }

        function getXoopsTpl()
        {
            $xoopsTpl = new XoopsTpl();
            return $xoopsTpl;
        }

        function getGTicketHtml( $params, &$xoopsTpl )
        {
            $gTicket = new XoopsGTicket();
            $salt = isset($params['salt']) ? $params['salt'] : XOOPS_SALT;
            $timeout = isset($params['timeout']) ? $params['timeout'] : 1800;
            $area = isset($params['area']) ? $params['area'] : '';
            return $gTicket->getTicketHtml( $salt, $timeout, $area );
        }

    }
}
