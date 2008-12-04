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
    require_once WIZIN_ROOT_PATH . '/src/util/Web.class.php';

    /**
     * Wizin framework common filter class
     *
     */
    class Wizin_Filter_Common extends Wizin_StdClass
    {
        /**
         * constructor
         *
         * @access public
         *
         */
        function __construct()
        {
            if ( extension_loaded('mbstring') ) {
                ini_set( 'mbstring.http_input', 'pass' );
                ini_set( 'mbstring.http_output', 'pass' );
                ini_set( 'mbstring.encoding_translation', 0 );
                ini_set( 'mbstring.substitute_character', null );
            }
        }

        /**
         *
         * @return object $instance
         */
        function &getSingleton()
        {
            static $instance;
            if ( ! isset($instance) ) {
                $instance = new Wizin_Filter();
            }
            return $instance;
        }

        /**
         * execute input filter
         *
         * @access public
         *
         */
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

        /**
         * execute output filter
         *
         * @access public
         *
         * @param string $contents
         */
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

        /**
         * input encoding filter
         *
         * @param string $inputEncoding
         */
        function filterInputEncoding( $inputEncoding = '' )
        {
            if ( extension_loaded('mbstring') ) {
                if ( empty($inputEncoding) ) {
                    $inputEncoding = mb_detect_encoding( serialize($_REQUEST), 'auto' );
                }
                $internalEncoding = mb_internal_encoding();
                if ( in_array(strtolower($internalEncoding), array('sjis', 'shift_jis', 'ms_kanji',
                        'csshift_jis')) ) {
                    $internalEncoding = 'sjis-win';
                } else if ( in_array(strtolower($internalEncoding), array('euc-jp',
                        'extended_unix_code_packed_format_for_japanese', 'cseucpkdfmtjapanese')) ) {
                    $internalEncoding = 'eucjp-win';
                }
                mb_convert_variables( $internalEncoding, $inputEncoding, $_GET );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_POST );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_REQUEST );
                mb_convert_variables( $internalEncoding, $inputEncoding, $_COOKIE );
            }
        }

        /**
         * output encoding filter
         *
         * @param string $contents
         * @param string $outputEncoding
         * @param string $outputCharset
         * @return string $contents
         */
        function filterOutputEncoding( & $contents, $outputEncoding, $outputCharset )
        {
            if ( extension_loaded('mbstring') ) {
                if ( ! empty($outputEncoding) && ! empty($outputEncoding) ) {
                    // exchange doctype
                    $pattern = '(<\?xml)([^>]*)(encoding=)([\"\'])(\S*)([\"\'])([^>]*)(\?>)';
                    $replacement = '${1}${2}${3}${4}' . $outputCharset . '${6}${7}${8}';
                    $contents = preg_replace( "/" .$pattern ."/i", $replacement, $contents );
                    // exchange meta header
                    $pattern = '(<meta)([^>]*)(http-equiv=)([^>]*)(charset=)(\S*)([\"\'])([^>]*)(>)';
                    $replacement = '${1}${2}${3}${4}${5}' . $outputCharset . '${7}${8}${9}';
                    $contents = preg_replace( "/" .$pattern ."/i", $replacement, $contents );
                    // convert all contents
                    $internalEncoding = mb_internal_encoding();
                    if ( in_array(strtolower($internalEncoding), array('sjis', 'shift_jis', 'ms_kanji',
                            'csshift_jis')) ) {
                        $internalEncoding = 'sjis-win';
                    } else if ( in_array(strtolower($internalEncoding), array('euc-jp',
                            'extended_unix_code_packed_format_for_japanese', 'cseucpkdfmtjapanese')) ) {
                        $internalEncoding = 'eucjp-win';
                    }
                    mb_convert_variables( $outputEncoding, $internalEncoding, $contents );
                    // convert url encoded string
                    $pattern = '(<a)([^>]*)(href=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
                    preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
                    if ( ! empty($matches) ) {
                        foreach ( $matches as $key => $match) {
                            $urlString = '';
                            $queryPart = '';
                            $query = array();
                            if ( strpos($match[5], '?') !== false ) {
                                $urlString = $match[5];
                                $flagmentArray = array();
                                if ( strpos($urlString, '#') !== false ) {
                                    $flagmentArray = explode( '#', $urlString );
                                    $urlString = $flagmentArray[0];
                                }
                                $urlArray = explode( '?', $urlString );
                                if ( ! empty($urlArray[1]) ) {
                                    $queryArray = explode( '&', $urlArray[1] );
                                    foreach ( $queryArray as $queryPart ) {
                                        if ( empty($queryPart) ) {
                                            continue;
                                        }
                                        $queryKey = '';
                                        $queryValue = '';
                                        if ( strpos($queryPart, '=') !== false ) {
                                            list( $queryKey, $queryValue ) = explode( '=', $queryPart );
                                            $queryValue = urldecode( $queryValue );
                                            mb_convert_variables( $outputEncoding, $internalEncoding, $queryValue );
                                            $queryValue = urlencode( $queryValue );
                                            $query[] = $queryKey . '=' . $queryValue;
                                        } else {
                                            $query[] = $queryPart;
                                        }
                                    }
                                    $queryString = implode( '&', $query );
                                    $contents = str_replace( $match[3] . $match[4] .$match[5] . $match[6],
                                        $match[3] . $match[4] . str_replace($urlArray[1], $queryString, $match[5]) .
                                        $match[6],  $contents );
                                }
                            }
                        }
                    }
                    ini_set( 'default_charset', $outputCharset );
                }
            }
            return $contents;
        }

        /**
         * insert SID(similar TransSID) filter
         *
         * @param string $contents
         * @param string $baseUri
         * @param string $currentUri
         * @return string $contents
         */
        function filterTransSid( & $contents, $baseUri, $currentUri )
        {
            // link
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
                            continue;
                            /*
                            $urlArray = explode( '#', $currentUri );
                            $url = $urlArray[0] . $url;
                            */
                        } else if ( substr($url, 0, 1) === '/' ) {
                            $parseUrl = parse_url( $baseUri );
                            $path = '';
                            if ( isset($parseUrl['path']) ) {
                                $path = $parseUrl['path'];
                            }
                            $url = str_replace( $path, '', $baseUri ) . $url;
                        } else {
                            $url = dirname( $currentUri ) . '/' . $url;
                        }
                    }
                    $check = strstr( $url, $baseUri );
                    if ( $check !== false ) {
                        if ( strpos($url, session_name()) === false ) {
                            if ( strpos($url, '?') === false ) {
                                $connector = '?';
                            } else {
                                $connector = '&amp;';
                            }
                            if ( strpos($url, '#') !== false ) {
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
            $pattern = '(<form)([^>]*)(action=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
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
                                $path = '';
                                if ( isset($parseUrl['path']) ) {
                                    $path = $parseUrl['path'];
                                }
                                $action = str_replace( $path, '', $baseUri ) . $action;
                            } else {
                                $action = dirname( $currentUri ) . '/' . $action;
                            }
                        }
                    } else {
                        $url = dirname( $currentUri );
                        if ( substr($url, -1, 1) !== '/' ) {
                            $url .= '/';
                        }
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

        /**
         * image resize filter
         *
         * @param string $contents
         * @param string $baseUri
         * @param string $currentUri
         * @param string $basePath
         * @param string $createDir
         * @param integer $maxWidth
         */
        function filterResizeImage ( & $contents, $baseUri, $currentUri, $basePath, $createDir = null, $maxWidth = 0, $forceResizeType = '' )
        {
            if ( is_null($createDir) ) {
                if ( defined('WIZIN_CACHE_DIR') ) {
                    $createDir = WIZIN_CACHE_DIR;
                } else {
                    $createDir = dirname( dirname(dirname(__FILE__)) ) . '/work/cache';
                }
            }
            if ( $forceResizeType === '' ) {
                $forceResizeType = array();
            }
            // image resize
            if ( extension_loaded('gd') ) {
                clearstatcache();
                $allowImageFormat = array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG );
                $pattern = '(<img)([^>]*)(src=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
                preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
                if ( ! empty($matches) ) {
                    foreach ( $matches as $key => $match) {
                        $maxImageWidth = $maxWidth;
                        $getFileFlag = false;
                        $imageUrl = $match[5];
                        if ( $imageUrl === '' ) {
                            continue;
                        } else if ( substr($imageUrl, 0, 4) !== 'http' ) {
                            if ( substr($imageUrl, 0, 1) === '/' ) {
                                $parseUrl = parse_url( $baseUri );
                                $path = '';
                                if ( isset($parseUrl['path']) ) {
                                    $path = $parseUrl['path'];
                                }
                                $imageUrl = str_replace( $path, '', $baseUri ) . $imageUrl;
                            } else {
                                $imageUrl = dirname( $currentUri ) . '/' . $imageUrl;
                            }
                        }
                        if ( strpos($imageUrl, $baseUri) === 0 ) {
                            $imagePath = str_replace( $baseUri, $basePath, $imageUrl );
                            if ( ! file_exists($imagePath) ) {
                                $imagePath = Wizin_Util_Web::getFileByHttp( $imageUrl );
                                if ( $imagePath === '' ) {
                                    continue;
                                }
                                $getFileFlag = true;
                            }
                            $ext = array_pop( explode('.', basename($imagePath)) );
                            $urlArray = parse_url( $imageUrl );
                            $newImageFile = str_replace( '/', '_', $urlArray['path'] );
                            $newImageFile = str_replace( $ext, '', $newImageFile );
                            $newImagePath = $createDir . '/' . $newImageFile;
                            if ( function_exists('imagegif') ) {
                                $newExt = 'gif';
                            } else {
                                $newExt = 'jpg';
                            }
                            $newImagePath .= $newExt;
                            $newImageUrl = str_replace( $basePath, $baseUri, $newImagePath );
                            $imageSizeInfo = getimagesize( $imagePath );
                            $width = $imageSizeInfo[0];
                            $height = $imageSizeInfo[1];
                            $format = $imageSizeInfo[2];
                            if ( $width == 0 || $height == 0 ) {
                                // Maybe the file is the script which send image, get file by http.
                                $imagePath = Wizin_Util_Web::getFileByHttp( $imageUrl );
                                if ( $imagePath === '' ) {
                                    continue;
                                }
                                $getFileFlag = true;
                                $imageSizeInfo = getimagesize( $imagePath );
                                $width = $imageSizeInfo[0];
                                $height = $imageSizeInfo[1];
                                $format = $imageSizeInfo[2];
                            }
                            if ( $getFileFlag && $width <= $maxImageWidth ) {
                                $maxImageWidth = $width;
                            }
                            if ( $width !== 0 && $height !== 0 ) {
                                if ( ($width >= $maxImageWidth && in_array($format, $allowImageFormat)) ||
                                        in_array($format, $forceResizeType) ) {
                                    if ( ! file_exists($newImagePath) ||
                                            (filemtime($newImagePath) <= filemtime($imagePath)) ) {
                                        Wizin_Util_Web::createThumbnail( $imagePath, $width, $height,
                                            $format, $newImagePath, $maxImageWidth );
                                    }
                                    $imageTag = str_replace( $match[3] . $match[4] .$match[5] . $match[6],
                                        $match[3] . $match[4] . $newImageUrl . $match[6], $match[0] );
                                    $replaceArray = array( "'" => "\'", '"' => '\"', '\\' => '\\\\',
                                        '/' => '\/', '(' => '\(', ')' => '\)', '.' => '\.', '?' => '\?' );
                                    $linkCheckPattern = '(<a)([^>]*)(href=)([^>]*)(>)((?:(?!<\/a>).)*)('
                                        . strtr($match[0], $replaceArray) . ')';
                                    if ( preg_match("/" .$linkCheckPattern ."/is", $contents) ) {
                                        $contents = str_replace( $match[0], $imageTag, $contents );
                                    } else {
                                        $imageLink = '<a href="' . $imageUrl . '">' . $imageTag . '</a>';
                                        $contents = str_replace( $match[0], $imageLink, $contents );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

    }
}
