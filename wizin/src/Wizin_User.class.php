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
    require dirname( __FILE__ ) . '/Wizin.class.php';
            if ( ! class_exists('Wizin_Parser_Yaml') ) {
                require WIZIN_ROOT_PATH . '/src/parser/Yaml.class.php';
            }

    /**
     * Wizin framework user class
     *
     * @access public
     *
     */
    class Wizin_User extends Wizin_StdClass
    {
        /**
         *
         * @return object $instance
         */
        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_User();
            }
            return $instance;
        }

        /**
         * check access client
         *
         * @param boolean $lookup
         */
        function checkClient( $lookup = false )
        {
            $ip = getenv( 'REMOTE_ADDR' );
            $agent = getenv( 'HTTP_USER_AGENT' );
            $parser =& Wizin_Parser_Yaml::getSingleton();
            $yaml = WIZIN_ROOT_PATH . '/data/user/client.yml';
            $mobileData = $parser->parse( $yaml );
            if ( $lookup ) {
                $data = $this->_advancedCheck( $mobileData );
            } else {
                $data = $this->_basicCheck( $mobileData );
            }
            if ( ! empty($data) ) {
                $this->iCarrierId = intval( $data['carrierid'] );
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
                    $this->sEncoding = 'utf-8';
                }
                $charset = $data['charset'];
                if ( ! empty($charset) ) {
                    $this->sCharset = $charset;
                } else {
                    $this->sCharset = 'utf-8';
                }
                $doctype = $data['doctype'];
                if ( ! empty($doctype) ) {
                    $this->sDoctype = $doctype;
                } else {
                    $this->sDoctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
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
                $inputMode = $data['inputmode'];
                if ( ! empty($inputMode) ) {
                    $this->aInputMode = $inputMode;
                } else {
                    $this->aInputMode = array();
                }
            } else {
                $this->iCarrierId = 99;
                $this->bIsMobile = false;
                $this->bIsBot = false;
                $this->bCookie = true;
                $this->sCarrier = 'unknown';
                $this->sUniqId = '';
                $this->sEncoding = 'utf-8';
                $this->sCharset = 'utf-8';
                $this->sDoctype = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
                $this->aInputMode = array();
            }
        }

        /**
         * basic check method
         *
         * @param array $mobileData
         * @return unknown
         */
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

        /**
         * advanced check method
         *
         * @param array $mobileData
         * @return unknown
         */
        function _advancedCheck( $mobileData )
        {
            $ip = getenv( 'REMOTE_ADDR' );
            $host = @ gethostbyaddr( $ip );
            if ( $host !== $ip ) {
                $ipList = gethostbynamel( $host );
                if ( $ipList !== false && in_array($ip, $ipList) ) {
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
                }
            }
            return null;
        }

    }
}
