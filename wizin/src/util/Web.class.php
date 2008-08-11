<?php
/**
 * Wizin framework web utility class
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
     * Wizin framework web utility class
     *
     * @access public
     *
     */
    class Wizin_Util_Web extends Wizin_Util
    {
        /**
         * create image thumbnail
         *
         * @param string $imagePath
         * @param integer $width
         * @param integer $height
         * @param integer $format(constant)
         * @param string $newImagePath
         * @param integer $maxImageWidth
         */
        function createThumbnail ( $imagePath, $width, $height, $format, $newImagePath, $maxImageWidth )
        {
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
            // If original image is transparent gif/png >>
            /**
             * This code is something which refers "smart_resize_image".
             * Thanks a lot for "Medium eXposure" !
             * Ref : http://www.mediumexposure.com/techblog/smart-image-resizing-while-preserving-transparency-php-and-gd-library
             */
            if ( $format === IMAGETYPE_GIF || $format === IMAGETYPE_PNG ) {
                $transparentIndex = imagecolortransparent( $image );
                if ( $transparentIndex >= 0 ) {
                    $transparentColor = imagecolorsforindex( $image, $transparentIndex );
                    $transparentIndex = imagecolorallocate( $newImage, $transparentColor['red'],
                        $transparentColor['green'], $transparentColor['blue'] );
                    imagefill( $newImage, 0, 0, $transparentIndex );
                    imagecolortransparent( $newImage, $transparentIndex );
                } else if ( $format === IMAGETYPE_PNG ) {
                    imagealphablending( $newImage, false );
                    $color = imagecolorallocatealpha( $newImage, 0, 0, 0, 127 );
                    imagefill( $newImage, 0, 0, $color );
                    imagesavealpha( $newImage, true );
                }
            }
            // If original image is transparent gif/png <<
            // image data copy
            imagecopyresampled( $newImage, $image , 0, 0, 0, 0,
                $resizeWidth, $resizeHeight, $width, $height );
            $tmpArray = explode( '.', $newImagePath );
            $newExt = array_pop( $tmpArray );
            if ( $newExt === 'gif' ) {
                imagegif( $newImage, $newImagePath );
            } else {
                imagejpeg( $newImage, $newImagePath );
            }
            imagedestroy( $image );
            imagedestroy( $newImage );
        }

        /**
         * get file by http request
         *
         * @param string $url
         * @param string $createDir
         * @return string $filePath
         */
        function getFileByHttp( $url = null, $createDir = null )
        {
            if ( empty($url) ) {
                return null;
            }
            if ( is_null($createDir) ) {
                if ( defined('WIZIN_CACHE_DIR') ) {
                    $createDir = WIZIN_CACHE_DIR;
                } else {
                    $createDir = dirname( dirname(dirname(__FILE__)) ) . '/work/cache';
                }
            }
            $replaceArray = array( '/' => '%', '.' => '%%' );
            $fileName = array_pop( explode('://', $url) );
            $filePath = $createDir . '/' . substr( md5($url), 0, 16 ) . '.tmp';

            // check file exists
            if ( file_exists($filePath) && is_readable($filePath) ) {
                return $filePath;
            }

            //
            // get file by http ( fsockopen )
            //
            $agent = getenv( 'HTTP_USER_AGENT' );
            $urlArray = parse_url( $url );
            $host = $urlArray['host'];
            $port = ( ! empty($urlArray['port']) && $urlArray['port'] != '80' ) ?
                $urlArray['port'] : '80';
            $path = $urlArray['path'];
            $path .= ( ! empty($urlArray['query']) ) ? '?' . str_replace( '&amp;', '&', $urlArray['query'] ): '';
            $path .= ( ! empty($urlArray['fragment']) ) ? '#' . $urlArray['fragment'] : '';
            $referer = '';
            $https = getenv( 'HTTPS' );
            if ( empty($https) || strtolower($https) !== 'on' ) {
                $referer = 'http://';
                $referer .= getenv( 'SERVER_NAME' );
                $port = getenv( 'SERVER_PORT' );
                if ( ! empty($port) && $port != '80' ) {
                    $referer .= ':' . getenv( 'SERVER_PORT' );
                }
                $referer .= getenv( 'REQUEST_URI' );
            }
            $replaceArray = array( "\r" => '', "\n" => '' );

            // socket connect
            $fp = fsockopen( $host, $port, $errNumber, $errString, 1 );
            if ( $fp ) {
                // send request
                $request  = "GET $path HTTP/1.1 \r\n";
                $request .= "Host: $host \r\n";
                if ( $referer !== '' ) {
                    $request .= "Referer: $referer \r\n";
                }
                $request .= "User-Agent: $agent \r\n";
                $request .= "Connection: Close \r\n\r\n";
                stream_set_timeout( $fp, 1, 0 );
                fwrite( $fp, $request );

                // get data
                while ( ! feof($fp) ) {
                    $buffer = fgets( $fp, 256 );
                    if ( empty($buffer) ) {
                        continue;
                    }
                    $buffer = strtr( $buffer, $replaceArray );
                    if ( empty($buffer) ) {
                        break;
                    }
                }
                $data = '';
                while ( ! feof($fp) ) {
                	$data .= fread( $fp, 8192 );
                }
                stream_set_timeout( $fp, 0, 2 );
                fclose( $fp );

                // save file
                $saveHandler = fopen( $filePath, 'wb' );
                fwrite( $saveHandler, $data );
                fclose( $saveHandler );
                chmod( $filePath, 0666 );
                return $filePath;
            }
            return '';
        }

    }
}
