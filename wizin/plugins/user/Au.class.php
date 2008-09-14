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
            require_once WIZIN_ROOT_PATH . '/src/filter/Mobile.class.php';
        }

        function _setup()
        {
            static $calledFlag;
            if ( ! isset($calledFlag) ) {
                $calledFlag = true;
                $filter =& Wizin_Filter_Mobile::getSingleton();
                $params = array();
                $filter->addOutputFilter( array( $this, 'filterAu' ), $params );
            }
        }

        function filterAu( & $contents )
        {
            $this->_replaceBlankAction( $contents );
            return $contents;
        }

        function _replaceBlankAction( & $contents )
        {
            $pattern = '(<form)([^>]*)(action=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                // get query string
                $queryString = getenv( 'QUERY_STRING' );
                $queryString = str_replace( '&' . SID, '', $queryString );
                $queryString = str_replace( SID, '', $queryString );
                // get script name
                $tmpUrl = 'http://' . getenv( 'SERVER_NAME' ) . getenv( 'REQUEST_URI' );
                $tmpUrlArray = parse_url( $tmpUrl );
                $tmpUrl = @ 'http://' . getenv( 'SERVER_NAME' ) . $tmpUrlArray['path'];
                if ( substr($tmpUrl, -1, 1) === '/' ) {
                    $script = 'index.php';
                } else {
                    $script = basename( $tmpUrl );
                }
                foreach ( $matches as $key => $match) {
                    if ( isset($match[5]) && $match[5] !== '' ) {
                        if ( substr($match[5], 0, 1) === '#' ) {
                            if ( ! empty($queryString) ) {
                                $action = $script . '?' . $queryString . $match[5];
                            } else {
                                $action = $script . $match[5];
                            }
                            $form = str_replace( $match[3] . $match[4] . $match[5] . $match[6],
                                $match[3] . $match[4] . $action . $match[6], $match[0] );
                        } else {
                            continue;
                        }
                    } else {
                        $action = $script;
                        if ( isset($queryString) && $queryString !== '' ) {
                            $action .= '?' . $queryString;
                        }
                        $form = str_replace( $match[3] . $match[4] . $match[5] . $match[6],
                            $match[3] . $match[4] . $action . $match[6], $match[0] );
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
