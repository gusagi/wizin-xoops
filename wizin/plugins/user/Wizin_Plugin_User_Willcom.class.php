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

if ( ! class_exists('Wizin_Plugin_User_Willcom') ) {
    class Wizin_Plugin_User_Willcom extends Wizin_StdClass
    {
        function __construct()
        {
            static $calledFlag;
            if ( ! isset($calledFlag) ) {
                $calledFlag = true;
                $this->_advancedCheck();
            }
        }

        function _advancedCheck()
        {
            $user =& Wizin_User::getSingleton();
            if ( $user->_bLookup ) {
                $agent = getenv( 'HTTP_USER_AGENT' );
                if ( ! preg_match("/(willcom|ddipocket)/i", $agent) ) {
                    $user->bIsMobile = false;
                    $user->bIsBot = false;
                    $user->sCarrier = 'othermobile';
                    $user->sUniqId = '';
                    $user->sEncoding = 'sjis-win';
                    $user->sCharset = 'shift_jis';
                }
            }
            return null;
        }
    }
}
