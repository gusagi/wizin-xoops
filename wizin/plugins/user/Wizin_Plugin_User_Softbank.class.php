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

if ( ! class_exists('Wizin_Plugin_User_Softbank') ) {
    class Wizin_Plugin_User_Softbank extends Wizin_StdClass
    {
        function __construct()
        {
            static $calledFlag;
            if ( ! isset($calledFlag) ) {
                $calledFlag = true;
                $this->_updateUniqId();
            }
        }

        function _updateUniqId()
        {
            $user =& Wizin_User::getSingleton();
            $user->sUniqId = substr( $user->sUniqId, 1 );
        }
    }
}
