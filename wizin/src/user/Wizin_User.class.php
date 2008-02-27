<?php
/**
 * Wizin framework user class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi <gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

if ( ! class_exists('Wizin_User') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    require 'src/parser/Wizin_Parser_Yaml.class.php';

    class Wizin_User extends Wizin_StdClass
    {
        function __construct()
        {
            $this->_bLookup = false;
            $this->bIsMobile = false;
            $this->sCarrier = 'unknown';
            $this->sUniqId = '';
            $this->sEncoding = '';
            $this->sCharset = '';
        }

        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_User();
            }
            return $instance;
        }

        function checkClient( $lookup = false )
        {
            $this->_bLookup = $lookup;
            $ip = getenv( 'REMOTE_ADDR' );
            $agent = getenv( 'HTTP_USER_AGENT' );
            $parser =& Wizin_Parser_Yaml::getSingleton();
            $yaml = dirname( dirname(dirname(__FILE__)) ) . '/data/user/client.yml';
            $mobileData = $parser->parse( $yaml );
            if ( $lookup ) {
                $data = $this->_advancedCheck( $mobileData );
            } else {
                $data = $this->_basicCheck( $mobileData );
            }
            if ( ! empty($data) ) {
                $this->bIsMobile = $data['mobile'];
                $this->sCarrier = $data['carrier'];
                $uniqid = getenv( $data['uniqid'] );
                if ( ! empty($uniqid) ) {
                    $this->sUniqId = $uniqid;
                } else {
                    $this->sUniqId = '';
                }
                $encoding = $data['encoding'];
                if ( ! empty($encoding) ) {
                    $this->sEncoding = $encoding;
                } else {
                    $this->sEncoding = '';
                }
                $charset = $data['charset'];
                if ( ! empty($charset) ) {
                    $this->sCharset = $charset;
                } else {
                    $this->sCharset = '';
                }
                $function = $data['function'];
                if ( ! empty($function) && method_exists($this, $function) ) {
                    // TODO : function not in this file, and dynamic include enable
                    $this->$function();
                }
            }
        }

        function _basicCheck( $mobileData )
        {
            $agent = getenv( 'HTTP_USER_AGENT' );
            foreach ( $mobileData as $carrier => $data ) {
                foreach ( $data['agent'] as $pattern ) {
                    $pattern = '/' . $pattern . '/i';
                    preg_match( $pattern, $agent, $matches );
                    if ( ! empty($matches) ) {
                        $data['carrier'] = $carrier;
                        return $data;
                    }
                }
            }
            return null;
        }

        function _advancedCheck( $mobileData )
        {
            $ip = getenv( 'REMOTE_ADDR' );
            $host = @ gethostbyaddr( $ip );
            foreach ( $mobileData as $carrier => $data ) {
                if ( ! empty($data['host']) ) {
                    $pattern = '/' . $data['host'] . '/i';
                    preg_match( $pattern, $host, $matches );
                    if ( ! empty($matches) ) {
                        $data['carrier'] = $carrier;
                        return $data;
                    }
                }
            }
            return null;
        }

        // TODO : function not in this file, and dynamic include enable
        function checkDocomo()
        {
            $agent = getenv( 'HTTP_USER_AGENT' );
            preg_match( "/ser([a-zA-Z0-9]+)/", $agent, $matches );
            // mova
            if ( ! empty($matches[1]) && strlen($matches[1]) === 11 ) {
                $this->sUniqId = $matches[1];
            } elseif ( ! empty($matches[1]) && strlen($matches[1]) === 15 ) {
                preg_match( "/icc([a-zA-Z0-9]+)/", $agent, $matches2 );
                if ( strlen($matches2[1]) === 20 ) {
                    // foma card id
                    $this->sUniqId = $matches2[1];
                } else {
                    // foma terminal id
                    $this->sUniqId = $matches[1];
                }
            }
        }

        // TODO : function not in this file, and dynamic include enable
        function checkSoftbank()
        {
            $this->sUniqId = substr( $this->sUniqId, 1 );
        }

        // TODO : function not in this file, and dynamic include enable
        function checkWillcom()
        {
            if ( $this->_bLookup ) {
                $agent = getenv( 'HTTP_USER_AGENT' );
                if ( ! preg_match("/(willcom|ddipocket)/i", $agent) ) {
                    $this->bIsMobile = true;
                    $this->sCarrier = 'othermobile';
                    $this->sUniqId = '';
                    $this->sEncoding = 'sjis-win';
                    $this->sCharset = 'shift_jis';
                }
            }
            return null;
        }
    }
}
