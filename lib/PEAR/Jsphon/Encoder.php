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

/**
 * Converts to JSON format.
 * 
 * example:
 * <code>
 * //create a new instance of Jsphon_Encoder
 * $json =& new Jsphon_Encoder();
 * 
 * //convert a complex value to JSON notation, and send it to the browser
 * $value = array('foo', 'bar', array('hoge' => array(1,2)));
 * $output = $json->encode($value);
 * 
 * print_r($output);
 * //prints: ["foo","bar",{"hoge":[1,2]}]
 * </code>
 * 
 * @author Hawk
 */
class Jsphon_Encoder
{
    var $_mbstring;
    
    var $_escapeNonASCII;
    
    var $_escapeOverUCS2;
    
    var $_transTable;
    
    var $_utf8UCS2reg;
    
    var $_utf8overUCS2reg;
    
    /**
     * constructs a new Jsphon_Encoder instance.
     * 
     * @param bool $escapeNonASCII  If true, encode() converts non-ASCII characters 
     *                              to Unicode escape sequence (\uXXXX).
     * @param bool $escapeOverUCS2  If true, encode() converts all the non-ASCII characters 
     *                              that is encodable in UTF-16 to Unicode escape sequence.
     *                              This parameter affects the encoder's behavior 
     *                              only if $escapeNonASCII is set to true and 
     *                              the multibyte string extension is available.
     * 
     */
    function Jsphon_Encoder($escapeNonASCII=true, $escapeOverUCS2=false)
    {
        $this->_escapeNonASCII = $escapeNonASCII;
        $this->_escapeOverUCS2 = $escapeOverUCS2;
        $this->_mbstring = extension_loaded('mbstring');

        $this->_transTable = array(
            "\x08" => '\b',
            "\x09" => '\t',
            "\x0A" => '\n',
            "\x0C" => '\f',
            "\x0D" => '\r',
            "\x22" => '\"',
            "\x2F" => '\/',
            "\x5C" => '\\\\'
        );
        
        $utf8ucs2 = '[\xC0-\xDF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF]{2}';
        $utf16sur = '[\xF0-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2}';
        $this->_utf8UCS2reg = "/{$utf8ucs2}/";
        $this->_utf8overUCS2reg = "/{$utf8ucs2}|{$utf16sur}/";
    }
    
    /**
     * encodes an arbitrary variables into JSON format.
     * 
     * If $_escapeNonASCII is set to true, 
     * encode() removes all non-ASCII characters to make sure that 
     * returning value dosen't contain any non-ASCII characters.
     * 
     * @param mixed $value
     * @return string
     */
    function encode($value)
    {
        $json = $this->_encode($value);
        if($this->_escapeNonASCII) {
            $json = preg_replace('/[\x80-\xFF]/', '', $json);
        }
        return $json;
    }
    
    function _encode($value)
    {
        if($value === null) {
            return 'null';
            
        } elseif(is_bool($value)) {
            return $value ? 'true' : 'false';
            
        } elseif(is_int($value)) {
            return (int)$value;
            
        } elseif(is_float($value)) {
            return (float)$value;
            
        } elseif(is_string($value)) {
            return '"'. $this->_encodeString($value) .'"';

        } elseif(is_array($value)) {
            if(($len = count($value)) > 0 &&
               array_keys($value) !== range(0, $len - 1)) {
                return $this->_encodeObject($value);
            } else {
                return '['. join(',', array_map(array(&$this, '_encode'), $value)) .']';
            }

        } elseif(is_object($value)) {
            return $this->_encodeObject($value);
            
        }
        
        return 'null';
    }
    
    function _encodeObject($arr)
    {
        $result = array();
        foreach($arr as $name => $value) {
            $result[] = $this->_encode((string)$name) .':'. $this->_encode($value);
        }
        return '{'. join(',', $result) ."}";
    }
    
    function _encodeString($str)
    {
        $str = strtr($str, $this->_transTable);
        
        if(!$this->_escapeNonASCII) {
            return $str;
        }
        
        if(!$this->_mbstring) {
            return $this->_escapeNonASCIIWithoutMbstring($str);
        }
        
        if($this->_escapeOverUCS2) {
            return $this->_escapeNonASCIIOverUCS2($str);
        } else {
            return $this->_escapeNonASCII($str);
        }
    }
    
    function _escapeNonASCII($str) 
    {
        $transTable = array();

        if(!preg_match_all($this->_utf8UCS2reg, $str, $matches)) {
            return $str;
        }
        
        foreach($matches[0] as $utf8char) {
            if(isset($transTable[$utf8char])) {
                continue;
            }
            $transTable[$utf8char] = $this->_formatSeq(
                mb_convert_encoding($utf8char, 'UTF-16', 'UTF-8'));
        }
        return strtr($str, $transTable);
    }

    function _escapeNonASCIIOverUCS2($str) 
    {
        $transTable = array();

        if(!preg_match_all($this->_utf8overUCS2reg, $str, $matches)) {
            return $str;
        }
        
        foreach($matches[0] as $utf8char) {
            if(isset($transTable[$utf8char])) {
                continue;
            }
            $utf16char = mb_convert_encoding($utf8char, 'UTF-16', 'UTF-8');
            
            if(($l = strlen($utf16char)) == 2) {
                $transTable[$utf8char] = $this->_formatSeq($utf16char);
            } elseif($l == 4) {
                $transTable[$utf8char] = 
                    $this->_formatSeq(substr($utf16char, 0, 2)) . $this->_formatSeq(substr($utf16char, 2));
            }
            
        }
        return strtr($str, $transTable);
    }

    function _escapeNonASCIIWithoutMbstring($str) 
    {
        $transTable = array();

        if(!preg_match_all($this->_utf8UCS2reg, $str, $matches)) {
            return $str;
        }
        
        foreach($matches[0] as $utf8char) {
            if(isset($transTable[$utf8char])) {
                continue;
            }
            
            switch(strlen($utf8char)) {
            case 2:
                $code = (
                 ((ord($utf8char{0}) & 0x1F) << 6) |
                  (ord($utf8char{1}) & 0x3F)
                );
                break;

            case 3:
                $code = (
                 ((ord($utf8char{0}) & 0x0F) << 12) |
                 ((ord($utf8char{1}) & 0x3F) << 6 ) |
                  (ord($utf8char{2}) & 0x3F)
                );
                break;
            default:
            }
            $transTable[$utf8char] = '\u'. substr('0000'. dechex($code), -4);
        }
        return strtr($str, $transTable);
    }

    function _formatSeq($u16)
    {
        return '\u'. substr('0000'. bin2hex($u16), -4);
    }
}

?>
