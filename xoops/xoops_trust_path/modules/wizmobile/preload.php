<?php
/**
 * WizMobile module index script for XOOPS Cube Legacy2.1
 *
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

// direct access protect
$scriptFileName = getenv( 'SCRIPT_FILENAME' );
if ( $scriptFileName === __FILE__ ) {
    exit();
}

$frontDirname = basename( dirname(dirname($frontFile)) );
require dirname( __FILE__ ) . '/init.php';

if ( class_exists('Wizin') ) {
    require dirname( __FILE__ ) . '/class/WizMobile.class.php';

    // execute
    $wizMobile =& WizMobile::getSingleton();
    $actionScript = dirname( __FILE__ ) . '/class/WizMobile_Action.class.php';
    if ( file_exists($actionScript) ) {
        require $actionScript;
        if ( class_exists($className) ) {
            $wizMobile->sActionClassName = $className;
        }
    }
    $preloadScript = dirname( __FILE__ ) . '/preload/WizMobile_Preload.class.php';
    if ( file_exists($preloadScript) ) {
        require $preloadScript;
    }
}
