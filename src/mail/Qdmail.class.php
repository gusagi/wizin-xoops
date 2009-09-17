<?php
/**
 * Wizin framework qdmail wrapper class
 *
 * PHP Version 4.4.3/5.1.3 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Mail_Qdmail')) {
    require dirname(dirname(__FILE__)) .'/Wizin.class.php';
    require WIZIN_ROOT_PATH .'/lib/Qdmail/qdmail.php';

    /**
     * Dummy function for mb_check_encoding
     *
     * As for this function it is correct to add?
     */
    if (! function_exists('mb_check_encoding')) {
        function mb_check_encoding($var = '', $encoding = '') {
            return true;
        }
    }

    /**
     * Wizin framework qdmail wrapper class
     *
     */
    class Wizin_Mail_Qdmail extends Qdmail
    {
        var $name ='Wizin_Mail_Qdmail';

        function Wizin_Mail_Qdmail($param = null) {
            if( !is_null($param)){
                $param = func_get_args();
            }
            $this->is_qmail = false;
            $ret = ini_get('sendmail_path');
            if (false !== strpos($ret,'qmail')) {
                $this->is_qmail = true;
            }
            parent::__construct($param);
        }
    }
}