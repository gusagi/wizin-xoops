<?php
//
// +--------------------------------------------------------------------+
// | PHP version 4 and 5                                                |
// +--------------------------------------------------------------------+
// | Copyright (c) 2006 Hawk                                            |
// +--------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,    |
// | that is available at http://www.php.net/license/3_0.txt.           |
// | If you did not receive a copy of the PHP license and are unable to |
// | obtain it through the world-wide-web, please send a note to        |
// | license@php.net so we can mail you a copy immediately.             |
// +--------------------------------------------------------------------+
// | Authors: Hawk <scholar@hawklab.jp>                                 |
// +--------------------------------------------------------------------+
//
//

define('JSPHON_TOKEN_EOF',      1);
define('JSPHON_TOKEN_DATUM',    2);
define('JSPHON_TOKEN_LBRACE',   3);
define('JSPHON_TOKEN_LBRACKET', 4);
define('JSPHON_TOKEN_RBRACE',   5);
define('JSPHON_TOKEN_RBRACKET', 6);
define('JSPHON_TOKEN_COMMA',    7);
define('JSPHON_TOKEN_COLON',    8);

/**
 * Jsphon_Decoder_Tokenizer
 * 
 * @author Hawk
 */
class Jsphon_Decoder_Tokenizer
{
    var $_source;

    var $_token;

    var $_tokenValue;

    var $_tokenTable = array(
        '{' => JSPHON_TOKEN_LBRACE,
        '}' => JSPHON_TOKEN_RBRACE,
        '[' => JSPHON_TOKEN_LBRACKET,
        ']' => JSPHON_TOKEN_RBRACKET,
        ',' => JSPHON_TOKEN_COMMA,
        ':' => JSPHON_TOKEN_COLON
        );

    var $_tokenValueTable = array(
        'true'  => true,
        'false' => false,
        'null'  => null
        );

    var $_errorCallback;

    /**
     * constructs a new Jsphon_Decoder_Tokenizer instance.
     * 
     * @param  String    $source
     */
    function Jsphon_Decoder_Tokenizer($source, $internalErrorCallback=null)
    {
        $this->_source = $source;
        $this->_errorCallback = $internalErrorCallback;
    }

    /**
     * Retrieves the next token from the source stream.
     * 
     * @return int or false
     */
    function nextToken()
    {
        $src = ltrim($this->_source);

        $this->_tokenValue = null;
        $this->_token = JSPHON_TOKEN_EOF;

        switch(true)
        {
          case $src === '':
            break;
            
          case ($c = $src{0}) !== "" and isset($this->_tokenTable[$c]):
            $this->_token = $this->_tokenTable[$c];
            $src = substr($src, 1);
            break;

          case $c == '"' and preg_match('/^"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/', $src, $m):
            $this->_token = JSPHON_TOKEN_DATUM;
            $this->_tokenValue = $m[1];
            $src = substr($src, strlen($m[0]));
            break;

          case preg_match('/^(true|false|null)\b/', $src, $m):
            $this->_token = JSPHON_TOKEN_DATUM;
            $this->_tokenValue = $this->_tokenValueTable[$m[1]];
            $src = substr($src, ($m[1]{0} == 'f' ? 5 : 4));
            break;

          case preg_match('/^-?(?:[1-9]\d+|\d)(?:\.\d+)?(?:[eE][-+]?\d+)?/', $src, $m):
            $this->_token = JSPHON_TOKEN_DATUM;
            $intV   = (int)$m[0];
            $floatV = (float)$m[0];
            $this->_tokenValue = ($intV == $floatV) ? $intV : $floatV;
            $src = substr($src, strlen($m[0]));
            break;

          default:
            $err = Jsphon_Error::push(
                JSPHON_ERROR_DECODE_SYNTAX,
                'error',
                array(),
                'Illegal Token',
                false,
                debug_backtrace());

            if(is_callable($this->_errorCallback)) {
                call_user_func($this->_errorCallback, $err);
            }
            return false;

        }

        $this->_source = $src;
        return $this->_token;
    }

    function getToken()
    {
        return $this->_token;
    }

    function getTokenValue()
    {
        return $this->_tokenValue;
    }

}

?>
