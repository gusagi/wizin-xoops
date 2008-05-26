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

//
// module info
//
Wizin_Util::define( 'WIZMOBILE_MODINFO_NAME', 'WizMobile' );
Wizin_Util::define( 'WIZMOBILE_MODINFO_DESC', 'Web site which with XOOPS Cube Legacy was made can be utilized by the portable telephone.' );

//
// message
//

// main area / all
Wizin_Util::define( 'WIZMOBILE_MSG_DENY_LOGIN_PAGE', 'Sorry, it cannot login from the portable telephone,<br /> we request the operation with PC' );
Wizin_Util::define( 'WIZMOBILE_MSG_DENY_ADMIN_AREA', 'Sorry, it cannot operate Admin Area from the portable telephone,<br /> we request the operation with PC' );
Wizin_Util::define( 'WIZMOBILE_MSG_SESSION_LIMIT_TIME', 'It passed the existence time of session for portable telephone.<br />There is no excuse, but login please do again to do once more.' );

// main area / simple login
Wizin_Util::define( 'WIZMOBILE_MSG_SIMPLE_LOGIN_CAUTION', 'When you utilize simple login, it is necessary to do the "Register of terminal specific ID" after the login.' );

// admin area / system status
Wizin_Util::define( 'WIZMOBILE_MSG_CONTROLLER_IS_NOT_EXCHANGED', 'Controller class is not exchanged.' );
Wizin_Util::define( 'WIZMOBILE_MSG_CONTROLLER_PATCH', 'Please write this code in ' . XOOPS_ROOT_PATH . '/settings/site_custom.ini.php .' );
Wizin_Util::define( 'WIZMOBILE_MSG_GD_NOT_EXISTS', 'Because the GD library does not exist, resize function of the image has become invalid.' );
Wizin_Util::define( 'WIZMOBILE_MSG_RESIZED_IMAGE_DIR_NOT_EXISTS', 'Because ' . XOOPS_ROOT_PATH . '/uploads/wizmobile directory does not exist, resize function of the image has become invalid.' );
Wizin_Util::define( 'WIZMOBILE_MSG_RESIZED_IMAGE_DIR_NOT_WRITABLE', 'Because ' . XOOPS_ROOT_PATH . '/uploads/wizmobile directory is not writable, resize function of the image has become invalid.' );
Wizin_Util::define( 'WIZMOBILE_MSG_DOM_NOT_EXISTS', 'Because DOMDocument class does not exist, page divided function has become invalid.' );
Wizin_Util::define( 'WIZMOBILE_MSG_SIMPLEXML_NOT_EXISTS', 'Because SimpleXMLElement class does not exist, page divided function has become invalid.' );
Wizin_Util::define( 'WIZMOBILE_MSG_TIDY_NOT_EXISTS', 'Because Tidy extension does not exist, the automatic operation correction of HTML is not done.' );

// main area / register uniq id
Wizin_Util::define( 'WIZMOBILE_MSG_REGISTER_UNIQID_SUCCESS', '%s of terminal specific ID completed.' );
Wizin_Util::define( 'WIZMOBILE_MSG_REGISTER_UNIQID_FAILED', 'It failed in %s of terminal specific ID.' );
Wizin_Util::define( 'WIZMOBILE_MSG_REGISTER_UNIQID', 'The terminal specific ID which is utilized with simple login is registered. (In case of the register being completed terminal specific ID is updated).<br />When terminal specific ID is registered, it reaches the point which just clicks the simple login button, can do login.' );
Wizin_Util::define( 'WIZMOBILE_MSG_CANNOT_GET_UNIQID', 'It cannot get The terminal specific ID.<br />Please check whether it does not prohibit the terminal specific ID.' );

// admin area / block setting
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_SUCCESS', 'Update of non display block setting completed.' );
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_FAILED', 'It failed in update of non display block setting.' );

// admin area / general setting
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_SUCCESS', 'Update of generality setting completed.' );
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_FAILED', 'It failed in update of generality setting.' );


