<?php
/**
 * Wizin framework utility class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Util_Web') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin_Util.class.php';

    /**
     * @access public
     *
     */
    class Wizin_Util_Web extends Wizin_Util
    {
        function createThumbnail ( & $contents, $baseUri, $currentUri, $basePath, $createDir = WIZIN_CACHE_DIR, $maxImageWidth = 0 )
        {
            // image resize
            if ( extension_loaded('gd') ) {
                clearstatcache();
                $allowImageFormat = array( IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG );
                $pattern = '(<img)([^>]*)(src=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
                preg_match_all( "/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER );
                if ( ! empty($matches) ) {
                    foreach ( $matches as $key => $match) {
                        $imageUrl = $match[5];
                        if ( substr($imageUrl, 0, 4) !== 'http' ) {
                            if ( substr($imageUrl, 0, 1) === '/' ) {
                                $parseUrl = parse_url( $baseUri );
                                $imageUrl = str_replace( $parseUrl['path'], '', $baseUri ) . $imageUrl;
                            } else {
                                $imageUrl = dirname( $currentUri ) . '/' . $imageUrl;
                            }
                        }
                        if ( strpos($imageUrl, $baseUri) === 0 ) {
                            $imagePath = str_replace( $baseUri, $basePath, $imageUrl );
                            $imageSizeInfo = getimagesize( $imagePath );
                            $width = $imageSizeInfo[0];
                            $height = $imageSizeInfo[1];
                            $format = $imageSizeInfo[2];
                            $tmpArray = explode( '.', basename($imagePath) );
                            $ext = array_pop( $tmpArray );
                            if ( $width !== 0 && $height !== 0 ) {
                                $resizeRate = 0;
                                if ( $width > $maxImageWidth && in_array($format, $allowImageFormat) ) {
                                    $newImagePath = $createDir . '/' . basename( $imagePath, $ext );
                                    if ( function_exists('imagegif') ) {
                                        $newExt = 'gif';
                                    } else {
                                        $newExt = 'jpg';
                                    }
                                    $newImagePath .= $newExt;
                                    $newImageUrl = str_replace( $basePath, $baseUri, $newImagePath );
                                    if ( ! file_exists($newImagePath) || (filemtime($newImagePath) <= filemtime($imagePath)) ) {
                                        $resizeRate = $maxImageWidth / $width;
                                        $resizeWidth = $resizeRate * $width;
                                        $resizeHeight = $resizeRate * $height;
                                        switch ( $format ) {
                                            case IMAGETYPE_GIF:
                                                $image = imagecreatefromgif( $imagePath );
                                                break;
                                            case IMAGETYPE_JPEG:
                                                $image = imagecreatefromjpeg( $imagePath );
                                                break;
                                            case IMAGETYPE_PNG:
                                                $image = imagecreatefrompng( $imagePath );
                                                break;
                                        }
                                        $newImage = imagecreatetruecolor( $resizeWidth, $resizeHeight );
                                        imagecopyresampled( $newImage, $image , 0, 0, 0, 0,
                                            $resizeWidth, $resizeHeight, $width, $height );
                                        if ( $newExt === 'gif' ) {
                                            imagegif( $newImage, $newImagePath );
                                        } else {
                                            imagejpeg( $newImage, $newImagePath );
                                        }
                                        imagedestroy( $image );
                                        imagedestroy( $newImage );
                                    }
                                    $imageTag = str_replace( $match[3] . $match[4] .$match[5] . $match[6],
                                        $match[3] . $match[4] . $newImageUrl . $match[6], $match[0] );
                                    $replaceArray = array( "'" => "\'", '"' => '\"', '\\' => '\\\\', '/' => '\/', '(' => '\(', ')' => '\)', '.' => '\.' );
                                    $linkCheckPattern = '(<a)([^>]*)(href=)([^>]*)(>)((?:(?!<\/a>).)*)(' . strtr($match[0], $replaceArray) . ')';
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

        function pager( $string, $maxKbyte = 0 )
        {
            if ( class_exists('DOMDocument') && class_exists('SimpleXMLElement') ) {
                // get encode
                if ( extension_loaded('mbstring') ) {
                    $encode = strtolower( mb_detect_encoding($string, 'auto') );
                    switch ( $encode ) {
                        case 'euc-jp':
                            $encode = 'eucjp-win';
                            break;
                        case 'sjis':
                            $encode = 'sjis-win';
                            break;
                        default:
                            break;
                    }
                } else {
                    $encode = 'ascii';
                }
                // convert to html-entities
                $string = trim( $string );
                if ( extension_loaded('mbstring') ) {
                    $string = mb_convert_encoding( $string, 'html-entities', $encode );
                }
                // repair
                if ( function_exists('tidy_repair_string') ) {
                    $string = tidy_repair_string( $string );
                }
                // convert to xml format
                $domDoc = new DOMDocument();
                $domDoc->loadHTML( $string );
                $string = $domDoc->saveXML();
                $string = strtr( $string, array('&' => '&amp;') );
                $xml = simplexml_load_string( $string );
                $string = $xml->body;
                unset( $domDoc );
                unset( $xml );
                // partition string
                $array = array();
                $array = Wizin_Util_Web::_partitionPage( $string, $encode, $maxKbyte );
                // set return value
                $count = count( $array ) - 1;
                if ( ! isset($array[$count]) || $array[$count] === '' ) {
                    array_pop( $array );
                }
                array_unshift( $array, '' );
                $string = '';
                $index = (! empty( $_REQUEST['mobilepage']) ) ? intval( $_REQUEST['mobilepage'] ) : 1;
                if ( count($array) >= $index ) {
                    // PEAR_Pager
                    $includePath = get_include_path();
                    set_include_path( $includePath . PATH_SEPARATOR . WIZIN_ROOT_PATH . '/lib/PEAR' );
                    require_once 'Pager/Pager.php';
                    $params = array(
                        'mode' => 'sliding',
                        'delta' => 3,
                        'perPage' => 1,
                        'prevImg' => '&laquo; Prev',
                        'nextImg' => 'Next &raquo;',
                        'urlVar' => 'mobilepage',
                        'spacesBeforeSeparator' => 1,
                        'spacesAfterSeparator' => 1,
                        'totalItems' => count($array) - 1
                        );
                    $pager =& Pager::factory($params);
                    $pageNavi = $pager->links;
                    $string .= $pageNavi . '<br />';
                    $string .= $array[$index] . '<br />';
                    $string .= $pageNavi;
                    set_include_path( $includePath );
                }
            }
            return $string;
        }

        function _partitionPage(  & $xml, $encode, $maxKbyte = 0  )
        {
            // set valiable
            if ( empty($maxKbyte) ) {
                $maxKbyte = 1;
            }
            $maxByte = $maxKbyte * 1024;
            $buffer = '';
            $noPartitionTag = array( 'form', 'tr' );
            $array = array();
            $convertMap = array(
                0x0, 0x20, 0, 0xfffff,
                0x2666, 0x10000, 0, 0xfffff);
            // get html from SimpleXMLElement
            $allAttribute = $xml->asXML();
            $allAttribute = strtr( $allAttribute, array('<body>' => '', '</body>' => '') );
            $allAttribute = strtr( $allAttribute, array('&amp;' => '&') );
            if ( extension_loaded('mbstring') ) {
                $excludedHex = $allAttribute;
                if ( preg_match("/&#[xX][0-9a-zA-Z]{1,8};/", $allAttribute) ) {
                    $excludedHex = preg_replace( "/&#[xX]([0-9a-zA-Z]{1,8});/e", "'&#'.hexdec('$1').';'", $allAttribute );
                }
                $allAttribute = mb_decode_numericentity( $excludedHex, $convertMap, $encode );
            }
            if ( strlen($allAttribute) <= $maxByte ) {
                $array[] = $allAttribute;
            } else {
                $children =& $xml->children();
                foreach ( $children as $child ) {
                    $nodeName = $child->getName();
                    $attribute = $child->asXML();
                    $attribute = strtr( $attribute, array('<body>' => '', '</body>' => '') );
                    $attribute = strtr( $attribute, array('&amp;' => '&') );
                    if ( extension_loaded('mbstring') ) {
                        $excludedHex = $attribute;
                        if ( preg_match("/&#[xX][0-9a-zA-Z]{1,8};/", $attribute) ) {
                            $excludedHex = preg_replace( "/&#[xX]([0-9a-zA-Z]{1,8});/e", "'&#'.hexdec('$1').';'", $attribute );
                        }
                        $attribute = mb_decode_numericentity( $excludedHex, $convertMap, $encode );
                    }
                    if ( strlen($attribute) > $maxByte && ! in_array( strtolower($nodeName), $noPartitionTag) ) {
                        $array[] = $buffer;
                        $buffer = '';
                        if ( extension_loaded('mbstring') ) {
                            $html = mb_convert_encoding( $attribute, 'html-entities', $encode );
                        } else {
                            $html = $attribute;
                        }
                        $domDoc = new DOMDocument();
                        $domDoc->loadHTML( $html );
                        $string = $domDoc->saveXML();
                        unset( $html );
                        unset( $domDoc );
                        $string = strtr( $string, array('&' => '&amp;') );
                        $childXml = simplexml_load_string( $string );
                        $body =& $childXml->body;
                        unset( $childXml );

                        if ( serialize($body) !== serialize($body->children()) ) {
                            // bottom layer
                            $pages = Wizin_Util_Web::_partitionPage( $body, $encode );
                            foreach ( $pages as $page ) {
                                $array[] = $page;
                            }
                            unset( $pages );
                            continue;
                        } else {
                            // not bottom layer
                            $buffer .= $attribute;
                            while ( strlen($buffer) > $maxByte ) {
                                if ( extension_loaded('mbstring') ) {
                                	$cutString = mb_substr( $buffer, 0, $maxByte );
                                } else {
                                	$cutString = substr( $buffer, 0, $maxByte );
                                }
                            	$cutStringArray = explode( '<', $cutString );
                            	array_pop( $cutStringArray );
                            	$cutString = implode( '<', $cutStringArray );
                                if ( extension_loaded('mbstring') ) {
                                    $html = mb_convert_encoding( $cutString, 'html-entities', $encode );
                                } else {
                                    $html = $cutString;
                                }
                                $domDoc = new DOMDocument();
                                $domDoc->loadHTML( $html );
                                $string = $domDoc->saveXML();
                                unset( $html );
                                unset( $domDoc );
                                $string = strtr( $string, array('&' => '&amp;') );
                                $cutXml = simplexml_load_string( $string );
                                $string = $cutXml->body->asXML();
                                $string = strtr( $string, array('<body>' => '', '</body>' => '') );
                                $string = strtr( $string, array('&amp;' => '&') );
                                if ( extension_loaded('mbstring') ) {
                                    $excludedHex = $string;
                                    if ( preg_match("/&#[xX][0-9a-zA-Z]{1,8};/", $string) ) {
                                        $excludedHex = preg_replace( "/&#[xX]([0-9a-zA-Z]{1,8});/e", "'&#'.hexdec('$1').';'", $string );
                                    }
                                    $string = mb_decode_numericentity( $excludedHex, $convertMap, $encode );
                                }
                            	$array[] = $string;
                            	$buffer = str_replace( $cutString, '', $buffer );
                            }
                            $attribute = '';
                        }
                    } else if ( (strlen($buffer) + strlen($attribute)) > $maxByte ) {
                        $array[] = $buffer;
                        $buffer = '';
                    }
                    $buffer .= $attribute;
                }
            }
            $array[] = $buffer;
            return $array;

        }
    }
}
