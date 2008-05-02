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

if (!defined('XOOPS_ROOT_PATH')) exit();

if ( !defined('LEGACY_RENDER_TARGET_TYPE_BUFFER') ) {
    include_once( XOOPS_ROOT_PATH . '/modules/legacyRender/kernel/Legacy_RenderTarget.class.php' );
}

if( ! class_exists( 'Legacy_WizMobileThemeRenderTarget' ) ) {
    class Legacy_WizMobileThemeRenderTarget extends Legacy_ThemeRenderTarget
    {
    	function sendHeader()
    	{
    		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    		header('Cache-Control: no-store, no-cache, must-revalidate');
    		header('Cache-Control: post-check=0, pre-check=0', false);
    		header('Pragma: no-cache');
    	}
    }
}

if( ! class_exists( 'Legacy_WizMobileDialogRenderTarget' ) ) {
    class Legacy_WizMobileDialogRenderTarget extends Legacy_DialogRenderTarget
    {
    	function sendHeader()
    	{
    		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    		header('Cache-Control: no-store, no-cache, must-revalidate');
    		header('Cache-Control: post-check=0, pre-check=0', false);
    		header('Pragma: no-cache');
    	}
    }
}
