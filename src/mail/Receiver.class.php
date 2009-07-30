<?php
/**
 * Wizin framework mail receiver class
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Mail_Receiver')) {
    require dirname(dirname(__FILE__)) . '/Wizin.class.php';
    /**
     * Wizin framework mail receiver class
     *
     */
    class Wizin_Mail_Receiver extends Wizin_StdClass
    {
        /**
         * Constructor
         */
        public function __construct($mailText = '')
        {
            if (is_null($mailText) || $mailText === '') {
                $mailText = file_get_contents("php://stdin");
            }
            $this->_mailText = $mailText;
            $this->_init();
            $this->_decode();
        }

        /**
         * Return Wizin_Mail_Receiver singleton instance
         */
        public function &getSingleton($mailText = '')
        {
            static $instance;
            if (! isset($instance)) {
                $instance = new Wizin_Mail_Receiver($mailText);
            }
            return $instance;
        }

        /**
         * Init process.
         *
         */
        protected function _init()
        {
            // include PEAR::Mail_mimeDecode
            $includePath = get_include_path();
            set_include_path($includePath . PATH_SEPARATOR . WIZIN_PEAR_DIR);
            if (! class_exists('Mail_mimeDecode')) {
                require 'Mail/mimeDecode.php';
            }
            // get mail text
            if (extension_loaded('mbstring')) {
                $this->_encode = mb_detect_encoding($this->_mailText,
                    'JIS,UTF-8,SJIS,EUC-JP,ASCII', true);
                switch (strtolower($this->_encode)) {
                    case 'sjis':
                    case 'shift_jis':
                        $this->_encode = 'sjis-win';
                        break;
                    case 'euc-jp':
                        $this->_encode = 'eucjp-win';
                        break;
                }
            } else {
                $this->_encode = 'ascii';
            }
        }

        /**
         * Decode mail text.
         *
         */
        protected function _decode()
        {
            // set decode params
            $params['include_bodies'] = true;
            $params['decode_bodies'] = true;
            $params['decode_headers'] = true;
            // decode
            $decoder = new Mail_mimeDecode($this->_mailText);
            $this->_structure = $decoder->decode($params);
            $this->_mailType = strtolower($this->_structure->ctype_primary);
        }

        /**
         * Return mail headers
         */
        public function getMailHeaders()
        {
            $headers = $this->_structure->headers;
            if (extension_loaded('mbstring')) {
                mb_convert_variables(mb_internal_encoding(), $this->_encode, $headers);
            }
            return $headers;
        }

        /**
         * Return 'from' header
         */
        public function getMailFrom()
        {
            $headers = $this->getMailHeaders();
            $from = $headers['from'];
            if (preg_match('/<(.*?)>$/', $from, $match)) {
                $from = $match[1];
            }
            return $from;
        }

        /**
         * Return mail headers
         */
        public function getMailBody()
        {
            if ($this->_mailType === 'text') {
                $body = $this->_structure->body;
            } else {
                $body = $this->_structure->parts[0]->body;
            }
            if (extension_loaded('mbstring')) {
                mb_convert_variables(mb_internal_encoding(), $this->_encode, $body);
            }
            return $body;
        }

        /**
         * Return mail headers
         */
        public function getMailAttachments()
        {
            $attachments = array();
            if ($this->_mailType !== 'multipart') {
                return $attachments;
            }
            for ($index = 1; $index < count($this->_structure->parts); $index++) {
                $attach = $this->_structure->parts[$index];
                $ctypePrimary = strtolower($attach->ctype_primary);
                $ctypeSecondary = strtolower($attach->ctype_secondary);
                switch ($ctypePrimary) {
                    case 'text':
                        switch ($ctypeSecondary) {
                            case 'html':
                            case 'htm':
                                $ext = 'html';
                                break;
                            default:
                                $ext = 'txt';
                        }
                        break;
                    case 'image':
                        $ext = $ctypeSecondary;
                        break;
                }
                $attachment = array(
                   'attach' => $attach,
                   'type' => $ctypePrimary,
                   'ext' => $ext
               );
                $attachments[] = $attachment;
            }
            return $attachments;
        }
    }
}
