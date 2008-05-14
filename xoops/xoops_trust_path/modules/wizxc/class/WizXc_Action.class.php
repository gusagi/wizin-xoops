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

if ( ! class_exists('WizXc_Action') ) {
    class WizXc_Action extends Wizin_StdClass
    {
        function __construct()
        {
            $this->_require();
            $this->_define();
            $this->_setup();
            $this->_init();
        }

        function _require()
        {
        }

        function _define()
        {
        }

        function _setup()
        {
            $this->_sModuleDir = XOOPS_TRUST_PATH . '/modules/wizxc';
        }

        function _init()
        {
        }

        function execute()
        {
            $act = ( ! empty($_REQUEST['act']) )? $_REQUEST['act']: 'index';
            $path = $this->_sModuleDir;
            if ( $this->_sMode === 'admin' ) {
                $path .= '/admin/';
            } else {
                $path .= '/main/';
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

    }
}
