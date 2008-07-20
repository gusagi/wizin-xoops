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

if ( ! class_exists('Wizin_Plugin_User_Au') ) {
    class Wizin_Plugin_User_Au extends Wizin_StdClass
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
                $filter->addOutputFilter( array( $this, 'filterAu' ), $params );
            }
        }

        function filterAu( & $contents )
        {
            // pattern 1 ( "method=, action=" pattern )
            $pattern = '(<form)([^>]*)(method=)([\"\'])(post|get)([\"\'])([^>]*)(action=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                $queryString = getenv( 'QUERY_STRING' );
                $queryString = str_replace( '&' . SID, '', $queryString );
                $queryString = str_replace( SID, '', $queryString );
                foreach ( $matches as $key => $match) {
                    if ( ! empty($match[10]) ) {
                        if ( ! empty($match[10]) && $match[10] !== '#' ) {
                            continue;
                        }
                        if ( ! empty($queryString) ) {
                            $action = basename( getenv('SCRIPT_NAME') ) . '?' . $queryString . $match[10];
                        } else {
                            $action = basename( getenv('SCRIPT_NAME') ) . $match[10];
                        }
                        $form = str_replace( $match[10], $action, $match[0] );
                    } else {
                        $url = basename( getenv('SCRIPT_NAME') );
                        if ( isset($queryString) && $queryString !== '' ) {
                            if ( $queryString !== '' ) {
                                $url .= '?' . $queryString;
                            }
                        }
                        $form = str_replace( $match[8] . $match[9] . $match[10] . $match[11],
                            $match[8] . $match[9] . $url . $match[11], $match[0] );
                        $action = $url;
                    }
                    $contents = str_replace( $match[0], $form . $tag, $contents );
                    $action = '';
                }
            }
            // pattern 2 ( "action=, method=" pattern )
            $pattern = '(<form)([^>]*)(action=)([\"\'])(\S*)([\"\'])([^>]*)(method=)([\"\'])(post|get)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                $queryString = getenv( 'QUERY_STRING' );
                $queryString = str_replace( '&' . SID, '', $queryString );
                $queryString = str_replace( SID, '', $queryString );
                foreach ( $matches as $key => $match) {
                    if ( ! empty($match[5]) ) {
                        if ( ! empty($match[5]) && $match[5] !== '#' ) {
                            continue;
                        }
                        if ( ! empty($queryString) ) {
                            $action = basename( getenv('SCRIPT_NAME') ) . '?' . $queryString . $match[5];
                        } else {
                            $action = basename( getenv('SCRIPT_NAME') ) . $match[5];
                        }
                        $form = str_replace( $match[5], $action, $match[0] );
                    } else {
                        $url = basename( getenv('SCRIPT_NAME') );
                        if ( isset($queryString) && $queryString !== '' ) {
                            if ( $queryString !== '' ) {
                                $url .= '?' . $queryString;
                            }
                        }
                        $form = str_replace( $match[3] . $match[4] . $match[5] . $match[6],
                            $match[3] . $match[4] . $url . $match[6], $match[0] );
                        $action = $url;
                    }
                    $contents = str_replace( $match[0], $form, $contents );
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
