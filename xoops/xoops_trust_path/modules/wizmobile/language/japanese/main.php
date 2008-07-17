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
Wizin_Util::define( 'WIZMOBILE_MODINFO_NAME', '�����б��⥸�塼��' );
Wizin_Util::define( 'WIZMOBILE_MODINFO_DESC', 'XOOPS Cube Legacy�ǹ��ۤ��������Ȥ���ӤǤ����Ѳ�ǽ�ˤ���⥸�塼��' );

//
// message
//

// main area / all
Wizin_Util::define( 'WIZMOBILE_MSG_DENY_LOGIN_PAGE', '����������ޤ��󤬡����Υ����ȤϷ��Ӥ���ϥ��������ޤ���<br />PC�Ǥ����򤪴ꤤ�פ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_DENY_ADMIN_AREA', '����������ޤ��󤬡����Ӥ���������̤�������ޤ���<br />PC�Ǥ����򤪴ꤤ�פ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_SESSION_LIMIT_TIME', '����ü����ͭ���ʥ��å����η�³���֤�᤮�ޤ���<br />����������ޤ��󤬡��⤦���٥�����ľ���Ʋ�����' );

// main area / simple login
Wizin_Util::define( 'WIZMOBILE_MSG_SIMPLE_LOGIN_CAUTION', '��ñ����������Ѥˤʤ��硢�������ˡֵ���ID��Ͽ�פ�ԤäƤ���ɬ�פ�����ޤ���' );

// admin area / system status
Wizin_Util::define( 'WIZMOBILE_MSG_CONTROLLER_IS_NOT_EXCHANGED', '����ȥ��餬��������Ƥ��ޤ���' );
Wizin_Util::define( 'WIZMOBILE_MSG_CONTROLLER_PATCH', '�ʲ��Υ����ɤ� ' . XOOPS_ROOT_PATH . '/settings/site_custom.ini.php �˽񤭹���ǲ�����' );
Wizin_Util::define( 'WIZMOBILE_MSG_GD_NOT_EXISTS', 'GD�饤�֥�꤬¸�ߤ��ʤ����ᡢ�����Υꥵ������ǽ��̵���ȤʤäƤ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_RESIZED_IMAGE_DIR_NOT_EXISTS', XOOPS_ROOT_PATH . '/uploads/wizmobile ��¸�ߤ��ʤ����ᡢ�����Υꥵ������ǽ��̵���ȤʤäƤ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_RESIZED_IMAGE_DIR_NOT_WRITABLE', XOOPS_ROOT_PATH . '/uploads/wizmobile �˽񤭹��߸��¤��ʤ����ᡢ�����Υꥵ������ǽ��̵���ȤʤäƤ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_DOM_NOT_EXISTS', 'DOM��ĥ��¸�ߤ��ʤ����ᡢ�ڡ���ʬ�䵡ǽ��̵���ȤʤäƤ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_SIMPLEXML_NOT_EXISTS', 'SimpleXML��¸�ߤ��ʤ����ᡢ�ڡ���ʬ�䵡ǽ��̵���ȤʤäƤ��ޤ�' );
Wizin_Util::define( 'WIZMOBILE_MSG_TIDY_NOT_EXISTS', 'Tidy��ĥ��¸�ߤ��ʤ����ᡢHTML�μ�ư�����ϹԤ��ޤ���' );

// main area / register uniq id
Wizin_Util::define( 'WIZMOBILE_MSG_REGISTER_UNIQID_SUCCESS', '����ID��%s����λ���ޤ���' );
Wizin_Util::define( 'WIZMOBILE_MSG_REGISTER_UNIQID_FAILED', '����ID��%s�˼��Ԥ��ޤ���' );
Wizin_Util::define( 'WIZMOBILE_MSG_REGISTER_UNIQID', '��ñ����������Ѥ�����Ӥε���ID����Ͽ���ޤ�������Ͽ�Ѥߤξ��ϵ���ID�򹹿����ޤ���<br />����ID����Ͽ����ȡ���ñ������ܥ���򥯥�å���������ǡ������󤬽����褦�ˤʤ�ޤ���' );
Wizin_Util::define( 'WIZMOBILE_MSG_CANNOT_GET_UNIQID', '����ID����������ޤ���<br />����ID��������ػߤ��Ƥ��ʤ�������ǧ���Ʋ�����' );

// admin area / block setting
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_SUCCESS', '��ɽ���֥�å�����ι�������λ���ޤ���' );
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_BLOCK_SETTING_FAILED', '��ɽ���֥�å�����ι����˼��Ԥ��ޤ���' );

// admin area / general setting
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_SUCCESS', '��������ι�������λ���ޤ���' );
Wizin_Util::define( 'WIZMOBILE_MSG_UPDATE_GENERAL_SETTING_FAILED', '��������ι����˼��Ԥ��ޤ���' );


