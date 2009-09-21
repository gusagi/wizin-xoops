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

if (! class_exists('Wizin_Plugin_User_Mobile')) {
    class Wizin_Plugin_User_Mobile extends Wizin_StdClass
    {
        function __construct()
        {
            $this->_require();
            $this->_setup();
        }

        function _require()
        {
            require_once WIZIN_ROOT_PATH . '/src/util/Web.class.php';
        }

        function _setup()
        {
            $this->iModelIdx = 3;
            $this->iWidthIdx = 7;
            static $calledFlag;
            if (! isset($calledFlag)) {
                $calledFlag = true;
                Wizin_Util_Web::setCheckLocationHeader();
                $this->_getModel();
            }
            $this->_updateDevice();
        }

        function _getModel()
        {
        }

        function _updateDevice()
        {
            $user =& Wizin_User::getSingleton();
            $specList = $this->_getSpecList();
            if (! empty($specList)) {
                $user->iWidth = intval($specList[$this->iWidthIdx]);
            }
        }

        function _getSpecList()
        {
            static $specList;
            $user =& Wizin_User::getSingleton();
            if (! isset($specList)) {
                $specList = array();
                if ($user->sModel !== '') {
                    $specFile = WIZIN_ROOT_PATH .'/data/user/ke-tai_list.csv';
                    if (file_exists($specFile) && is_readable($specFile) &&
                            extension_loaded('mbstring')) {
                        $handle = fopen($specFile, "r");
                        while (($data = fgetcsv($handle, 1024, ",")) !== false) {
                            mb_convert_variables(mb_internal_encoding(), 'sjis-win', $data);
                            if (isset($data[$this->iModelIdx]) &&
                                    $data[$this->iModelIdx] == $user->sModel) {
                                $specList = $data;
                                break;
                            }
                        }
                        fclose($handle);
                    }
                }
            }
            return $specList;
        }
    }
}
