<?php
/**
 *
 * PHP Versions 4
 *
 * @package  WizXc
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
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
            require_once dirname( __FILE__ ) . '/WizXcUtil.class.php';
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
            $wizin =& Wizin::getSingleton();
            Wizin_Util::getPrefix( XOOPS_SALT );
        }

    }
}
