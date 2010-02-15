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

if (! class_exists('Wizin_Filter_Common')) {
    require dirname(dirname(__FILE__)) . '/Wizin.class.php';
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
            if (extension_loaded('mbstring')) {
                ini_set('mbstring.http_input', 'pass');
                ini_set('mbstring.http_output', 'pass');
                ini_set('mbstring.encoding_translation', 0);
                ini_set('mbstring.substitute_character', null);
            }
        }

        /**
         *
         * @return object $instance
         */
        function &getSingleton()
        {
            static $instance;
            if (! isset($instance)) {
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
            for ($index = 0; $index < count($inputFilter); $index ++) {
                $filter = & $inputFilter[$index];
                $function =& $filter[0];
                $params =& $filter[1];
                Wizin_Util::callUserFuncArrayReference($function, $params);
                unset($filter);
                unset($function);
                unset($params);
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
        function executeOutputFilter(& $contents)
        {
            $outputFilter = $this->_aOutputFilter;
            for ($index = 0; $index < count($outputFilter); $index ++) {
                $filter = & $outputFilter[$index];
                $function =& $filter[0];
                $params = array();
                $params[] =& $contents;
                for ($argIndex = 0; $argIndex < count($filter[1]); $argIndex ++) {
                    $params[] =& $filter[1][$argIndex];
                }
                Wizin_Util::callUserFuncArrayReference($function, $params);
                unset($filter);
                unset($function);
                unset($params);
            }
            $this->_aOutputFilter = array();
        }

        /**
         * input encoding filter
         *
         * @param string $inputEncoding
         */
        function filterInputEncoding($inputEncoding = '')
        {
            if (extension_loaded('mbstring')) {
                if (empty($inputEncoding)) {
                    $inputEncoding = strtolower(mb_detect_encoding(serialize($_REQUEST),
                        'ASCII,JIS,UTF-8,EUC-JP,SJIS', true));
                    switch ($inputEncoding) {
                        case 'euc-jp':
                            $inputEncoding = 'eucjp-win';
                            break;
                        case 'sjis':
                            $inputEncoding = 'sjis-win';
                            break;
                    }
                }
                $internalEncoding = mb_internal_encoding();
                if (in_array(strtolower($internalEncoding), array('sjis', 'shift_jis', 'ms_kanji',
                        'csshift_jis'))) {
                    $internalEncoding = 'sjis-win';
                } else if (in_array(strtolower($internalEncoding), array('euc-jp',
                        'extended_unix_code_packed_format_for_japanese', 'cseucpkdfmtjapanese'))) {
                    $internalEncoding = 'eucjp-win';
                }
                mb_convert_variables($internalEncoding, $inputEncoding, $_GET);
                mb_convert_variables($internalEncoding, $inputEncoding, $_POST);
                mb_convert_variables($internalEncoding, $inputEncoding, $_REQUEST);
                mb_convert_variables($internalEncoding, $inputEncoding, $_COOKIE);
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
        function filterOutputEncoding(& $contents, $outputEncoding, $outputCharset)
        {
            if (extension_loaded('mbstring')) {
                if (! empty($outputEncoding) && ! empty($outputEncoding)) {
                    // exchange doctype
                    $pattern = '(<\?xml)([^>]*)(encoding=)([\"\'])(\S*)([\"\'])([^>]*)(\?>)';
                    $replacement = '${1}${2}${3}${4}' . $outputCharset . '${6}${7}${8}';
                    $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    // exchange meta header
                    $pattern = '(<meta)([^>]*)(http-equiv=)([^>]*)(charset=)(\S*)([\"\'])([^>]*)(>)';
                    $replacement = '${1}${2}${3}${4}${5}' . $outputCharset . '${7}${8}${9}';
                    $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    // convert all contents
                    $internalEncoding = mb_internal_encoding();
                    if (in_array(strtolower($internalEncoding), array('sjis', 'shift_jis', 'ms_kanji',
                            'csshift_jis'))) {
                        $internalEncoding = 'sjis-win';
                    } else if (in_array(strtolower($internalEncoding), array('euc-jp',
                            'extended_unix_code_packed_format_for_japanese', 'cseucpkdfmtjapanese'))) {
                        $internalEncoding = 'eucjp-win';
                    }
                    mb_convert_variables($outputEncoding, $internalEncoding, $contents);
                    // convert url encoded string
                    $pattern = '(<a)([^>]*)(href=)([\"\'])([^\"\']*)([\"\'])([^>]*)(>)';
                    preg_match_all("/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER);
                    if (! empty($matches)) {
                        foreach ($matches as $key => $match) {
                            $urlString = '';
                            $queryPart = '';
                            $query = array();
                            if (strpos($match[5], '?') !== false) {
                                $urlString = $match[5];
                                $flagmentArray = array();
                                if (strpos($urlString, '#') !== false) {
                                    $flagmentArray = explode('#', $urlString);
                                    $urlString = $flagmentArray[0];
                                }
                                $urlArray = explode('?', $urlString);
                                if (! empty($urlArray[1])) {
                                    $queryArray = explode('&', $urlArray[1]);
                                    foreach ($queryArray as $queryPart) {
                                        if (empty($queryPart)) {
                                            continue;
                                        }
                                        $queryKey = '';
                                        $queryValue = '';
                                        if (strpos($queryPart, '=') !== false) {
                                            list($queryKey, $queryValue) = explode('=', $queryPart);
                                            $queryValue = urldecode($queryValue);
                                            mb_convert_variables($outputEncoding, $internalEncoding, $queryValue);
                                            $queryValue = urlencode($queryValue);
                                            $query[] = $queryKey . '=' . $queryValue;
                                        } else {
                                            $query[] = $queryPart;
                                        }
                                    }
                                    $queryString = implode('&', $query);
                                    $contents = str_replace($match[3] . $match[4] .$match[5] . $match[6],
                                        $match[3] . $match[4] . str_replace($urlArray[1], $queryString, $match[5]) .
                                        $match[6],  $contents);
                                }
                            }
                        }
                    }
                    ini_set('default_charset', $outputCharset);
                }
            }
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
        function filterResizeImage (& $contents, $params = array())
        {
            /**
             * set variables
             */
            extract($params);
            if (is_null($createDir)) {
                if (defined('WIZIN_CACHE_DIR')) {
                    $createDir = WIZIN_CACHE_DIR;
                } else {
                    $createDir = dirname(dirname(dirname(__FILE__))) . '/work/cache';
                }
            }
            if ($forceResizeType === '') {
                $forceResizeType = array();
            }
            // image resize
            if (extension_loaded('gd')) {
                clearstatcache();
                $allowImageFormat = array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG);
                $pattern = '(<img)([^>]*)(src=)([\"\'])([^\"\']*)([\"\'])([^>]*)(>)';
                preg_match_all("/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER);
                if (! empty($matches)) {
                    $imgClassPattern = '(class=)([\"\'])([^\"\']*)(wiz-img-)(\w+)(-)(\w+)([^\"\']*)([\"\'])';
                    foreach ($matches as $key => $match) {
                        $linkReplaceFlg = false;
                        $maxImageWidth = $maxWidth;
                        $getFileFlag = false;
                        $imageUrl = $match[5];
                        if ($imageUrl === '') {
                            continue;
                        } else if (substr($imageUrl, 0, 4) !== 'http') {
                            if (substr($imageUrl, 0, 1) === '/') {
                                $parseUrl = parse_url($baseUri);
                                $path = '';
                                if (isset($parseUrl['path'])) {
                                    $path = $parseUrl['path'];
                                }
                                $imageUrl = str_replace($path, '', $baseUri) . $imageUrl;
                            } else {
                                $imageUrl = dirname($currentUri) . '/' . $imageUrl;
                            }
                        }
                        /**
                         * check image setting in class name
                         */
                        preg_match("/" .$imgClassPattern ."/i", $match[0], $imgMatch);
                        if (! empty($imgMatch)) {
                            switch ($imgMatch[5]) {
                            	case 'width':
                            	    if (is_numeric($imgMatch[7])) {
                            	        $maxImageWidth = intval($imgMatch[7]);
                            	    }
                            		break;
                            	default:
                            }
                        }
                        /**
                         * resize image in own site
                         */
                        if (strpos($imageUrl, $baseUri) === 0) {
                            if (strpos($imageUrl, './') !== false) {
                                $urlArray = parse_url($imageUrl);
                                if (isset($urlArray['path'])) {
                                    $pathArray = array();
                                    $explodedPath = explode('/', $urlArray['path']);
                                    foreach ($explodedPath as $part) {
                                        if ($part === '' || $part === '.') {
                                            continue;
                                        } else if ($part === '..') {
                                            array_pop($pathArray);
                                        } else {
                                            array_push($pathArray, $part);
                                        }
                                    }
                                    $pathString = implode('/', $pathArray);
                                    if (substr($pathString, 0, 1) !== '/') {
                                        $pathString = '/' . $pathString;
                                    }
                                    $imageUrl = str_replace($urlArray['path'],
                                        $pathString, $imageUrl);
                                } else {
                                    continue;
                                }
                            }
                            $imagePath = str_replace($baseUri, $basePath, $imageUrl);
                            if (! file_exists($imagePath)) {
                                $imagePath = Wizin_Util_Web::getFileByHttp($imageUrl);
                                if ($imagePath === '') {
                                    continue;
                                }
                                $getFileFlag = true;
                            }
                            $ext = array_pop(explode('.', basename($imagePath)));
                            if (function_exists('imagegif')) {
                                $newExt = 'gif';
                            } else {
                                $newExt = 'jpg';
                            }
                            $imageSizeInfo = @ getimagesize($imagePath);
                            $width = $imageSizeInfo[0];
                            $height = $imageSizeInfo[1];
                            $format = $imageSizeInfo[2];
                            if ($width == 0 || $height == 0) {
                                // Maybe the file is the script which send image, get file by http.
                                $imagePath = Wizin_Util_Web::getFileByHttp($imageUrl);
                                if ($imagePath === '') {
                                    continue;
                                }
                                $getFileFlag = true;
                                $imageSizeInfo = @ getimagesize($imagePath);
                                $width = $imageSizeInfo[0];
                                $height = $imageSizeInfo[1];
                                $format = $imageSizeInfo[2];
                            }
                            if ($getFileFlag && $width <= $maxImageWidth) {
                                $maxImageWidth = $width;
                            }
                            if ($width !== 0 && $height !== 0) {
                                if (in_array($format, $forceResizeType)) {
                                    $urlArray = parse_url($imageUrl);
                                    $newImageFile = str_replace('/', '_', $urlArray['path']);
                                    $newImageFile = str_replace($ext, '', $newImageFile);
                                    $newImageFile .= ($maxImageWidth / $width) > 1 ?
                                        $width .'.' : $maxImageWidth .'.';
                                    $newImagePath = $createDir . '/' . $newImageFile;
                                    $newImagePath .= $newExt;
                                    $newImageUrl = str_replace($basePath, $baseUri, $newImagePath);
                                    if (! file_exists($newImagePath) ||
                                            (filemtime($newImagePath) <= filemtime($imagePath))) {
                                        Wizin_Util_Web::createThumbnail($imagePath, $width, $height,
                                            $format, $newImagePath, $width);
                                    }
                                    if (file_exists($newImagePath)) {
                                        // reset image path and image url
                                        $imagePath = $newImagePath;
                                        $imageUrl = $newImageUrl;
                                        $ext = $newExt;
                                        switch ($newExt) {
                                            case 'gif':
                                                $format = IMAGETYPE_GIF;
                                                break;
                                            default:
                                                $format = IMAGETYPE_JPEG;
                                                break;
                                        }
                                        $linkReplaceFlg = true;
                                    }
                                }
                                if ($width > $maxImageWidth && in_array($format, $allowImageFormat)) {
                                    $urlArray = parse_url($imageUrl);
                                    $newImageFile = str_replace('/', '_', $urlArray['path']);
                                    $newImageFile = str_replace($ext, '', $newImageFile);
                                    $newImageFile .= ($maxImageWidth / $width) > 1 ?
                                        $width .'.' : $maxImageWidth .'.';
                                    $newImagePath = $createDir . '/' . $newImageFile;
                                    $newImagePath .= $newExt;
                                    $newImageUrl = str_replace($basePath, $baseUri, $newImagePath);
                                    if (! file_exists($newImagePath) ||
                                            (filemtime($newImagePath) < filemtime($imagePath))) {
                                        Wizin_Util_Web::createThumbnail($imagePath, $width, $height,
                                            $format, $newImagePath, $maxImageWidth);
                                    }
                                    if (file_exists($newImagePath)) {
                                        $linkReplaceFlg = true;
                                    }
                                }
                                if ($linkReplaceFlg) {
                                    $imageTag = str_replace($match[3] . $match[4] .$match[5] . $match[6],
                                        $match[3] . $match[4] . $newImageUrl . $match[6], $match[0]);
                                    $pattern = '(width|height)=([\"\'])(\S*)([\"\'])';
                                    $replacement = '';
                                    $imageTag = preg_replace("/" .$pattern ."/i", $replacement, $imageTag);
                                    $replaceArray = array("'" => "\'", '"' => '\"', '\\' => '\\\\',
                                        '/' => '\/', '(' => '\(', ')' => '\)', '.' => '\.', '?' => '\?');
                                    $linkCheckPattern = '(<a)([^>]*)(href=)([^>]*)(>)((?:(?!<\/a>).)*)('
                                        . strtr($match[0], $replaceArray) . ')';
                                    if (preg_match("/" .$linkCheckPattern ."/is", $contents)) {
                                        $contents = str_replace($match[0], $imageTag, $contents);
                                    } else {
                                        $imageLink = '<a href="' . $imageUrl . '">' . $imageTag . '</a>';
                                        $contents = str_replace($match[0], $imageLink, $contents);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        /**
         * Delete contiguous space and blank line.
         *
         * @param string $contents
         */
        function minimize(& $contents) {
            $contents = strtr($contents, array("\t" => ' ', "\r\n" => 'PHP_EOL', "\r" => 'PHP_EOL', "\n" => 'PHP_EOL'));
            $contents = preg_replace('/\s\s+/', ' ', $contents);
            $contents = str_replace('PHP_EOL PHP_EOL', 'PHP_EOL', $contents);
            $contents = str_replace('PHP_EOL', PHP_EOL, $contents);
        }
    }
}
