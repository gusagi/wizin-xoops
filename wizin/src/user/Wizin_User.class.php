<?php
/**
 * Wizin framework user class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
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
            $this->bIsBot = false;
            $this->bCookie = true;
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
                $this->bIsBot = $data['bot'];
                $this->bCookie = $data['cookie'];
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
                $plugin = $data['plugin'];
                if ( ! empty($plugin) ) {
                    if ( ! empty($plugin['path']) && file_exists(WIZIN_ROOT_PATH . '/' . $plugin['path']) ) {
                        include WIZIN_ROOT_PATH . '/' . $plugin['path'];
                    }
                    if ( ! empty($plugin['class']) && class_exists($plugin['class']) ) {
                        $class = $plugin['class'];
                        $instance = new $class;
                    }
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

    }
}
