<?php
/**
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Plugin_User_Googlebot') ) {
    class Wizin_Plugin_User_Googlebot extends Wizin_StdClass
    {
        function __construct()
        {
            $this->_require();
            $this->_setup();
        }

        function _require()
        {
        }

        function _setup()
        {
            $this->_checkMobile();
        }

        function _checkMobile()
        {
            $agent = getenv( 'HTTP_USER_AGENT' );
            $user =& Wizin_User::getSingleton();
            $pattern = '/([a-zA-Z_0-9\/.;() -]+)(googlebot-mobile)([a-zA-Z_0-9\/.;() -]+)/i';
            if ( preg_match($pattern, $agent, $matches) ) {
                $user->bIsMobile = true;
            } else {
                $user->bIsMobile = false;
            }
        }
    }
}
