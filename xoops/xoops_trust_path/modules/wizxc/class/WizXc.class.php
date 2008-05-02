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

if ( ! class_exists('WizXc') ) {
    class WizXc
    {
        function WizXc()
        {
            WizXc::_require();
            WizXc::_define();
            WizXc::_setup();
            WizXc::_init();
        }

        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new WizXc();
            }
            return $instance;
        }

        function _require()
        {
            require_once XOOPS_TRUST_PATH . '/wizin/src/Wizin.class.php';
            require_once dirname( __FILE__ ) . '/WizXc_Util.class.php';
        }

        function _define()
        {
            define( 'WIZIN_CACHE_DIR', XOOPS_TRUST_PATH . '/cache' );
            $parseUrl = parse_url( XOOPS_URL );
            if ( ! empty($parseUrl['path']) ) {
                define( 'WIZXC_CURRENT_URI', str_replace($parseUrl['path'], '', XOOPS_URL) . getenv('REQUEST_URI') );
            } else {
                define( 'WIZXC_CURRENT_URI', XOOPS_URL . getenv('REQUEST_URI') );
            }
            $queryString = getenv( 'QUERY_STRING' );
            if ( ! empty($queryString) ) {
                define( 'WIZXC_URI_CONNECTOR', '&' );
            } else {
                define( 'WIZXC_URI_CONNECTOR', '?' );
            }
        }


        function _setup()
        {
            Wizin_Util::getPrefix( XOOPS_SALT );
        }

        function _init()
        {
            $xcRoot =& XCube_Root::getSingleton();
            $xcRoot->mDelegateManager->add( 'XoopsTpl.New' , array( $this , 'registerModifier' ) ) ;
        }

        function registerModifier( &$xoopsTpl )
        {
            $xoopsTpl->register_modifier( 'wiz_constant', array('Wizin_Util', 'constant') );
            $xoopsTpl->register_modifier( 'wiz_pager', array('Wizin_Util_Web', 'pager') );
        }

    }
}
