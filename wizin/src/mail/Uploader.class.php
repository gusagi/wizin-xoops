<?php
/**
 * Wizin framework mail uploader class
 *
 * PHP Version 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Mail_Uploader') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    if ( ! class_exists('Wizin_Mail_Receiver') ) {
        require dirname( __FILE__ ) . '/Receiver.class.php';
    }
    /**
     * Wizin framework mail receiver class
     *
     */
    class Wizin_Mail_Uploader extends Wizin_StdClass
    {
        /**
         * Constructor
         */
        public function __construct()
        {
            $this->_save();
        }

        /**
         * Return Wizin_Mail_Uploader singleton instance
         */
        public function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Mail_Uploader();
            }
            return $instance;
        }

        /**
         * Save attachment files into work directory
         */
        protected function _save()
        {
            $this->_files = array();
            $mailReceiver =& Wizin_Mail_Receiver::getSingleton();
            $headers = $mailReceiver->getMailHeaders();
            $timestamp = date( 'Ymd_His' );
            $uniqueKey = Wizin_Util::cipher( $headers['message-id'] );
            $attachments = $mailReceiver->getMailAttachments();
            foreach ( $attachments as $key => $attachment ) {
                $attach = $attachment['attach'];
                $file = $attach->body;
                $filePath = WIZIN_UPLOAD_DIR . '/' . $uniqueKey . '_' . $timestamp . '_' .
                    sprintf('%03d', $key) . '.' . $attachment['ext'];
                $fp = fopen( $filePath, 'w' );
                fputs( $fp, $file );
                fclose( $fp );
                chmod( $filePath, 0666 );
                // if upload file was danger, remove this file.
                if ( $this->_checkFile($filePath, $attachment['type']) === false ) {
                    unlink( $filePath );
                    continue;
                }
                $this->_files[] = $filePath;
            }
        }


        /**
         * Check uploaded file.
         */
        protected function _checkFile( $filePath, $type )
        {
            // if file will be danger, return false.
            clearstatcache();
            $allowImageFormat = array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG );
            switch ( $type ) {
                case 'image':
                    $imageSizeInfo = getimagesize( $filePath );
                    $width = $imageSizeInfo[0];
                    $height = $imageSizeInfo[1];
                    $format = $imageSizeInfo[2];
                    // not image file
                    if ( $width == 0 || $height == 0 || ! in_array($format, $allowImageFormat) ) {
                        return false;
                    }
                    break;
                default:
                    break;
            }
            return true;
        }

        /**
         * Return upload files path
         *
         * @return unknown
         */
        public function getUploadFiles()
        {
            return $this->_files;
        }
    }
}
