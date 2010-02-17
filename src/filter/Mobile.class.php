<?php
/**
 * Wizin framework mobile filter class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Filter_Mobile')) {
    require dirname(dirname(__FILE__)) . '/Wizin_Filter.php';

    /**
     * Wizin framework mobile filter class
     *
     * @access public
     *
     */
    class Wizin_Filter_Mobile extends Wizin_Filter
    {
        /**
         *
         * @return object $instance
         */
        function &getSingleton()
        {
            static $instance;
            if (! isset($instance)) {
                $instance = new Wizin_Filter_Mobile();
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
            $parent =& parent::getSingleton();
            $inputFilter = $this->_aInputFilter;
            $parentInputFilter = $parent->_aInputFilter;
            $count = count($parentInputFilter);
            for ($index = 0; $index < $count; $index ++) {
                $inputFilter[] = $parentInputFilter[$index];
            }
            $this->_aInputFilter = $inputFilter;
            parent::executeInputFilter();
            $parent->_aInputFilter = array();
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
            $parent =& parent::getSingleton();
            $outputFilter = $this->_aOutputFilter;
            $parentOutputFilter = $parent->_aOutputFilter;
            $count = count($parentOutputFilter);
            for ($index = 0; $index < $count; $index ++) {
                $outputFilter[] = $parentOutputFilter[$index];
            }
            $this->_aOutputFilter = $outputFilter;
            parent::executeOutputFilter($contents);
            $parent->_aOutputFilter = array();
        }

        /**
         * call input filter for mobile
         *
         */
        function filterInputMobile()
        {
            $method = strtolower(getenv('REQUEST_METHOD'));
            if ($method === 'get') {
                $_GET = $this->_filterInputKanaConvert($_GET);
            } else if ($method === 'post') {
                $_POST = $this->_filterInputKanaConvert($_POST);
            }
            $_REQUEST = $this->_filterInputKanaConvert($_REQUEST);
        }

        /**
         * convert kana filter for input value with mobile
         *
         * @param array $input
         * @return array $_input
         */
        function _filterInputKanaConvert($input)
        {
            if (extension_loaded('mbstring')) {
                $_input = array();
                foreach ($input as $key => $value) {
                    if (empty($value) && $value === '') {
                        $_input[$key] = '';
                    } else if (is_array($value)) {
                        $_input[$key] = $this->_filterInputKanaConvert($value);
                    } else {
                        $_input[$key] = mb_convert_kana($value, 'KV');
                    }
                }
            } else {
                $_input = $input;
            }
            return $_input;
        }

        /**
         * optimizer filter for mobile
         *
         * @param string $contents
         * @param string $baseUri
         * @param string $currentUri
         * @param string $basePath
         * @param string $createDir
         * @return string $contents
         */
        function filterOptimizeMobile(& $contents, $params = array())
        {
            extract($params);
            if (! isset($createDir)) {
                $createDir = WIZIN_CACHE_DIR;
            }
            $resizeParams = array(
                'baseUri' => $baseUri,
                'currentUri' => $currentUri,
                'basePath' => $basePath,
                'createDir' => $createDir,
                'maxWidth' => $maxWidth,
                'forceResizeType' => array(IMAGETYPE_PNG)
            );
            Wizin_Filter::filterResizeImage($contents, $resizeParams);
            // replace input type "password" => "text"
            /*
            $pattern = '(<input)([^>]*)(type=)([\"\'])(password)([\"\'])([^>]*)(>)';
            $replacement = '${1}${2}${3}${4}text${6} ${7}${8}';
            $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
            */
            $pattern = '(enctype=)([\"\'])(multipart\/form-data)([\"\'])';
            $replacement = '';
            $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
            $pattern = '(<input)([^>]*)(type=)([\"\'])(file)([\"\'])([^>]*)(>)';
            $replacement = '${1}${2}${3}${4}hidden${6} ${7}${8}';
            $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
            Wizin_Filter_Mobile::filterDeleteTags($contents, $final = true);
            Wizin_Filter_Mobile::filterInsertAnchor($contents, $baseUri, $currentUri);
            // convert from zenkaku to hankaku
            if (extension_loaded('mbstring')) {
                $contents = mb_convert_kana($contents, 'knr');
            }
            // delete contiguous space
            $this->minimize($contents);
            // add mobile link discovery tag
            $pattern = '(<link)([^>]*)(media=)([\"\'])(handheld)([\"\'])([^>]*)(>)';
            if (! preg_match("/" .$pattern ."/i", $contents)) {
                $mobileLinkDiscovery = '<link rel="alternate" media="handheld" type="text/html"' .
                    ' href="' .str_replace('&', '&amp;',$currentUri) .'" />';
                $contents = str_replace('</head>', $mobileLinkDiscovery .'</head>', $contents);
            }
            // return contents string
            return $contents;
        }

        /**
         * delete some tags filter
         *
         * @param string $contents
         */
        function filterDeleteTags(& $contents, $final = false)
        {
            static $callFlag;
            if (! isset($callFlag)) {
                $callFlag = true;
                // delete script tags
                $pattern = '@<script[^>]*?>.*?<\/script>@si';
                $replacement = '';
                $contents = preg_replace($pattern, $replacement, $contents);
                // delete del tags
                $pattern = '@<del[^>]*?>.*?<\/del>@si';
                $replacement = '';
                $contents = preg_replace($pattern, $replacement, $contents);
                // delete comment
                $pattern = '<!--[\s\S]*?-->';
                $replacement = '';
                $contents = preg_replace("/" .$pattern ."/", $replacement, $contents);
                // delete "nobr" tag
                $pattern = '<\/?nobr>';
                $replacement = '';
                $contents = preg_replace("/" .$pattern ."/", $replacement, $contents);
            }
            if ($final) {
                // delete first/last '@' string in link
                $pattern = '(<a)([^>]*)(href=)([\"\'])(@)([^\"\']*)(@)([\"\'])([^>]*)(>)';
                preg_match_all("/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER);
                if (! empty($matches)) {
                    foreach ($matches as $key => $match) {
                        $contents = str_replace(
                            $match[3] . $match[4] .$match[5] . $match[6] . $match[7] . $match[8] ,
                            $match[3] . $match[4] . $match[6] . $match[8], $contents);
                    }
                }
            }
        }

        /**
         * insert anchor filter
         *
         * @param string $contents
         * @param string $baseUri
         * @param string $currentUri
         */
        function filterInsertAnchor(& $contents, $baseUri, $currentUri)
        {
            static $callFlag;
            if (! isset($callFlag)) {
                $callFlag = true;
                $pattern = '(<a)([^>]*)(href=)([\"\'])([^\"\']*)([\"\'])([^>]*)(>)';
                preg_match_all("/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER);
                if (! empty($matches)) {
                    foreach ($matches as $key => $match) {
                        $href = '';
                        $hrefArray = array();
                        $url = $match[5];
                        if (substr($url, 0, 4) !== 'http') {
                            if (strpos($url, ':') !== false) {
                                continue;
                            } else if (substr($url, 0, 1) === '#') {
                                continue;
                                /*
                                $urlArray = explode('#', $currentUri);
                                $url = $urlArray[0] . $url;
                                */
                            } else if (substr($url, 0, 1) === '/') {
                                $parseUrl = parse_url($baseUri);
                                if (! empty($parseUrl['path'])) {
                                    $url = str_replace($parseUrl['path'], '', $baseUri) . $url;
                                } else {
                                    $url = $baseUri . $url;
                                }
                            } else {
                                $url = dirname($currentUri) . '/' . $url;
                            }
                        }
                        if (strstr($url, '#') === false) {
                            continue;
                        }
                        $urlArray = explode('#', $url);
                        $parseUrl = parse_url($url);
                        if (! empty($parseUrl['query'])) {
                            $url = $urlArray[0] . '&amp;wiz_anchor=' . $parseUrl['fragment'] . '#' . $urlArray[1];
                        } else {
                            $url = $urlArray[0] . '?wiz_anchor=' . $parseUrl['fragment'] . '#' . $urlArray[1];
                        }
                        $contents = str_replace($match[3] . $match[4] .$match[5] . $match[6],
                            $match[3] . $match[4] . $url . $match[6], $contents);
                    }
                }
            }
        }

        /**
         * mobile pager filter
         *
         * @param string $string
         * @param integer $maxKbyte
         * @return string $string
         */
        function filterMobilePager($string, $maxKbyte = 0)
        {
            Wizin_Filter_Mobile::filterDeleteTags($string);
            // delete contiguous space
            Wizin_Filter_Mobile::minimize($string);
            if (class_exists('DOMDocument') && class_exists('SimpleXMLElement') &&
                    method_exists('SimpleXMLElement','getName')) {
                // get encode
                if (extension_loaded('mbstring')) {
                    if (function_exists('detect_encoding_ja')) {
                        $encode = detect_encoding_ja($string);
                    } else {
                        $encode = strtolower(mb_detect_encoding($string,
                            'ASCII,JIS,UTF-8,EUC-JP,SJIS', true));
                    }
                    $encode = strtolower($encode);
                    switch ($encode) {
                        case 'euc-jp':
                            $encode = 'eucjp-win';
                            break;
                        case 'sjis':
                            $encode = 'sjis-win';
                            break;
                    }
                    $string = mb_convert_encoding($string, 'utf-8', $encode);
                } else {
                    $encode = 'ascii';
                }
                // replace for DOMDocument convert
                $string = strtr($string, array('&' => '&amp;', "\r\n" => PHP_EOL, "\r" => PHP_EOL, "\n" => PHP_EOL,
                    '</textarea>' => Wizin_Util::cipher(__FILE__) . '</textarea>',
                    '</TEXTAREA>' => Wizin_Util::cipher(__FILE__) . '</TEXTAREA>'));
                // convert XML(step1)
                $string = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8">' .
                    '<body><div>' .$string . '</div></body></html>';
                $domDoc = new DOMDocument('1.0', 'utf-8');
                @ $domDoc->loadHTML($string);
                $domDoc->normalizeDocument();
                $xml = simplexml_import_dom($domDoc);
                // add XML header
                $string = $xml->asXML();
                $pattern = '^<\\?xml version=[\"\']1.0[\"\']';
                if (preg_match('/' . $pattern . '/i', $string) !== true) {
                    $string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $string;
                }
                $xml = simplexml_load_string($string);
                $body = $xml->body;
                // partition string
                $pageArray = array();
                $array = array();
                $pageArray = Wizin_Filter_Mobile::_partitionPage($body, $encode, $maxKbyte);
                foreach ($pageArray as $key => $value) {
                    if (isset($value) && trim($value) !== '') {
                        $array[] = $value;
                    }
                }
                unset($pageArray);
                unset($domDoc);
                unset($xml);
                // set return value
                $count = count($array) - 1;
                if (! isset($array[$count]) || $array[$count] === '') {
                    array_pop($array);
                }
                array_unshift($array, '');
                $string = '';
                $index = (! empty($_GET['wiz_page'])) ? intval($_GET['wiz_page']) : 1;
                if (count($array) >= $index) {
                    $page = '';
                    if (empty($_GET['wiz_page']) && ! empty($_GET['wiz_anchor'])) {
                        $pattern = '(<a)([^>]*)(name|id)=([\"\'])' . $_GET['wiz_anchor'] . '([\"\'])';
                        $pages = preg_grep("/" . $pattern . "/is", $array);
                        if (! empty($pages)) {
                            $page = array_shift($pages);
                            $_GET['wiz_page'] = array_search($page, $array);
                            $_REQUEST['wiz_page'] =& $_GET['wiz_page'];
                            $page = strtr($page, array('&amp;' => '&',
                                Wizin_Util::cipher(__FILE__) . '</textarea>' => '</textarea>',
                                Wizin_Util::cipher(__FILE__) . '</TEXTAREA>' => '</TEXTAREA>'));
                        }
                    }
                    if (empty($page)) {
                        $page = strtr($array[$index], array('&amp;' => '&',
                            Wizin_Util::cipher(__FILE__) . '</textarea>' => '</textarea>',
                            Wizin_Util::cipher(__FILE__) . '</TEXTAREA>' => '</TEXTAREA>'));
                    }
                    // PEAR_Pager
                    $includePath = get_include_path();
                    set_include_path($includePath . PATH_SEPARATOR . WIZIN_PEAR_DIR);
                    require_once 'Pager/Pager.php';
                    $params = array(
                        'mode' => 'sliding',
                        'delta' => 3,
                        'perPage' => 1,
                        'prevImg' => '&laquo; Prev',
                        'nextImg' => 'Next &raquo;',
                        'urlVar' => 'wiz_page',
                        'spacesBeforeSeparator' => 1,
                        'spacesAfterSeparator' => 1,
                        'totalItems' => count($array) - 1
                       );
                    $pager =& Pager::factory($params);
                    $pageNavi = $pager->links;
                    $string .= $pageNavi . '<br />';
                    $string .= $page . '<br />';
                    $string .= $pageNavi;
                    set_include_path($includePath);
                }
                if (extension_loaded('mbstring')) {
                    $string = mb_convert_encoding($string, $encode, 'utf-8');
                }
            }
            return $string;
        }

        /**
         * partition page function(called by Wizin_Filter_Mobile::filterMobilePager)
         *
         * @access  private
         * @param object $xml (SimpleXMLElement)
         * @param string $encode
         * @param integer $maxKbyte
         * @return array $array
         */
        function _partitionPage(& $xml, $encode = 'ascii', $maxKbyte = 0 )
        {
            static $depth;
            if (isset($depth) === false) {
                $depth = 0;
            }
            // set valiable
            if (empty($maxKbyte)) {
                $maxKbyte = 1;
            }
            $maxByte = $maxKbyte * 1024;
            $admissibleByte = 256;
            $buffer = '';
            $array = array();
            $pattern = '^<\\?xml version=[\"\']1.0[\"\']';
            $noPartitionTag = array('form', 'tr', 'th', 'td', 'tbody', 'fieldset', 'pre', 'li');
            // get html from SimpleXMLElement
            $allAttribute = $xml->asXML();
            $allAttribute = strtr($allAttribute, array('<body>' => '', '</body>' => ''));
            if (strlen($allAttribute) <= $maxByte) {
                $array[] = $allAttribute;
            } else {
                $children =& $xml->children();
                foreach ($children as $child) {
                    $nodeName = $child->getName();
                    $attribute = $child->asXML();
                    $attribute = strtr($attribute, array('<body>' => '', '</body>' => ''));
                    if ($nodeName === 'tr') {
                        $attribute = '<table>' .$attribute .'</table>';
                    }
                    if (strlen($attribute) > $maxByte && ! in_array(strtolower($nodeName), $noPartitionTag)) {
                        if (strlen($buffer) > 0) {
                            if (empty($array) === false &&
                                strlen($array[count($array) -1]) + strlen($buffer) < $maxByte + $admissibleByte) {
                                $array[count($array) -1] .= $buffer;
                            } else {
                                $array[] = $buffer;
                            }
                        }
                        $buffer = '';
                        $html = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8">' .
                            '<body>' . $attribute . '</body></html>';
                        $domDoc = new DOMDocument('1.0', 'utf-8');
                        @ $domDoc->loadHTML($html);
                        $domDoc->normalizeDocument();
                        $childXml = simplexml_import_dom($domDoc);
                        unset($html);
                        unset($domDoc);
                        // add XML header
                        $string = $childXml->asXML();
                        if (preg_match('/' . $pattern . '/i', $string) !== true) {
                            $string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $string;
                        }
                        $childXml = simplexml_load_string($string);
                        $childDomElm = dom_import_simplexml($childXml->body);
                        if ($childDomElm->hasChildNodes() === true) {
                            // not bottom layer
                            foreach ($childDomElm->childNodes as $grandchildNode) {
                                $grandchildXml = simplexml_import_dom($grandchildNode);
                                $depth ++;
                                $pages = Wizin_Filter_Mobile::_partitionPage(
                                    $grandchildXml,
                                    $encode,
                                    $maxKbyte
                                );
                                $depth --;
                                foreach ($pages as $page) {
                                    if (empty($array) === false &&
                                        strlen($array[count($array) -1]) + strlen($page) < $maxByte + $admissibleByte) {
                                        $array[count($array) -1] .= $page;
                                    } else {
                                        $array[] = $page;
                                    }
                                }
                                unset($pages);
                                unset($grandchildXml);
                            }
                            unset($childXml);
                            unset($childDomElm);
                            continue;
                        } else {
                            // bottom layer
                            $buffer .= $attribute;
                            while (strlen($buffer) > $maxByte) {
                                if (extension_loaded('mbstring')) {
                                    $cutString = mb_strcut($buffer, 0, $maxByte, 'utf-8');
                                } else {
                                    $cutString = substr($buffer, 0, $maxByte);
                                }
                                if (stripos($cutString, '<form') !== false) {
                                    if (stripos($cutString, '</form>') !== false) {
                                        // form close tag exists in $cutString
                                        $cutStringArray = preg_split('/<\/form>/i', $cutString);
                                        array_pop($cutStringArray);
                                        $cutString = implode('</form>', $cutStringArray);
                                    } else {
                                        // form close tag not exists in $cutString
                                        $cutString = array_shift(preg_split('/<\/form>/i', $buffer)) .'</form>';
                                    }
                                } else {
                                    $cutStringArray = explode('<', $cutString);
                                    array_pop($cutStringArray);
                                    $cutString = implode('<', $cutStringArray);
                                }
                                $html = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8">' .
                                    '<body>' . $cutString . '</body></html>';
                                $domDoc = new DOMDocument('1.0', 'utf-8');
                                @ $domDoc->loadHTML($html);
                                $domDoc->normalizeDocument();
                                $cutXml = simplexml_import_dom($domDoc);
                                unset($domDoc);
                                // add XML header
                                $string = $cutXml->asXML();
                                if (preg_match('/' . $pattern . '/i', $string) !== true) {
                                    $string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $string;
                                }
                                $cutXml = simplexml_load_string($string);
                                $string = $cutXml->body->asXML();
                                $string = strtr($string, array('<body>' => '', '</body>' => ''));
                                // If length for the total of the last element of $array and $string to exceed $maxByte is less than $admissibleByte,
                                // the one that $string was added to last element of $array is considered to be 1 page.
                                if (empty($array) === false &&
                                    strlen($array[count($array) -1]) + strlen($string) < $maxByte + $admissibleByte) {
                                    $array[count($array) -1] .= $string;
                                } else {
                                    $array[] = $string;
                                }
                                $buffer = str_replace($cutString, '', $buffer);
                            }
                            $attribute = '';
                        }
                        unset($childXml);
                        unset($childDomElm);
                    } else if ((strlen($buffer) + strlen($attribute)) > $maxByte) {
                        // If length for the total of $buffer and $attribute to exceed $maxByte is less than $admissibleByte,
                        // the one that $attribute was added to $buffer is considered to be 1 page.
                        if ((strlen($buffer) + strlen($attribute)) < $maxByte + $admissibleByte) {
                            $buffer .= $attribute;
                            $attribute = '';
                        }
                        $array[] = $buffer;
                        $buffer = '';
                    }
                    $buffer .= $attribute;
                }
            }
            $bufferArray = explode('<', $buffer);
            array_walk($bufferArray, 'trim');
            for ($index = 0; $index < count($bufferArray); $index++) {
                $breakFlg = true;
                if ($bufferArray[$index] === '') {
                    continue;
                }
                if (substr($bufferArray[$index], 0, 1) === '/') {
                    $breakFlg = false;
                }
                if (strtolower(substr($bufferArray[$index], 0, 2)) === 'br') {
                    $breakFlg = false;
                }
                if ($breakFlg === true) {
                    break;
                }
                unset($bufferArray[$index]);
            }
            $buffer = implode('<', $bufferArray);
            if (trim($buffer) !== '') {
                // If length for the total of the last element of $array and $buffer to exceed $maxByte is less than $admissibleByte,
                // the one that $buffer was added to last element of $array is considered to be 1 page.
                $html = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8">' .
                    '<body>' . $buffer . '</body></html>';
                $domDoc = new DOMDocument('1.0', 'utf-8');
                @ $domDoc->loadHTML($html);
                $domDoc->normalizeDocument();
                $bufXml = simplexml_import_dom($domDoc);
                // add XML header
                $buffer = $bufXml->body->asXML();
                $buffer = strtr($buffer, array('<body>' => '', '</body>' => ''));
                unset($domDoc);
                unset($bufXml);
                if (empty($array) === false &&
                    strlen($array[count($array) -1]) + strlen($buffer) < $maxByte + $admissibleByte) {
                    $array[count($array) -1] .= $buffer;
                } else {
                    $array[] = $buffer;
                }
            }
            return $array;
        }

        /**
         * The filter which apply style of CSS by inline style
         *
         *   As for this function, it depends on HTML_CSS_Mobile which is distributed with the license.
         *   Special thanks : yudoufu
         *   http://coderepos.org/share/browser/lang/php/HTML_CSS_Mobile
         *
         * @param string $contents
         * @param array $cssBaseDirs
         * @return string $contents
         */
        function filterCssMobile(& $contents, $cssBaseDirs = '', $baseUrl = '')
        {
            if (floatval(PHP_VERSION) < 5.1) {
                return $contents;
            }
            if (! extension_loaded('mbstring')) {
                return $contents;
            }
            if (! empty($cssBaseDirs) ) {
                if (! is_array($cssBaseDirs)) {
                    $cssBaseDirs = (array)$cssBaseDirs;
                }
                if (! class_exists('Wizin_Filter_Css')) {
                    if (file_exists(dirname(__FILE__) . '/Css.class.php')) {
                        require dirname(__FILE__) . '/Css.class.php';
                    }
                }
                if (class_exists('Wizin_Filter_Css')) {
                    /**
                     * delete base url in link tag
                     */
                    // forward rel
                    $pattern = '(<link)([^>]*)(rel=)([\"\'])(stylesheet)([\"\'])([^>]*)(href=)([\"\'])(' .
                        strtr($baseUrl, array('/' => '\/')) .')([^\"\']*)([\"\'])([^>]*)(>)';
                    $replacement = '${1}${2}${3}${4}${5}${6}${7}${8}${9}${11}${12} ${13}${14}';
                    $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    // forward href
                    $pattern = '(<link)([^>]*)(href=)([\"\'])(' .strtr($baseUrl, array('/' => '\/')) .
                        ')([^\"\']*)([\"\'])([^>]*)(rel=)([\"\'])(stylesheet)([\"\'])([^>]*)(>)';
                    $replacement = '${1}${2}${3}${4}${6}${7} ${8}${9}${10}${11}${12}${13}${14}';
                    $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    /**
                     * delete empty link tag
                     */
                    // forward rel
                    $pattern = '(<link)([^>]*)(rel=)([\"\'])(stylesheet)([\"\'])([^>]*)(href=)' .
                        '([\"\'])([\"\'])([^>]*)(>)';
                    $replacement = '';
                    $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    // forward href
                    $pattern = '(<link)([^>]*)(href=)([\"\'])([\"\'])([^>]*)(rel=)([\"\'])' .
                        '(stylesheet)([\"\'])([^>]*)(>)';
                    $replacement = '${1}${2}${3}${4}${6}${7} ${8}${9}${10}${11}${12}${13}${14}';
                    $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    /**
                     * convert encoding to utf-8
                     */
                    $internalEncoding = mb_internal_encoding();
                    if (in_array(strtolower($internalEncoding), array('sjis', 'shift_jis', 'ms_kanji',
                            'csshift_jis'))) {
                        $internalEncoding = 'sjis-win';
                    } else if (in_array(strtolower($internalEncoding), array('euc-jp',
                            'extended_unix_code_packed_format_for_japanese', 'cseucpkdfmtjapanese'))) {
                        $internalEncoding = 'eucjp-win';
                    }
                    /**
                     * add keyword for encoding detect
                     */
                    $_encodingSalt = mb_convert_kana($internalEncoding, 'A');
                    $encodingSalt = str_repeat($_encodingSalt .PHP_EOL, 10);
                    unset($_encodingSalt);
                    $contents .= $encodingSalt;
                    /**
                     * replace declaration
                     */
                    $dummyHead = '<?xml version="1.0" encoding="utf-8" ?>' ."\n" .
                        '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" ' .
                        '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' ."\n";
                    $declaration = '';
                    if (preg_match('/^(.*?)(<head)/is', $contents, $matches)) {
                        $declaration = $matches[1];
                        $contents = str_replace($declaration, $dummyHead, $contents);
                    }
                    $contents = mb_convert_kana($contents, 'K');
                    // exchange meta header
                    $pattern = '(<meta)([^>]*)(http-equiv=)([^>]*)(charset=)(\S*)([\"\'])([^>]*)(>)';
                    preg_match("/" .$pattern ."/is", $contents, $match);
                    $exchangeHeader = '';
                    if (! empty($match) && ! empty($match[0])) {
                        $exchangeHeader = $match[0];
                        $replacement = '<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />';
                        $contents = preg_replace("/" .$pattern ."/i", $replacement, $contents);
                    }
                    $contents = mb_convert_encoding($contents, 'utf-8', $internalEncoding);
                    /**
                     * apply style
                     */
                    $errorLevel = error_reporting();
                    foreach ($cssBaseDirs as $cssDir) {
                        // directory check
                        if (! file_exists($cssDir) || ! is_dir($cssDir) || ! is_readable($cssDir)) {
                            continue;
                        }
                        $filterCss = Wizin_Filter_Css::getInstance();
                        $filterCss = $filterCss->setBaseDir($cssDir);
                        error_reporting(E_ERROR);
                        $contents = $filterCss->apply($contents);
                        error_reporting($errorLevel);
                        unset($filterCss);
                    }
                    /**
                     * convert encoding to internal encoding
                     */
                    $contents = mb_convert_encoding($contents, $internalEncoding, 'utf-8');
                    $contents = mb_convert_kana($contents, 'k');
                    /**
                     * delete keyword and environ tags
                     */
                    $contents = array_shift(preg_split('/<\/html>/i', $contents)) .'</html>';
                }
                /**
                 * revert declaration
                 */
                if ($declaration !== '') {
                    if (preg_match('/^(.*?)(<head)/is', $contents, $matches)) {
                        $contents = str_replace($matches[1], $declaration, $contents);
                    }
                }
                if ($exchangeHeader !== '') {
                    $replacement = '<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />';
                    $contents = str_replace($replacement, $exchangeHeader, $contents);
                }
            }
            return $contents;
        }


        /**
         * return Text_Pictogram_Mobile object
         *
         *   As for this function, it depends on HTML_CSS_Mobile which is distributed with the license.
         *   Special thanks : yudoufu
         *
         */
        function & _getPicObject()
        {
            static $picObject;
            if (! isset($picObject)) {
                // check client by user-agent with Wizin_User
                if (! class_exists('Wizin_User')) {
                    require WIZIN_ROOT_PATH . '/src/Wizin_User.class.php';
                }
                $user = & Wizin_User::getSingleton();
                $user->checkClient();
                if (intval(PHP_VERSION) < 5) {
                    $picObject = null;
                    return $picObject;
                }
                if (! class_exists('Wizin_Filter_Pictogram')) {
                    if (file_exists(dirname(__FILE__) . '/Pictogram.class.php')) {
                        require dirname(__FILE__) . '/Pictogram.class.php';
                    }
                }
                if (is_callable(array('Wizin_Filter_Pictogram', 'factory'))) {
                    $encoding = $user->sEncoding;
                    if ($encoding === 'sjis-win') {
                        $encoding = 'sjis';
                    }
                    // get Wizin_Filter_Pictogram object
                    $picObject = Wizin_Filter_Pictogram::factory($user->sCarrier, $encoding);
                    $picObject->setIntercodePrefix('[emj:');
                    $picObject->setIntercodeSuffix(']');
                }
            }
            return $picObject;
        }


        /**
         * return pictograms array
         *
         *   As for this function, it depends on HTML_CSS_Mobile which is distributed with the license.
         *   Special thanks : yudoufu
         *
         */
        function & _getPictograms()
        {
            static $pictograms;
            if (! isset($pictograms)) {
                $pictograms = array();
                $picObject = $this->_getPicObject();
                if (isset($picObject) && is_object($picObject)) {
                    $pictograms = & Wizin_Filter_Pictogram::getPictograms($picObject);
                }
            }
            return $pictograms;
        }

        /**
         * The filter which converts 'Emoji' with japanese mobile to text.
         *
         *   As for this function, it depends on HTML_CSS_Mobile which is distributed with the license.
         *   Special thanks : yudoufu
         *
         */
        function filterInputPictogramMobile()
        {
            $method = strtolower(getenv('REQUEST_METHOD'));
            if ($method === 'get') {
                $_GET = $this->_convertInputPictogram($_GET);
            } else if ($method === 'post') {
                $_POST = $this->_convertInputPictogram($_POST);
            }
            $_REQUEST = $this->_convertInputPictogram($_REQUEST);
        }

        function _convertInputPictogram($input)
        {
            $converted = array();
            $picObject = & $this->_getPicObject();
            foreach ($input as $key => $value) {
                if (empty($value) && $value === '') {
                    $converted[$key] = '';
                } else if (is_array($value)) {
                    $converted[$key] = $this->_convertInputPictogram($value);
                } else {
                    if (isset($picObject) && is_object($picObject)) {
                        $converted[$key] = $picObject->convert($value);
                        $converted[$key] = $picObject->unescapeString($converted[$key]);
                        switch ($picObject->getCarrier()) {
                            case 'ezweb':
                                $carrier = ':ez';
                                break;
                            case 'softbank':
                                $carrier = ':sb';
                                break;
                            case 'docomo':
                            default:
                                $carrier = ':im';
                                break;
                        }
                        $pattern = '/(\[emj:)( )?(' . $picObject->getCarrier() .')( )?(\d+)( )?(\])/s';
                        preg_match_all($pattern, $converted[$key], $matches, PREG_SET_ORDER);
                        foreach ($matches as $match) {
                            $converted[$key] = str_replace($match[0], $match[1] . $match[5] . $carrier .
                                $match[7], $converted[$key]);
                        }
                    } else {
                        $converted[$key] = $value;
                    }
                }
            }
            return $converted;
        }

        /**
         * The filter which converts text to 'Emoji' with japanese mobile.
         *
         *   As for this function, it depends on HTML_CSS_Mobile which is distributed with the license.
         *   Special thanks : yudoufu
         *   http://coderepos.org/share/browser/lang/php/HTML_CSS_Mobile
         *
         * @param string $contents
         * @return string $contents
         */
        function filterOutputPictogramMobile(& $contents, $params = array())
        {
            /**
             * set variables
             */
            extract($params);
            if (! isset($pictImgDir)) {
                $pictImgDir = '/images/emoticons';
            }
            if (! isset($baseType)) {
                $baseType = 'typecast';
            }
            if (! class_exists('Wizin_User')) {
                require WIZIN_ROOT_PATH . '/src/Wizin_User.class.php';
            }
            $user = & Wizin_User::getSingleton();
            $user->checkClient();
            $pictograms = & $this->_getPictograms();
            if (class_exists('Wizin_Filter_Pictogram')) {
                $dataDir = Wizin_Filter_Pictogram::pictogramDataDir();
                if (! $user->bIsMobile) {
                    // check pictogram pattern in value="***"
                    $pattern = "/" . '(value=)([\'\"])?([^>]*)(\[emj:)(\d+)(:im|:ez|:sb)?(\])([^>]*)([\'\"])?' . "/is";
                    preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);
                    if (! empty($matches)) {
                        foreach ($matches as $key => $match) {
                            $string = strtr($match[0], array('[emj' => '&#091;emj', ']' => '&#093;'));
                            $contents = str_replace($match[0], $string, $contents);
                            unset($match);
                        }
                    }
                    unset($matches);
                    // check pictogram pattern in textarea
                    $pattern = "/" . '(<textarea)([^>]*)(>)([^<]*)?(\[emj:)(\d+)(:im|:ez|:sb)?(\])([^<]*)(<\/textarea>)?' . "/is";
                    preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);
                    if (! empty($matches)) {
                        foreach ($matches as $key => $match) {
                            $string = strtr($match[0], array('[emj' => '&#091;emj', ']' => '&#093;'));
                            $contents = str_replace($match[0], $string, $contents);
                            unset($match);
                        }
                    }
                    unset($matches);
                    // check pictogram pattern
                    $pattern = "/" . '(\[emj:)(\d+)(:im|:ez|:sb)?(\])' . "/";
                    $idIndex = 2;
                    $carrierIndex = 3;
                } else {
                    // check pictogram pattern
                    $pattern = "/" . '(\[emj:)(\d+)(:im|:ez|:sb)?(\])' . "/";
                    $idIndex = 2;
                    $carrierIndex = 3;
                }
                preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);
                if (! empty($matches)) {
                    $jsonData = array();
                    foreach ($matches as $key => $match) {
                        $id = intval($match[$idIndex]);
                        switch ($match[$carrierIndex]) {
                            case ':ez':
                                $carrier = 'ezweb';
                                break;
                            case ':sb':
                                $carrier = 'softbank';
                                break;
                            case ':im':
                            default:
                                $carrier = 'docomo';
                                break;
                        }
                        $pictogram = '';
                        if (isset($pictograms) && ! empty($pictograms[$carrier][$id]) && $user->bIsMobile) {
                            $pictogram = $pictograms[$carrier][$id];
                        } else {
                            if (! isset($jsonNonmobileData)) {
                                $jsonFile = WIZIN_ROOT_PATH . '/data/pictogram/nonmobile.json';
                                $jsonNonmobileData = Wizin_Filter_Pictogram::getJsonData('nonmobile', $jsonFile);
                            }
                            $jsonFile = $dataDir . DIRECTORY_SEPARATOR . $carrier . '_convert.json';
                            if (file_exists($jsonFile) && function_exists('json_decode') ) {
                                if (! isset($jsonData[$carrier])) {
                                    $jsonData[$carrier] =& Wizin_Filter_Pictogram::getJsonData($carrier, $jsonFile);
                                }
                                $pictogram = $match[0];
                                if (isset($jsonData[$carrier][$carrier][$id]['docomo'])) {
                                    $key = $jsonData[$carrier][$carrier][$id]['docomo'];
                                    if (is_numeric($key)) {
                                        $pictogram = '<img src="' . $pictImgDir . '/' .
                                            $jsonNonmobileData[$baseType][$key]['image'] . '" alt="' .
                                            basename($jsonNonmobileData[$baseType][$key]['image'], '.gif') . '" />';
                                    } else {
                                        $pictogram = mb_convert_encoding($key, mb_internal_encoding(), 'utf-8');
                                    }
                                }
                            }
                        }
                        $contents = str_replace($match[0], $pictogram, $contents);
                        unset($match);
                    }
                    unset($jsonNonmobileData);
                    unset($jsonData);
                    unset($matches);
                }
            }
            return $contents;
        }

        /**
         * replace link filter
         *
         * @param string $contents
         * @param string $baseUri
         * @param string $currentUri
         * @return string $contents
         */
        function filterReplaceLinks(& $contents, $params = array())
        {
            /**
             * extract params
             */
            extract($params);
            if (! isset($extKey)) {
                $extKey = 'ext';
            }
            if (! isset($backKey)) {
                $backKey = 'back';
            }

            /**
             * include crypt class
             */
            if (! class_exists('Wizin_Crypt')) {
                require WIZIN_ROOT_PATH . '/src/Wizin_Crypt.class.php';
            }
            if (isset($extlinkKey) && isset($extConfirmUrl)) {
                $blowfish =& Wizin_Crypt::getBlowfish($extlinkKey);
            }

            /**
             * replace links and forms
             */
            // link
            $pattern = '(<a)([^>]*)(href=)([\"\'])([^\"\']*)([\"\'])([^>]*)(>)';
            preg_match_all("/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER);
            if (! empty($matches)) {
                foreach ($matches as $key => $match) {
                    $href = '';
                    $hrefArray = array();
                    $url = $match[5];
                    if (substr($url, 0, 4) !== 'http') {
                        if (strpos($url, ':') !== false) {
                            continue;
                        } else if (substr($url, 0, 1) === '#') {
                            continue;
                        } else if (substr($url, 0, 1) === '/') {
                            $parseUrl = parse_url($baseUri);
                            $path = '';
                            if (isset($parseUrl['path'])) {
                                $path = $parseUrl['path'];
                            }
                            $url = str_replace($path, '', $baseUri) . $url;
                        } else {
                            if (substr($currentUri, -1, 1) === '/') {
                                $url = $currentUri .$url;
                            } else {
                                $url = dirname($currentUri) .'/' .$url;
                            }
                        }
                    }
                    $check = strstr($url, $baseUri);
                    if ($check !== false) {
                        /**
                         * internal links
                         */
                        if (strpos($url, session_name()) === false) {
                            if (strpos($url, '?') === false) {
                                $connector = '?';
                            } else {
                                $connector = '&amp;';
                            }
                            if (strpos($url, '#') !== false) {
                                $hrefArray = explode('#', $url);
                                $href .= $hrefArray[0] . $connector . SID;
                                if (! empty($hrefArray[1])) {
                                    $href .= '#' . $hrefArray[1];
                                }
                            } else {
                                $href = $url .$connector .SID;
                            }
                            $contents = str_replace(
                                $match[1] . $match[2] .$match[3] . $match[4] .$match[5] . $match[6],
                                $match[1] . $match[2] .$match[3] . $match[4] . $href . $match[6],
                                $contents
                            );
                        }
                    } else {
                        /**
                         * external links
                         */
                        if (isset($blowfish)) {
                            if (strpos($extConfirmUrl, '?') === false) {
                                $connector = '?';
                            } else {
                                $connector = '&amp;';
                            }
                            $href = $extConfirmUrl .$connector .
                                $extKey .'=' .
                                rawurlencode(base64_encode($blowfish->encrypt($url))) .'&amp;' .
                                $backKey .'=' .
                                rawurlencode(base64_encode($blowfish->encrypt($currentUri)));
                            $contents = str_replace($match[3] . $match[4] .$match[5] . $match[6],
                                $match[3] . $match[4] . $href . $match[6], $contents);
                        }
                    }
                }
            }
            //
            // form
            //
            $pattern = '(<form)([^>]*)(action=)([\"\'])([^\"\']*)([\"\'])([^>]*)(>)';
            preg_match_all("/" .$pattern ."/i", $contents, $matches, PREG_SET_ORDER);
            if (! empty($matches)) {
                foreach ($matches as $key => $match) {
                    if (! empty($match[5])) {
                        $form = $match[0];
                        $action = $match[5];
                        if (substr($action, 0, 4) !== 'http') {
                            if (strpos($action, ':') !== false) {
                                continue;
                            } else if (substr($action, 0, 1) === '#') {
                                $urlArray = explode('#', $currentUri);
                                $action = $urlArray[0] . $action;
                            } else if (substr($action, 0, 1) === '/') {
                                $parseUrl = parse_url($baseUri);
                                $path = '';
                                if (isset($parseUrl['path'])) {
                                    $path = $parseUrl['path'];
                                }
                                $action = str_replace($path, '', $baseUri) . $action;
                            } else {
                                if (substr($currentUri, -1, 1) === '/') {
                                    $action = $currentUri .$action;
                                } else {
                                    $action = dirname($currentUri) .'/' .$action;
                                }
                            }
                        }
                    } else {
                        if (substr($currentUri, -1, 1) === '/') {
                            $url = $currentUri;
                        } else {
                            $url = dirname($currentUri) .'/';
                        }
                        $url .= basename(getenv('SCRIPT_NAME'));
                        $queryString = getenv('QUERY_STRING');
                        if (isset($queryString) && $queryString !== '') {
                            $queryString = str_replace('&' . SID, '', $queryString);
                            $queryString = str_replace(SID, '', $queryString);
                            if ($queryString !== '') {
                                $url .= '?' . $queryString;
                            }
                        }
                        $form = str_replace($match[3] . $match[4] . $match[5] . $match[6],
                            $match[3] . $match[4] . $url . $match[6], $match[0]);
                        $action = $url;
                    }
                    $check = strstr($action, $baseUri);
                    if ($check !== false) {
                        $tag = '<input type="hidden" name="' . session_name() . '" value="' . session_id() . '" />';
                        $contents = str_replace($match[0], $form . $tag, $contents);
                    }
                    $action = '';
                }
            }
            // delete needless strings
            $contents = str_replace('?&', '?', $contents);
            $contents = str_replace('&&', '&', $contents);
            return $contents;
        }

    }
}
