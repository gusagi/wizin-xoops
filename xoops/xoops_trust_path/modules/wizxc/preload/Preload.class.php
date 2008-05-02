<?php
/**
 * call WizXC module init process preload
 *
 * PHP Versions 4
 *
 * @package  WizXC
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

if( ! defined( 'XOOPS_ROOT_PATH' ) ) exit ;

if ( ! class_exists('CallWizXC') && defined('XOOPS_TRUST_PATH') ) {
    class CallWizXC extends XCube_ActionFilter
    {
        function preFilter()
        {
            $initScript = XOOPS_TRUST_PATH . '/modules/wizxc/init.php';
            if ( file_exists($initScript) && is_readable($initScript) ) {
                require_once $initScript;
            }
            parent::preFilter();
        }

        function preBlockFilter()
        {
            parent::preBlockFilter();
        }

        function postFilter()
        {
            parent::postFilter();
        }
    }
}