//
// error message
//
Wizin_Util::define( 'WIZMOBILE_ERR_PHP_VERSION', '���Υ⥸�塼��ϡ�PHP4.4�ʾ�Ǥʤ���Х��󥹥ȡ������ޤ���' );
Wizin_Util::define( 'WIZMOBILE_ERR_TICKET_NOT_FOUND', '��󥿥�������åȤ����Ĥ���ޤ���<br />������Ǥ������⤦�������򤪴ꤤ���ޤ�' );

//
// language for main area
//
Wizin_Util::define( 'WIZMOBILE_LANG_SIMPLE_LOGIN', '��ñ������' );
Wizin_Util::define( 'WIZMOBILE_LANG_REGISTER_UNIQID', '����ID��Ͽ' );

//
// language for admin area
//
Wizin_Util::define( 'WIZMOBILE_LANG_SYSTEM_STATUS', '�����ƥ�ξ���' );
Wizin_Util::define( 'WIZMOBILE_LANG_NON_DISPLAY_BLOCK_SETTING', '��ɽ���֥�å�������' );
Wizin_Util::define( 'WIZMOBILE_LANG_GENERAL_SETTING', '��������' );

// system status
Wizin_Util::define( 'WIZMOBILE_LANG_EXCHANGE_CONTROLLER', '����ȥ���δ���' );
Wizin_Util::define( 'WIZMOBILE_LANG_IMAGE_RESIZE', '�����Υꥵ����' );
Wizin_Util::define( 'WIZMOBILE_LANG_PARTITION_PAGE', '�ڡ���ʬ��' );

// non display block setting
Wizin_Util::define( 'WIZMOBILE_LANG_BLOCK_TITLE', '�֥�å������ȥ�' );
Wizin_Util::define( 'WIZMOBILE_LANG_MODULE_NAME', '�⥸�塼��̾' );
Wizin_Util::define( 'WIZMOBILE_LANG_DIRNAME', '�ǥ��쥯�ȥ�' );
Wizin_Util::define( 'WIZMOBILE_LANG_NON_DISPLAY', '��ɽ��' );

// general setting
Wizin_Util::define( 'WIZMOBILE_LANG_ITEM', '����' );
Wizin_Util::define( 'WIZMOBILE_LANG_VALUE', '������' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN', '������' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN_DESC', '�����ѤΥ�����ǽ����<br />ͭ���ˤ��뤳�Ȥǡ���ñ����������Ѳ�ǽ�ˤʤ�ޤ�' );
Wizin_Util::define( 'WIZMOBILE_LANG_THEME', '�ơ���' );
Wizin_Util::define( 'WIZMOBILE_LANG_THEME_DESC', '�����ѤΥơ�������' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOOKUP', '�ۥ���̾�εհ���' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOOKUP_DESC', '���Ӥ���Υ����������ɤ������ۥ���̾��հ������Ƴ�ǧ<br />�桼������������Ȥε������ɤ����Ȥ���������ꡢ�ѥե����ޥ󥹤��㲼���ޤ�' );
Wizin_Util::define( 'WIZMOBILE_LANG_OTHERMOBILE', '����¾ü���η����б�' );
Wizin_Util::define( 'WIZMOBILE_LANG_OTHERMOBILE_DESC', '���ޡ��ȥե���ʤɰ�����ü�����Ф��Ʒ����б���Ԥ����ϡ�ͭ�������򤷤Ʋ�����' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGER', '�ڡ���ʬ��' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGER_DESC', '���Ӥ���Υ��������ǡ�����ƥ����ʬ�Υڡ���ʬ���Ԥ����ϡ�ͭ�������򤷤Ʋ�����' );
Wizin_Util::define( 'WIZMOBILE_LANG_CONTENT_TYPE', '����ƥ�ĥ�����' );
Wizin_Util::define( 'WIZMOBILE_LANG_CONTENT_TYPE_DESC', '���Ӥ���Υ����������Ф��ơ�����ƥ�Ĥ����Ф��륿���פ����򤷤Ʋ�����' );


//
// language for all area
//
Wizin_Util::define( 'WIZMOBILE_LANG_SETTING', '����' );
Wizin_Util::define( 'WIZMOBILE_LANG_REGISTER', '��Ͽ' );
Wizin_Util::define( 'WIZMOBILE_LANG_UPDATE', '����' );
Wizin_Util::define( 'WIZMOBILE_LANG_DELETE', '���' );
Wizin_Util::define( 'WIZMOBILE_LANG_ENABLE', 'ͭ��' );
Wizin_Util::define( 'WIZMOBILE_LANG_DISABLE', '̵��' );

//
// language for theme
//
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN', '������' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGOUT', '��������' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGE_TOP', '�����' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGE_BOTTOM', '������' );
Wizin_Util::define( 'WIZMOBILE_LANG_MAIN_CONTENTS', '�ᥤ�󥳥�ƥ��' );
Wizin_Util::define( 'WIZMOBILE_LANG_SEARCH', '����' );