//
// error message
//
Wizin_Util::define( 'WIZMOBILE_ERR_PHP_VERSION', 'Sorry, this module cannot install, because it needs PHP4.4.X or upper version.' );
Wizin_Util::define( 'WIZMOBILE_ERR_TICKET_NOT_FOUND', 'The one-time ticket is not found.<br />Sorry, but we ask operation once more, please.' );

//
// language for main area
//
Wizin_Util::define( 'WIZMOBILE_LANG_SIMPLE_LOGIN', 'Simple Login' );
Wizin_Util::define( 'WIZMOBILE_LANG_REGISTER_UNIQID', 'Register terminal specific ID' );

//
// language for admin area
//
Wizin_Util::define( 'WIZMOBILE_LANG_SYSTEM_STATUS', 'System status' );
Wizin_Util::define( 'WIZMOBILE_LANG_NON_DISPLAY_BLOCK_SETTING', 'Non display block setting' );
Wizin_Util::define( 'WIZMOBILE_LANG_GENERAL_SETTING', 'Generality setting' );

// system status
Wizin_Util::define( 'WIZMOBILE_LANG_EXCHANGE_CONTROLLER', 'Exchange Controller' );
Wizin_Util::define( 'WIZMOBILE_LANG_IMAGE_RESIZE', 'Resize of image' );
Wizin_Util::define( 'WIZMOBILE_LANG_PARTITION_PAGE', 'Page division' );

// non display block setting
Wizin_Util::define( 'WIZMOBILE_LANG_BLOCK_TITLE', 'Block title' );
Wizin_Util::define( 'WIZMOBILE_LANG_MODULE_NAME', 'Module name' );
Wizin_Util::define( 'WIZMOBILE_LANG_DIRNAME', 'Directory' );
Wizin_Util::define( 'WIZMOBILE_LANG_NON_DISPLAY', 'Non display' );

// non general setting
Wizin_Util::define( 'WIZMOBILE_LANG_ITEM', 'Item' );
Wizin_Util::define( 'WIZMOBILE_LANG_VALUE', 'Value' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN', 'Login' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN_DESC', 'Login functional setting for mobile.<br />When it makes enable, also simple login becomes available.' );
Wizin_Util::define( 'WIZMOBILE_LANG_THEME', 'Theme' );
Wizin_Util::define( 'WIZMOBILE_LANG_THEME_DESC', 'Theme setting for mobile.' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOOKUP', 'Lookup host name' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOOKUP_DESC', 'Lookup host name, you verify whether access from mobile.<br />Instead of being able prevent the disguise of the user agent, performance decreases.' );
Wizin_Util::define( 'WIZMOBILE_LANG_OTHERMOBILE', 'Correspondence of other mobile terminals' );
Wizin_Util::define( 'WIZMOBILE_LANG_OTHERMOBILE_DESC', 'When it corresponds portably vis-a-vis the terminal of part such as smart phone, please select enable' );


//
// language for all area
//
Wizin_Util::define( 'WIZMOBILE_LANG_SETTING', 'Setting' );
Wizin_Util::define( 'WIZMOBILE_LANG_REGISTER', 'Register' );
Wizin_Util::define( 'WIZMOBILE_LANG_UPDATE', 'Update' );
Wizin_Util::define( 'WIZMOBILE_LANG_DELETE', 'Delete' );
Wizin_Util::define( 'WIZMOBILE_LANG_ENABLE', 'Enable' );
Wizin_Util::define( 'WIZMOBILE_LANG_DISABLE', 'Disable' );

//
// language for theme
//
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN', 'Login' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGOUT', 'Logout' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGE_TOP', 'Page Top' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGE_BOTTOM', 'Page Bottom' );
Wizin_Util::define( 'WIZMOBILE_LANG_MAIN_CONTENTS', 'Main Contents' );
Wizin_Util::define( 'WIZMOBILE_LANG_SEARCH', 'Search' );
