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
    class WizMobile_Action extends Wizin_StdClass
    {
        function execute()
        {
            $act = ( ! empty($_REQUEST['act']) )? $_REQUEST['act']: 'index';
            $path = XOOPS_TRUST_PATH . '/modules/wizmobile/';
            if ( $this->_sMode === 'admin' ) {
                $path .= 'admin/';
            } else {
                $path .= 'main/';
            }
            $actionFile = $path . $act . '.php';
            if ( file_exists($actionFile) ) {
                require $actionFile;
            }
        }

        function executeAdmin()
        {
            $this->_sMode = 'admin';
            $this->execute();
        }

        function getNondisplayBlocks()
        {
            $nondisplayBlocks = array();
            if ( getenv('SERVER_NAME') === 'www.gusagi.com' ) {
                $nondisplayBlocks = array( 20, 23, 28, 33, 41, 42 );
            }
            return $nondisplayBlocks;
        }
    }
}

$className = $frontDirname . "_" . 'WizMobile_Action';
if ( ! class_exists($className) ) {
    eval( "class $className extends WizMobile_Action {}" );
}
