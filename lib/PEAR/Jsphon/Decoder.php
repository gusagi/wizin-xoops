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

require_once(dirname(__FILE__) .'/Decoder/Tokenizer.php');

/**
 * Converts JSON-formatted string to appropriate PHP variable
 * 
 * example:
 * <code>
 * //create a new instance of Jsphon_Decoder
 * $json =& new Jsphon_Decoder();
 * 
 * //convert JSON-formatted string to PHP variable
 * $value = '["foo","bar",{"hoge":[1,2]}]';
 * $var = $json->decode($value);
 * 
 * print_r($var);
 * //array('foo', 'bar', array('hoge' => array(1,2)))
 * </code>
 * 
 * @author Hawk
 */
class Jsphon_Decoder
{
    var $_mbstring;

    var $_decodeOverUCS2;

    var $_internalError;

    var $_transTable = array(
        '\b' => "\x08",
        '\t' => "\x09",
        '\n' => "\x0A",
        '\f' => "\x0C",
        '\r' => "\x0D",
        '\"' => "\x22",
        '\/' => "\x2F",
         '\\\\' => "\x5C"
        );

    var $_allUESreg = '/\\\u([a-fA-F0-9]{4})/';

    var $_utf16surUESreg = '/\\\u(D[89AB][A-F0-9]{2})\\\u(D[C-F][A-F0-9]{2})/i';

    /**
     * construct a new Jsphon_Decoder instance.
     * 
     * @param  bool   $decodeOverUCS2   If true, decodeString() converts the whole
     *                                  Unicode escape sequences (\uXXXX) including surrogate pairs
     *                                  to corresponding characters in UTF-8.
     */
    function Jsphon_Decoder($decodeOverUCS2=false)
    {
        $this->_decodeOverUCS2 = $decodeOverUCS2;
        $this->_mbstring = extension_loaded('mbstring');
    }

    /**
     * decodes a JSON string into appropriate variable
     * 
     * @param  String    $json
     * @return mixed
     */
    function decode($json)
    {
        $tknz = new Jsphon_Decoder_Tokenizer($json,
                                             array(&$this, 'handleInternalError'));
        $this->_internalError = false;

        $result = null;
        if($tknz->nextToken()) {
            $result = $this->_decodeJSValue($tknz);
        }
        
        $this->_internalError = false;
        return $result;
    }

    /**
     * 
     * @param  Object $tknz
     * @return mixed
     */
    function _decodeJSValue(&$tknz)
    {
        switch($tknz->getToken()) {
          case JSPHON_TOKEN_DATUM:
            if(is_string($r = $tknz->getTokenValue())) {
                $r = $this->decodeString($r);
            }
            return $r;
            
          case JSPHON_TOKEN_LBRACKET:
            return $this->_decodeArray($tknz);

          case JSPHON_TOKEN_LBRACE:
            return $this->_decodeObject($tknz);

          default:
            $this->_error("syntax error: Expecting '{', '[' or DAUM.");
            return null;
        }
    }

    /**
     * Decodes a JSON array format:
     *	[element, element2,...,elementN]
     * 
     * @param  Object $tknz
     * @return array
     */
    function _decodeArray(&$tknz)
    {
        $ret = array();
        if(!($token = $tknz->nextToken())) {
            return null;
            
        } elseif($token == JSPHON_TOKEN_RBRACKET) {
            return $ret;
        }

        //if false, break
        while(  ($value = $this->_decodeJSValue($tknz) or true)
            and !$this->_internalError
            and ($ret[] = $value or true)
            and $token = $tknz->nextToken()
            and $token == JSPHON_TOKEN_COMMA
            and $token = $tknz->nextToken()
            );

        if($this->_internalError) {
            return null;
            
        } elseif($token == JSPHON_TOKEN_RBRACKET) {
            return $ret;
        } else {
            $this->_error("Missing ',' or ']' in array encoding.");
            return null;
        }
    }

    /**
     * Decodes an object of the form:
     *  { "attribute: value, "attribute2" : value,...}
     * 
     * _decodeObject() always converts a JSON-object to an associative array,
     * because you can't access empty property in PHP5.
     * <code>
     * // { "":"foo" }
     * $obj->{""} = "foo"; //Fatal error: Cannot access empty property
     * </code>
     * 
     * @param  Object $tknz
     * @return array
     */
    function _decodeObject(&$tknz)
    {
        $ret = array();
        if(!($token = $tknz->nextToken())) {
            return null;
            
        } elseif($token == JSPHON_TOKEN_RBRACE) {
            return $ret;
        }

        //if false, break
        while(  ($key = $this->_decodeJSValue($tknz) or true)
            and !$this->_internalError
            and $this->_checkObjectKey($key)
            and $token = $tknz->nextToken()
            and $this->_checkKeyValueSep($token)
            and $token = $tknz->nextToken()
            and ($value = $this->_decodeJSValue($tknz) or true)
            and !$this->_internalError
            and ($ret[$key] = $value or true)
            and $token = $tknz->nextToken()
            and $token == JSPHON_TOKEN_COMMA
            and $token = $tknz->nextToken()
            );

        if($this->_internalError) {
            return null;
            
        } elseif($token == JSPHON_TOKEN_RBRACE) {
            return $ret;
        } else {
            $this->_error("Missing ',' or '}' in object encoding.");
            return null;
        }
    }

