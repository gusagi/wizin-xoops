<?php
/**
 * Wizin framework core session class
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Core_Session')) {
    require dirname(dirname(__FILE__)) . '/Wizin.class.php';

    /**
     * Wizin framework core session class
     *
     */
    class Wizin_Core_Session extends Wizin_StdClass
    {
        const HASH_BIT_BASE_16NUM = 4;
        const HASH_BIT_BASE_36NUM = 5;
        const HASH_BIT_BASE_64NUM = 6;

        /**
         * session init process
         *
         * @param integer $hashBits
         */
        public static function init($hashBits = '')
        {
            //
            // set default session setting
            //
            ini_set('session.use_trans_sid', "0");

            //
            // set hash bits per character
            //
            $hashBitsArray = array(Wizin_Core_Session::HASH_BIT_BASE_16NUM,
                Wizin_Core_Session::HASH_BIT_BASE_36NUM, Wizin_Core_Session::HASH_BIT_BASE_64NUM);
            if (empty($hashBits) || ! in_array($hashBits, $hashBitsArray)) {
                $hashBits = Wizin_Core_Session::HASH_BIT_BASE_36NUM;
            }
            ini_set('session.hash_bits_per_character', $hashBits);

            //
            // check session_id from request
            //
            $sessionName = ini_get('session.name');
            if (! empty($_REQUEST[$sessionName])) {
                switch ($hashBits) {
                    case Wizin_Core_Session::HASH_BIT_BASE_16NUM:
                        $pattern = '/^[^a-f0-9]+$/';
                        break;
                    case Wizin_Core_Session::HASH_BIT_BASE_64NUM:
                        $pattern = '/^[^a-zA-Z0-9\-,]+$/';
                        break;
                    default:
                        $pattern = '/^[^a-zA-Z0-9]+$/';
                        break;
                }
                // if match pattern, this session_id is wrong
                if (preg_match($pattern, $_REQUEST[$sessionName])) {
                    unset($_GET[$sessionName]);
                    unset($_POST[$sessionName]);
                    unset($_COOKIE[$sessionName]);
                    unset($_REQUEST[$sessionName]);
                }
            }
            // if DatSession class exists, session handler is database.
            if (! class_exists('Propel') || ! class_exists('DatSession')) {
                session_save_path(WIZIN_ROOT_PATH . '/work/session');
                return true;
            }
            // set original session handler
            ini_set('session.save_handler', 'user');
            session_set_save_handler (
                array(__CLASS__, 'open'),
                array(__CLASS__, 'close'),
                array(__CLASS__, 'read'),
                array(__CLASS__, 'write'),
                array(__CLASS__, 'destroy'),
                array(__CLASS__, 'gc')
           );
        }

        /**
         * return DatSession class object
         *
         */
        protected static function _getDatSession($sessionId = '')
        {
            static $datSession;
            if (empty($sessionId)) {
                $sessionId = session_id();
            }
            if (! isset($datSession)) {
                $datSession = DatSessionPeer::retrieveByPK($sessionId);
                if (empty($datSession)) {
                    $datSession = new DatSession();
                    $datSession->setSessionId($sessionId);
                }
            }
            return $datSession;
        }

        /**
         * set or return gc session id
         *
         * @param string $sessionId
         */
        protected static function _gcSessionId($sessionId = '')
        {
            static $gcSessionId;
            if (! isset($gcSessionId)) {
                $gcSessionId = '';
            }
            if (! empty($sessionId)) {
                $gcSessionId = $sessionId;
            }
            return $gcSessionId;
        }

        /**
         * session open process
         *
         */
        public static function open()
        {
            return true;
        }

        /**
         * session close process
         *
         */
        public static function close()
        {
            return true;
        }

        /**
         * session read process
         *
         */
        public static function read($sessionId)
        {
            // get DatSession object
            $datSession = call_user_func(array(__CLASS__, '_getDatSession'), $sessionId);

            // return session data
            return $datSession->getSessionData();
        }

        /**
         * session write process
         *
         */
        public static function write($sessionId, $sessionData)
        {
            // get DatSession object
            $datSession = call_user_func(array(__CLASS__, '_getDatSession'), $sessionId);

            // is this session is gc target?
            $gcSessionId = call_user_func(array(__CLASS__, '_gcSessionId'));
            if ($gcSessionId === $sessionId) {
                session_regenerate_id(true);
                $sessionData = '';
                $datSession = new DatSession();
                $datSession->setSessionId(session_id());
            }

            // save session data
            $now = date('Y-m-d H:i:s');
            $datSession->setSessionData($sessionData);
            $datSession->setUpdatedAt($now);      // TODO : must be auto update
            $datSession->save();
            return true;
        }

        /**
         * session destroy process
         *
         */
        public static function destroy($sessionId)
        {
            // get DatSession object
            $datSession = call_user_func(array(__CLASS__, '_getDatSession'), $sessionId);

            // delete session data
            $datSession->delete();
            return true;
        }

        /**
         * session gavage collection process
         *
         */
        public static function gc($maxLifetime)
        {
            // set variables
            $sessionId = session_id();
            $lifetimeLimit = date('Y-m-d H:i:s', time() - $maxLifetime);

            // is this session gc target?
            $criteria = new Criteria();
            $criteria->add(DatSessionPeer::SESSION_ID, $sessionId);
            $criteria->addAnd(DatSessionPeer::UPDATED_AT, $lifetimeLimit, Criteria::LESS_THAN);
            $datSession = DatSessionPeer::doSelectOne($criteria);
            if (! empty($datSession)) {
                call_user_func(array(__CLASS__, '_gcSessionId'), $sessionId);
            }

            // delete old sessions
            $criteria = new Criteria();
            $criteria->add(DatSessionPeer::UPDATED_AT, $lifetimeLimit, Criteria::LESS_THAN);
            $rowCount = DatSessionPeer::doDelete($criteria);
            return true;
        }

    }
}
