<?php
/**
 * Wizin framework filter class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Filter_Common') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
    require_once 'src/util/Web.class.php';

    class Wizin_Filter_Common extends Wizin_StdClass
    {
        function __construct()
        {
            if ( extension_loaded('mbstring') ) {
                ini_set( 'mbstring.http_input', 'pass' );
                ini_set( 'mbstring.http_output', 'pass' );
                ini_set( 'mbstring.encoding_translation', 0 );
                ini_set( 'mbstring.substitute_character', null );
            }
        }

        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Filter();
            }
            return $instance;
        }

        function executeInputFilter()
        {
            $inputFilter = $this->_aInputFilter;
            for ( $index = 0; $index < count($inputFilter); $index ++ ) {
                $filter = & $inputFilter[$index];
                $function =& $filter[0];
                $params =& $filter[1];
                Wizin_Util::callUserFuncArrayReference( $function, $params );
                unset( $filter );
                unset( $function );
                unset( $params );
            }
            $this->_aInputFilter = array();
        }

        function executeOutputFilter( & $contents )
        {
            $outputFilter = $this->_aOutputFilter;
            for ( $index = 0; $index < count($outputFilter); $index ++ ) {
                $filter = & $outputFilter[$index];
                $function =& $filter[0];
                $params = array();
                $params[] =& $contents;
                for ( $argIndex = 0; $argIndex < count($filter[1]); $argIndex ++ ) {
                    $params[] =& $filter[1][$argIndex];
                }
                Wizin_Util::callUserFuncArrayReference( $function, $params );
                unset( $filter );
                unset( $function );
                unset( $params );
            }
            $this->_aOutputFilter = array();
        }

        function filterInputEncoding( $inputEncoding = '' )
        {
            if ( extension_loaded('mbstring') ) {
                if ( empty($inputEncoding) ) {
                    $inputEncoding = mb_detect_encoding( serialize($_REQUEST), 'auto' );
                }
                $internalEncoding = mb_internal_encoding();
                mb_convert_variables( $internalEncoding, $inputEncoding, $_GET );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_POST );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_REQUEST );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_COOKIE );
            }
        }

        function filterOutputEncoding( & $contents, $outputEncoding, $outputCharset )
        {
            if ( extension_loaded('mbstring') ) {
                if ( ! empty($outputEncoding) && ! empty($outputEncoding) ) {
                    $contents = str_replace( 'charset=' ._CHARSET, 'charset=' . $outputCharset, $contents );
                    $pattern = '(=)([\"\'])(' . _CHARSET . ')([\"\'])';
                    $replacement = '${1}${2}' . $outputCharset . '${4}';
                    $contents = preg_replace( "/" .$pattern ."/", $replacement, $contents );
                    $contents = mb_convert_encoding( $contents, $outputEncoding, mb_internal_encoding() );
                    ini_set( 'default_charset', $outputCharset );
                }
            }
            return $contents;
        }

        function filterOptimizeMobile( & $contents, $baseUri, $currentUri, $basePath, $createDir = WIZIN_CACHE_DIR )
        {
            $maxImageWidth = 220;
            Wizin_Util_Web::createThumbnail( $contents, $baseUri, $currentUri, $basePath, $createDir, $maxImageWidth );
            // replace input type "password" => "text"
            $pattern = '(<input)([^>]*)(type=)([\"\'])(password)([\"\'])([^>]*)(>)';
            $replacement = '${1}${2}${3}${4}text${6} ${7}${8}';
            $contents = preg_replace( "/" .$pattern ."/i", $replacement, $contents );
            // delete script tags
            $pattern = '@<script[^>]*?>.*?<\/script>@si';
            $replacement = '';
            $contents = preg_replace( $pattern, $replacement, $contents );
            // delete del tags
            $pattern = '@<del[^>]*?>.*?<\/del>@si';
            $replacement = '';
            $contents = preg_replace( $pattern, $replacement, $contents );
            // delete comment
            $pattern = '<!--[\s\S]*?-->';
            $replacement = '';
            $contents = preg_replace( "/" .$pattern ."/", $replacement, $contents );
            // convert from zenkaku to hankaku
            if ( extension_loaded('mbstring') ) {
                $contents = mb_convert_kana( $contents, 'knr' );
            }
            return $contents;
        }

        function filterTransSid( & $contents, $baseUri, $currentUri )
        {
            // get method
            $pattern = '(<a)([^>]*)(href=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
            preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
            if ( ! empty($matches) ) {
                foreach ( $matches as $key => $match) {
                    $href = '';
                    $hrefArray = array();
                    $url = $match[5];
                    if ( substr($url, 0, 4) !== 'http' ) {
                        if ( strpos($url, ':') !== false ) {
                            continue;
                        } else if ( substr($url, 0, 1) === '#' ) {
                            $urlArray = explode( '#', $currentUri );
                            $url = $urlArray[0] . $url;
                        } else if ( substr($url, 0, 1) === '/' ) {
                            $parseUrl = parse_url( $baseUri );
                            $url = str_replace( $parseUrl['path'], '', $baseUri ) . $url;
                        } else {
                            $url = dirname( $currentUri ) . '/' . $url;
                        }
                    }
                    $check = strstr( $url, $baseUri );
                    if ( $check !== false ) {
                        if ( ! strpos($url, session_name()) ) {
                            if ( ! strstr($url, '?') ) {
                                $connector = '?';
                            } else {
                                $connector = '&';
                            }
                            if ( strstr($url, '#') ) {
                                $hrefArray = explode( '#', $url );
                                $href .= $hrefArray[0] . $connector . SID;
                                if ( ! empty($hrefArray[1]) ) {
                                    $href .= '#' . $hrefArray[1];
                                }
                            } else {
                                $href = $url . $connector . SID;
                            }
                            $contents = str_replace( $match[3] . $match[4] .$match[5] . $match[6],
                                $match[3] . $match[4] . $href . $match[6], $contents );
                        }
                    }
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
                        $form = $match[0];
                        $action = $match[10];
                        if ( substr($action, 0, 4) !== 'http' ) {
                            if ( strpos($action, ':') !== false ) {
                                continue;
                            } else if ( substr($action, 0, 1) === '#' ) {
                                $urlArray = explode( '#', $currentUri );
                                $action = $urlArray[0] . $action;
                            } else if ( substr($action, 0, 1) === '/' ) {
                                $parseUrl = parse_url( $baseUri );
                                $action = str_replace( $parseUrl['path'], '', $baseUri ) . $action;
                            } else {
                                $action = dirname( $currentUri ) . '/' . $action;
                            }
                        }
                    } else {
                        $url = dirname( $currentUri );
                        $url .= basename( getenv('SCRIPT_NAME') );
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
                    $check = strstr( $action, $baseUri );
                    if ( $check !== false ) {
                        $tag = '<input type="hidden" name="' . session_name() . '" value="' . session_id() . '" />';
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
                        $form = $match[0];
                        $action = $match[5];
                        if ( substr($action, 0, 4) !== 'http' ) {
                            if ( strpos($action, ':') !== false ) {
                                continue;
                            } else if ( substr($action, 0, 1) === '#' ) {
                                $urlArray = explode( '#', $currentUri );
                                $action = $urlArray[0] . $action;
                            } else if ( substr($action, 0, 1) === '/' ) {
                                $parseUrl = parse_url( $baseUri );
                                $action = str_replace( $parseUrl['path'], '', $baseUri ) . $action;
                            } else {
                                $action = dirname( $currentUri ) . '/' . $action;
                            }
                        }
                    } else {
                        $url = dirname( $currentUri );
                        $url .= basename( getenv('SCRIPT_NAME') );
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
                    $check = strstr( $action, $baseUri );
                    if ( $check !== false ) {
                        $tag = '<input type="hidden" name="' . session_name() . '" value="' . session_id() . '" />';
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
