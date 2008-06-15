<?php
/**
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Plugin_User_Docomo') ) {
    class Wizin_Plugin_User_Docomo extends Wizin_StdClass
    {
        function __construct()
        {
            $this->_require();
            $this->_setup();
        }

        function _require()
        {
            require_once 'src/Wizin_Filter.php';
        }

        function _setup()
        {
            static $calledFlag;
            if ( ! isset($calledFlag) ) {
                $calledFlag = true;
                $filter =& Wizin_Filter::getSingleton();
                $params = array();
                $filter->addOutputFilter( array( $this, 'filterDocomo' ), $params );
                //$this->_checkGuid();
            }
        }

        function _checkGuid()
        {
            $checkString = 'guid=on';
            $requestUri = getenv( 'REQUEST_URI' );
            $method = getenv( 'REQUEST_METHOD' );
            if ( strtolower($method) === 'get' ) {
                if ( ! preg_match('/' . $checkString . '/i', $requestUri) ) {
                    $https = getenv( 'HTTPS' );
                    if ( ! empty($https) && strtolower($https) === 'on' ) {
                        $scheme = 'https';
                    } else {
                        $scheme = 'http';
                    }
                    $serverName = getenv( 'SERVER_NAME' );
                    $currentUrl = $scheme . '://' . $serverName;
                    $port = getenv( 'SERVER_PORT' );
                    if ( ! empty($port) && $port !== '80' && $port !== '443' ) {
                        $currentUrl .= ':' . $port;
                    }
                    $currentUrl .= $requestUri;
                    $queryString = getenv( 'QUERY_STRING' );
                    if ( isset($queryString) && $queryString !== '' ) {
                        $currentUrl .= '&' . $checkString;
                    } else {
                        $currentUrl .= '?' . $checkString;
                    }
                    header( 'Location: ' . $currentUrl );
                }
            }
        }

        function filterDocomo( & $contents )
        {
            $insertString = 'guid=on';
            // get method
            $pattern = '(<a)([^>]*)(href=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                foreach ( $matches as $key => $match) {
                    $href = '';
                    $hrefArray = array();
                    $url = $match[5];
                    if ( preg_match('/' . $insertString . '/i', $url) ) {
                        continue;
                    } else if ( substr($url, 0, 4) !== 'http' && strpos($url, ':') !== false ) {
                        continue;
                    } else if ( substr($url, 0, 1) === '#' ) {
                        $url = basename( getenv('SCRIPT_NAME') ) . $url;
                    }
                    if ( ! strstr($url, '?') ) {
                        $connector = '?';
                    } else {
                        $connector = '&';
                    }
                    if ( strstr($url, '#') ) {
                        $hrefArray = explode( '#', $url );
                        $href .= $hrefArray[0] . $connector . $insertString;
                        if ( ! empty($hrefArray[1]) ) {
                            $href .= '#' . $hrefArray[1];
                        }
                    } else {
                        $href = $url . $connector . $insertString;
                    }
                    $contents = str_replace( $match[3] . $match[4] .$match[5] . $match[6],
                        $match[3] . $match[4] .$href . $match[6], $contents );
                }
            }
            //
            // form
            //
            // pattern 1 ( "method=, action=" pattern )
            $pattern = '(<form)([^>]*)(method=)([\"\'])(post|get)([\"\'])([^>]*)(action=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                foreach ( $matches as $key => $match) {
                    if ( ! empty($match[10]) ) {
                        if ( substr($match[10], 0, 4) !== 'http' && strpos($match[10], ':') !== false ) {
                            continue;
                        } else if ( substr($match[10], 0, 1) === '#' ) {
                            $action = basename( getenv('SCRIPT_NAME') ) . $match[10];
                            $form = str_replace( $match[10], $action, $match[0] );
                        } else {
                            $action = $match[10];
                            $form = $match[0];
                        }
                    } else {
                        $url = basename( getenv('SCRIPT_NAME') );
                        $queryString = getenv( 'QUERY_STRING' );
                        if ( isset($queryString) && $queryString !== '' ) {
                            $queryString = str_replace( '&' . SID, '', $queryString );
                            $queryString = str_replace( SID, '', $queryString );
                            if ( $queryString !== '' ) {
                                $url .= '?' . $queryString;
                            }
                        }
                        $form = str_replace( $match[8] . $match[9] . $match[10] . $match[11],
                            $match[8] . $match[9] . $url . $match[11], $match[0] );
                        $action = $url;
                    }
                    if ( preg_match('/' . $insertString . '/i', $action) ) {
                        continue;
                    }
                    $method = strtolower( $match[5] );
                    if ( $method === 'post' ) {
                        $baseAction = $action;
                        if ( ! strstr($action, '?') ) {
                            $connector = '?';
                        } else {
                            $connector = '&';
                        }
                        if ( strstr($action, '#') ) {
                            $actionArray = explode( '#', $action );
                            $action = $actionArray[0] . $connector . $insertString;
                            if ( ! empty($actionArray[1]) ) {
                                $action .= '#' . $actionArray[1];
                            }
                        } else {
                            $action = $action . $connector . $insertString;
                        }
                        $form = str_replace( $baseAction, $action, $form );
                        $contents = str_replace( $match[0], $form, $contents );
                    } else {
                        $tag = '<input type="hidden" name="guid" value="on" />';
                        $contents = str_replace( $match[0], $form . $tag, $contents );
                    }
                    $action = '';
                }
            }
            // pattern 2 ( "action=, method=" pattern )
            $pattern = '(<form)([^>]*)(action=)([\"\'])(\S*)([\"\'])([^>]*)(method=)([\"\'])(post|get)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                foreach ( $matches as $key => $match) {
                    if ( ! empty($match[5]) ) {
                        if ( substr($match[5], 0, 4) !== 'http' && strpos($match[5], ':') !== false ) {
                            continue;
                        } else if ( substr($match[5], 0, 1) === '#' ) {
                            $action = basename( getenv('SCRIPT_NAME') ) . $match[5];
                            $form = str_replace( $match[5], $action, $match[0] );
                        } else {
                            $action = $match[5];
                            $form = $match[0];
                        }
                    } else {
                        $url = basename( getenv('SCRIPT_NAME') );
                        $queryString = getenv( 'QUERY_STRING' );
                        if ( isset($queryString) && $queryString !== '' ) {
                            $queryString = str_replace( '&' . SID, '', $queryString );
                            $queryString = str_replace( SID, '', $queryString );
                            if ( $queryString !== '' ) {
                                $url .= '?' . $queryString;
                            }
                        }
                        $form = str_replace( $match[3] . $match[4] . $match[5] . $match[6],
                            $match[3] . $match[4] . $url . $match[6], $match[0] );
                        $action = $url;
                    }
                    if ( preg_match('/' . $insertString . '/i', $action) ) {
                        continue;
                    }
                    $method = strtolower( $match[10] );
                    if ( $method === 'post' ) {
                        $baseAction = $action;
                        if ( ! strstr($action, '?') ) {
                            $connector = '?';
                        } else {
                            $connector = '&';
                        }
                        if ( strstr($action, '#') ) {
                            $actionArray = explode( '#', $action );
                            $action = $actionArray[0] . $connector . $insertString;
                            if ( ! empty($actionArray[1]) ) {
                                $action .= '#' . $actionArray[1];
                            }
                        } else {
                            $action = $action . $connector . $insertString;
                        }
                        $form = str_replace( $baseAction, $action, $form );
                        $contents = str_replace( $match[0], $form, $contents );
                    } else {
                        $tag = '<input type="hidden" name="guid" value="on" />';
                        $contents = str_replace( $match[0], $form . $tag, $contents );
                    }
                    $action = '';
                }
            }
            // delete needless strings
            $contents = str_replace( '?&', '?', $contents );
            $contents = str_replace( '&&', '&', $contents );
            return $contents;
        }

    }
}
