<?php
/**
 * Wizin framework filter class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi <gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

if ( ! class_exists('Wizin_Filter') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';

    class Wizin_Filter extends Wizin_StdClass
    {

        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Filter();
            }
            return $instance;
        }

        function filterInputEncoding( $inputEncoding = '' )
        {
            if ( extension_loaded('mbstring') ) {
                if ( empty($inputEncoding) ) {
                    $inputEncoding = mb_detect_encoding( serialize($_REQUEST) );
                }
                $internalEncoding = mb_internal_encoding();
                mb_convert_variables( $internalEncoding, $inputEncoding, $_GET );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_POST );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_REQUEST );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_COOKIE );
            }
        }

        function filterOutputEncoding( $outputEncoding, $outputCharset )
        {
            if ( extension_loaded('mbstring') ) {
                if ( empty($outputEncoding) || empty($outputEncoding) ) {
                    return ;
                }
                $this->_obFromEncoding = mb_internal_encoding();
                $this->_obToEncoding = $outputEncoding;
                $this->_obCharset = $outputCharset;
                ini_set( 'default_charset', $outputCharset );
            }
            ob_start( array(&$this, '_filterObEncoding') );
        }

        function _filterObEncoding( $buf )
        {
            if ( extension_loaded('mbstring') ) {
                $buf = str_replace( 'charset=' ._CHARSET, 'charset=' . $this->_obCharset, $buf );
                $pattern = '(=)([\"\'])(' . _CHARSET . ')([\"\'])';
                $replacement = '${1}${2}' . $this->_obCharset . '${4}';
                $buf = preg_replace( "/" .$pattern ."/", $replacement, $buf );
                $buf = mb_convert_encoding( $buf, $this->_obToEncoding, $this->_obFromEncoding );
            }
            return $buf;
        }

    }
}
