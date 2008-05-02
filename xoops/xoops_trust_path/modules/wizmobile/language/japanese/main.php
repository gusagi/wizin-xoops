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

// module info
Wizin_Util::define( 'WIZMOBILE_MODINFO_NAME', '携帯対応モジュール' );
Wizin_Util::define( 'WIZMOBILE_MODINFO_DESC', 'XOOPS Cube Legacyで構築したサイトを携帯でも利用可能にするモジュール' );

// message
Wizin_Util::define( 'WIZMOBILE_MSG_DENY_ADMIN_AREA', '申し訳ありませんが、携帯から管理画面は操作出来ません<br />PCでの操作をお願い致します' );
Wizin_Util::define( 'WIZMOBILE_MSG_SESSION_LIMIT_TIME', '携帯端末で有効なセッションの継続時間を過ぎました<br />申し訳ありませんが、もう一度ログインし直して下さい' );

// error message
Wizin_Util::define( 'WIZMOBILE_ERR_PHP_VERSION', 'このモジュールは、PHP4.4以上でなければインストール出来ません' );

// language for public area

// language for admin area
Wizin_Util::define( 'WIZMOBILE_LANG_INTRODUCTION', 'はじめに' );

// language for all area

// language for theme
Wizin_Util::define( 'WIZMOBILE_LANG_LOGIN', 'ログイン' );
Wizin_Util::define( 'WIZMOBILE_LANG_LOGOUT', 'ログアウト' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGE_TOP', '▲上へ' );
Wizin_Util::define( 'WIZMOBILE_LANG_PAGE_BOTTOM', '▼下へ' );
Wizin_Util::define( 'WIZMOBILE_LANG_MAIN_CONTENTS', 'メインコンテンツ' );
Wizin_Util::define( 'WIZMOBILE_LANG_SEARCH', '検索' );
