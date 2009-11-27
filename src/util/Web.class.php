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

if (! class_exists('Wizin_Util_Web')) {
    require dirname(dirname(__FILE__)) . '/Wizin_Util.class.php';

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
        function createThumbnail ($imagePath, $width, $height, $format, $newImagePath, $maxImageWidth)
        {
            $resizeRate = $maxImageWidth / $width;
            $copyFunction = 'imagecopyresampled';
            if ($resizeRate >= 1) {
                $resizeRate = 1;
                $copyFunction = 'imagecopy';
            }
            $resizeWidth = $resizeRate * $width;
            if ($resizeWidth < 1) {
                $resizeWidth = 1;
            }
            $resizeHeight = $resizeRate * $height;
            if ($resizeHeight < 1) {
                $resizeHeight = 1;
            }
            switch ($format) {
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($imagePath);
                    break;
                case IMAGETYPE_JPEG:
                    $image = imagecreatefromjpeg($imagePath);
                    break;
                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($imagePath);
                    break;
            }
            $newImage = imagecreatetruecolor($resizeWidth, $resizeHeight);
            // If original image is transparent gif/png >>
            /**
             * This code is something which refers "smart_resize_image".
             * Thanks a lot for "Medium eXposure" !
             * Ref : http://www.mediumexposure.com/techblog/smart-image-resizing-while-preserving-transparency-php-and-gd-library
             */
            if ($format === IMAGETYPE_GIF || $format === IMAGETYPE_PNG) {
                $transparentIndex = imagecolortransparent($image);
                if ($transparentIndex >= 0) {
                    // GIF / PNG-8
                    $transparentColor = imagecolorsforindex($image, $transparentIndex);
                    $transparentIndex = imagecolorallocate($newImage, $transparentColor['red'],
                        $transparentColor['green'], $transparentColor['blue']);
                    imagefill($newImage, 0, 0, $transparentIndex);
                    imagecolortransparent($newImage, $transparentIndex);
                } else {
                    // PNG-24
                    imagealphablending($newImage, false);
                    $color = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                    imagefill($newImage, 0, 0, $color);
                    imagesavealpha($newImage, true);
                }
            }
            // If original image is transparent gif/png <<
            // image data copy
            if ($copyFunction === 'imagecopyresampled') {
                imagecopyresampled($newImage, $image , 0, 0, 0, 0,
                    $resizeWidth, $resizeHeight, $width, $height);
            } else if ($copyFunction === 'imagecopy') {
                imagecopy($newImage, $image , 0, 0, 0, 0, $resizeWidth, $resizeHeight);
            }
            $tmpArray = explode('.', $newImagePath);
            $newExt = array_pop($tmpArray);
            if ($newExt === 'gif') {
                imagegif($newImage, $newImagePath);
            } else {
                imagejpeg($newImage, $newImagePath);
            }
            imagedestroy($image);
            imagedestroy($newImage);
        }

        /**
         * get file by http request
         *
         * @param string $url
         * @param string $createDir
         * @return string $filePath
         */
        function getFileByHttp($url = null, $createDir = null, $sendReferer = false)
        {
            if (empty($url)) {
                return null;
            }
            if (is_null($createDir)) {
                if (defined('WIZIN_CACHE_DIR')) {
                    $createDir = WIZIN_CACHE_DIR;
                } else {
                    $createDir = dirname(dirname(dirname(__FILE__))) . '/work/cache';
                }
            }
            $replaceArray = array('/' => '%', '.' => '%%');
            $fileName = array_pop(explode('://', $url));
            $filePath = $createDir . '/' . substr(md5($url), 0, 16) . '.tmp';

            // check file exists
            if (file_exists($filePath) && is_readable($filePath)) {
                return $filePath;
            }

            //
            // get file by http (fsockopen)
            //
            $agent = getenv('HTTP_USER_AGENT');
            $urlArray = parse_url($url);
            $host = $urlArray['host'];
            $port = (! empty($urlArray['port']) && $urlArray['port'] != '80') ?
                $urlArray['port'] : '80';
            $path = $urlArray['path'];
            $path .= (! empty($urlArray['query'])) ? '?' . str_replace('&amp;', '&', $urlArray['query']): '';
            $path .= (! empty($urlArray['fragment'])) ? '#' . $urlArray['fragment'] : '';
            $referer = '';
            $https = getenv('HTTPS');
            if ($sendReferer === true && (empty($https) || strtolower($https) !== 'on')) {
                $referer = 'http://';
                $referer .= getenv('SERVER_NAME');
                $port = getenv('SERVER_PORT');
                if (! empty($port) && $port != '80') {
                    $referer .= ':' . getenv('SERVER_PORT');
                }
                $referer .= getenv('REQUEST_URI');
            }
            $replaceArray = array("\r" => '', "\n" => '');

            // socket connect
            $fp = fsockopen($host, $port, $errNumber, $errString, 1);
            if ($fp) {
                // send request
                $request  = "GET $path HTTP/1.1 \r\n";
                $request .= "Host: $host \r\n";
                if ($referer !== '') {
                    $request .= "Referer: $referer \r\n";
                }
                $request .= "User-Agent: $agent \r\n";
                $request .= "Connection: Close \r\n\r\n";
                stream_set_timeout($fp, 5, 0);
                fwrite($fp, $request);

                // get data
                while (! feof($fp)) {
                    $buffer = fgets($fp, 256);
                    if (empty($buffer)) {
                        continue;
                    }
                    $buffer = strtr($buffer, $replaceArray);
                    if (empty($buffer)) {
                        break;
                    }
                }
                $data = '';
                while (! feof($fp)) {
                    $data .= fread($fp, 8192);
                }
                stream_set_timeout($fp, 5, 0);
                fclose($fp);

                // save file
                $saveHandler = fopen($filePath, 'wb');
                fwrite($saveHandler, $data);
                fclose($saveHandler);
                chmod($filePath, 0666);
                return $filePath;
            }
            return '';
        }

        /**
         * get contents by http request
         *
         * @param string $url
         * @return string $contents
         */
        function getContentsByHttp($url = null, $agent = '', $referer = '', $sendReferer = false)
        {
            if (empty($url)) {
                return null;
            }
            //
            // get contents by http (fsockopen)
            //
            $urlArray = parse_url($url);
            $host = $urlArray['host'];
            $port = (! empty($urlArray['port']) && $urlArray['port'] != '80') ?
                $urlArray['port'] : '80';
            $path = $urlArray['path'];
            $path .= (! empty($urlArray['query'])) ? '?' . str_replace('&amp;', '&', $urlArray['query']): '';
            $path .= (! empty($urlArray['fragment'])) ? '#' . $urlArray['fragment'] : '';
            $referer = '';
            $https = getenv('HTTPS');
            if ($sendReferer === true && (empty($https) || strtolower($https) !== 'on')) {
                $referer = 'http://';
                $referer .= getenv('SERVER_NAME');
                $port = getenv('SERVER_PORT');
            }
            $replaceArray = array("\r" => '', "\n" => '');

            // socket connect
            $fp = fsockopen($host, $port, $errNumber, $errString, 1);
            if ($fp) {
                // send request
                $request  = "GET $path HTTP/1.0 \r\n";
                $request .= "Host: $host \r\n";
                if ($referer !== '') {
                    $request .= "Referer: $referer \r\n";
                }
                if ($agent !== '') {
                    $request .= "User-Agent: $agent \r\n";
                }
                $request .= "Connection: Close \r\n\r\n";
                stream_set_timeout($fp, 5, 0);
                fwrite($fp, $request);

                // get data
                while (! feof($fp)) {
                    $buffer = fgets($fp, 256);
                    if (empty($buffer)) {
                        continue;
                    }
                    $buffer = strtr($buffer, $replaceArray);
                    if (empty($buffer)) {
                        break;
                    }
                }
                $contents = '';
                while (! feof($fp)) {
                    $contents .= fread($fp, 8192);
                }
                stream_set_timeout($fp, 5, 0);
                fclose($fp);
                return $contents;
            }
            return '';
        }

        function setCheckLocationHeader()
        {
            if (intval(PHP_VERSION) < 5) {
                ob_start(array('Wizin_Util_Web', 'checkLocationHeader'));
            } else {
                register_shutdown_function(array('Wizin_Util_Web', 'checkLocationHeader'));
            }
        }

        function checkLocationHeader($buf = '')
        {
            $sessionId = session_id();
            $sessionName = session_name();
            if (empty($sessionId) || empty($sessionName)) {
                return $buf;
            }
            $sessionName = ini_get('session.name');
            if (empty($_GET[$sessionName]) && empty($_POST[$sessionName])) {
                return $buf;
            }
            $headers = array();
            if (function_exists('apache_response_headers')) {
                $headers = apache_response_headers();
            } else {
                if (function_exists('headers_list')) {
                    $headersList = headers_list();
                    foreach ($headersList as $header) {
                        $parseHeader = explode(':', $header);
                        $key = array_shift($parseHeader);
                        $value = implode(':', $parseHeader);
                        $headers[$key] = $value;
                    }
                    unset($headersList);
                }
            }
            foreach ($headers as $key => $value) {
                if (strtolower(trim($key)) === 'location') {
                    $url = trim($value);
                    $urlFirstChar = substr($url, 0, 1);
                    if (strpos($url, WIZIN_URL) === 0 || $urlFirstChar === '.' ||
                            $urlFirstChar === '/' || $urlFirstChar === '#') {
                        if (strpos($url, $sessionName) === false) {
                            if (! strstr($url, '?')) {
                                $connector = '?';
                            } else {
                                $connector = '&';
                            }
                            if (strstr($url, '#')) {
                                $urlArray = explode('#', $url);
                                $url = $urlArray[0] . $connector . $sessionName .
                                    '=' . session_id();
                                if (! empty($urlArray[1])) {
                                    $url .= '#' . $urlArray[1];
                                }
                            } else {
                                $url .= $connector . $sessionName . '=' . session_id();
                            }
                            header('Location: ' . $url);
                            exit();
                        }
                    }
                }
            }
            return $buf;
        }
    }
}
