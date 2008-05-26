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

if ( ! defined('XOOPS_ROOT_PATH') || ! defined('XOOPS_TRUST_PATH') ) {
    exit();
}

require_once XOOPS_TRUST_PATH . '/wizin/src/Wizin_Util.class.php';
$frontDirname = basename( dirname($frontFile) );
$language = empty( $GLOBALS['xoopsConfig']['language'] ) ? 'english' : $GLOBALS['xoopsConfig']['language'];
if( file_exists( XOOPS_ROOT_PATH . '/modules/' . $frontDirname . '/language/' . $language . '/main.php' ) ) {
    require XOOPS_ROOT_PATH . '/modules/' . $frontDirname . '/language/' . $language . '/main.php';
}
if( file_exists( dirname(__FILE__) . '/language/' . $language . '/main.php' ) ) {
    require dirname(__FILE__) . '/language/' . $language . '/main.php';
}

// module infomation
$modversion = array();
$modversion['name']        = Wizin_Util::constant( 'WIZMOBILE_MODINFO_NAME' );
$modversion['version']     = '0.22';
$modversion['description'] = Wizin_Util::constant( 'WIZMOBILE_MODINFO_DESC' );
$modversion['credits']     = 'Makoto Hashiguchi a.k.a. gusagi';
$modversion['author']      = 'Makoto Hashiguchi a.k.a. gusagi &lt;gusagi&#64;gusagi.com&gt;<br />url : http://www.gusagi.com';
$modversion['license']     = 'GNU General Public License';
$modversion['official']    = 0;
$modversion['image']       = file_exists( dirname($frontFile) .'/modicon.png' ) ? 'modicon.png' : 'modicon.php';
$modversion['dirname']     = basename( dirname($frontFile) );
$modversion['use_smarty'] = 0;
$modversion['cube_style'] = true;

// installer
$modversion['disable_legacy_2nd_installer'] = true;
$modversion['legacy_installer']['installer']['filepath'] = dirname( __FILE__ ) . '/class/WizMobile_Installer.class.php';
$modversion['legacy_installer']['installer']['class'] = 'WizMobile_Installer';

// updater
$modversion['legacy_installer']['updater']['filepath'] = dirname( __FILE__ ) . '/class/WizMobile_Updater.class.php';
$modversion['legacy_installer']['updater']['class'] = 'WizMobile_Updater';

// database
$modversion['sqlfile']['mysql'] = "";
$modversion['tables'][] = "{prefix}_{dirname}_login";
$modversion['tables'][] = "{prefix}_{dirname}_config";
$modversion['tables'][] = "{prefix}_{dirname}_block";

/*
// Templates
$modversion['templates'][] = array( 'file' => 'user_userinfo.html',
    'description' => 'Display a user information in userinfo.php' );
*/

// access permission
$modversion['read_any']  = true;

// main menu
$modversion['hasMain']   = 0;

// admin view
$modversion['hasAdmin']   = 1;
$modversion['adminindex'] = 'admin/admin.php?act=SystemStatus';
$modversion['adminmenu']  = 'adminmenu.php';

// search
$modversion['hasSearch'] = 0;

// comments
$modversion['hasComments'] = 0;

// notification
$modversion['hasNotification'] = 0;
