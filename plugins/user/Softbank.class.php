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

if (! class_exists('Wizin_Plugin_User_Softbank')) {
    require dirname(__FILE__) .'/Mobile.class.php';
    class Wizin_Plugin_User_Softbank extends Wizin_Plugin_User_Mobile
    {
        function _setup()
        {
            $this->_check3GC();
            $this->_updateUniqId();
            parent::_setup();
        }

        function _check3GC()
        {
            $agent = getenv('HTTP_USER_AGENT');
            $user =& Wizin_User::getSingleton();
            $pattern = '/^j\-phone\//i';
            if (preg_match($pattern, $agent, $matches)) {
                $user->sEncoding = 'sjis-win';
                $user->sCharset = 'shift_jis';
            }
        }

        function _updateUniqId()
        {
            $user =& Wizin_User::getSingleton();
            if (strlen($user->sUniqId) === 16) {
                $user->sUniqId = substr($user->sUniqId, 1);
            }
        }

        function _getModel()
        {
            $user =& Wizin_User::getSingleton();
            // get model name from request header
            $model = getenv('HTTP_X_JPHONE_MSNAME');
            if (! empty($model)) {
                $user->sModel = $model;
            }
        }
    }
}