    function _checkObjectKey($key)
    {
        if(!is_string($key)) {
            $this->_error("Object's key must be a string, but is ". gettype($key));
            return false;
        }
        return true;
    }

    function _checkKeyValueSep($token)
    {
        if($token != JSPHON_TOKEN_COLON) {
            $this->_error("Missing ':' in object encoding.");
            return false;
        }
        return true;
    }
    

    /**
     * 
     * 
     * @param  String    $encoded
     * @return String
     */
    function decodeString($encoded)
    {
        $ret = strtr($encoded, $this->_transTable);
        
        if($this->_decodeOverUCS2) {
            return $this->_decodeUESOverUCS2($ret);
        } else {
            //_decodeUESWithoutMbstring() fastar than _decodeUES()...
            return $this->_decodeUESWithoutMbstring($ret);
        }
        
        /*
        if(!$this->_mbstring) {
            return $this->_decodeUESWithoutMbstring($ret);
        } elseif($this->_decodeOverUCS2) {
            return $this->_decodeUESOverUCS2($ret);
        } else {
            return $this->_decodeUES($ret);
        }
        */
    }

    function _decodeUES($str)
    {
        $transTable = array();

        if(!preg_match_all($this->_allUESreg, $str, $matches)) {
            return $str;
        }

        $codepoints = $matches[1];
        foreach($matches[0] as $i => $escSeq) {
            if(isset($transTable[$escSeq])) {
                continue;
            }
            $codepoint = hexdec($codepoints[$i]);
            $transTable[$escSeq] = (0xD800 <= $codepoint && $codepoint <= 0xDFFF) ? ""
            : mb_convert_encoding(pack('n', $codepoint), 'UTF-8', 'UCS-2');
        }
        return strtr($str, $transTable);
    }
    
    function _decodeUESOverUCS2($str)
    {
        $transTable = array();

        if(!preg_match_all($this->_allUESreg, $str, $u16)) {
            return $str;
        }
        
        if(preg_match_all($this->_utf16surUESreg, $str, $u16sur)) {
            $sur1st = $u16sur[1];
            $sur2nd = $u16sur[2];
            
            foreach($u16sur[0] as $i => $escSeq) {
                if(isset($transTable[$escSeq])) {
                    continue;
                }
                $transTable[$escSeq] = mb_convert_encoding(
                    pack('n2', hexdec($sur1st[$i]), hexdec($sur2nd[$i])), 'UTF-8', 'UTF-16BE');
            }
        }

        $codepoints = $u16[1];
        foreach($u16[0] as $i => $escSeq) {
            if(isset($transTable[$escSeq])) {
                continue;
            }
            $codepoint = hexdec($codepoints[$i]);
            $transTable[$escSeq] = (0xD800 <= $codepoint && $codepoint <= 0xDFFF) ? ""
            : mb_convert_encoding(pack('n', $codepoint), 'UTF-8', 'UCS-2');
        }
        return strtr($str, $transTable);
    }

    function _decodeUESWithoutMbstring($str)
    {
        $transTable = array();

        if(!preg_match_all($this->_allUESreg, $str, $matches)) {
            return $str;
        }

        $codepoints = $matches[1];
        foreach($matches[0] as $i => $escSeq) {
            if(isset($transTable[$escSeq])) {
                continue;
            }
            
            $utf8char = "";

            $cp = hexdec($codepoints[$i]);
            switch(true) {
              case ($cp < 0x80):
                $utf8char = chr($cp);
                break;
                
              case (0xD800 <= $cp && $cp <= 0xDFFF):
                break;

              case ($cp < 0x800):
                $utf8char = chr($cp >> 6 & 0x1F | 0xC0) . chr($cp & 0x3F | 0x80);
                break;
                
              case ($cp < 0x10000):
                $utf8char = chr($cp >> 12 & 0xF | 0xE0) .
                chr($cp >> 6 & 0x3F | 0x80) . chr($cp & 0x3F | 0x80);
                break;

              default:
            }
            $transTable[$escSeq] = $utf8char;
        }
        return strtr($str, $transTable);
    }
    

    /**
     * a simple wrapper for PEAR_ErrorStack::push.
     * 
     * @param  String    $message
     * @param  array     $param
     * @param  string    $level
     * @param  int       $code
     * @return String
     */
    function _error($message,
                    $param= array(),
                    $level='error',
                    $code=JSPHON_ERROR_DECODE_SYNTAX)
    {
        $e = Jsphon_Error::push(
            $code, $level, $param, $message, false, debug_backtrace());
        $this->_internalError = true;
        return $e;
    }
    
    function handleInternalError($err)
    {
        $this->_internalError = true;
    }
}

?>
