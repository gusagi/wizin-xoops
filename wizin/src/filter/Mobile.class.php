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
        function filterOptimizeMobile(& $contents, $baseUri, $currentUri, $basePath, $createDir = WIZIN_CACHE_DIR)
        {
            $maxWidth = 220;
            Wizin_Filter::filterResizeImage($contents, $baseUri, $currentUri, $basePath, $createDir,
                $maxWidth, array(IMAGETYPE_PNG));
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
                $pattern = '(<a)([^>]*)(href=)([\"\'])(@)(\S*)(@)([\"\'])([^>]*)(>)';
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
                $pattern = '(<a)([^>]*)(href=)([\"\'])(\S*)([\"\'])([^>]*)(>)';
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
            if (class_exists('DOMDocument') && class_exists('SimpleXMLElement') &&
                    method_exists('SimpleXMLElement','getName')) {
                // get encode
                if (extension_loaded('mbstring')) {
                    $encode = strtolower(mb_detect_encoding($string,
                        'ASCII,JIS,UTF-8,EUC-JP,SJIS', true));
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
                $string = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8"><body>' .
                    $string . '</body></html>';
                $domDoc = new DOMDocument('1.0', 'utf-8');
                @ $domDoc->loadHTML($string);
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
            // set valiable
            if (empty($maxKbyte)) {
                $maxKbyte = 1;
            }
            $maxByte = $maxKbyte * 1024;
            $buffer = '';
            $pattern = '^<\\?xml version=[\"\']1.0[\"\']';
            $noPartitionTag = array('form', 'tr', 'th', 'td', 'tbody', 'fieldset', 'pre');
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
                    if (strlen($attribute) > $maxByte && ! in_array(strtolower($nodeName), $noPartitionTag)) {
                        $array[] = $buffer;
                        $buffer = '';
                        $html = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8">' .
                            '<body>' . $attribute . '</body></html>';
                        $domDoc = new DOMDocument('1.0', 'utf-8');
                        @ $domDoc->loadHTML($html);
                        $childXml = simplexml_import_dom($domDoc);
                        unset($html);
                        unset($domDoc);
                        // add XML header
                        $string = $childXml->asXML();
                        if (preg_match('/' . $pattern . '/i', $string) !== true) {
                            $string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $string;
                        }
                        $childXml = simplexml_load_string($string);
                        $body =& $childXml->body;
                        if (serialize($body) !== serialize($body->children())) {
                            // bottom layer
                            $pages = Wizin_Filter_Mobile::_partitionPage($body, $encode, $maxKbyte);
                            foreach ($pages as $page) {
                                $array[] = $page;
                            }
                            unset($pages);
                            continue;
                        } else {
                            // not bottom layer
                            $buffer .= $attribute;
                            while (strlen($buffer) > $maxByte) {
                                if (extension_loaded('mbstring')) {
                                        $cutString = mb_substr($buffer, 0, $maxByte, 'utf-8');
                                } else {
                                        $cutString = substr($buffer, 0, $maxByte);
                                }
                                $cutStringArray = explode('<', $cutString);
                                array_pop($cutStringArray);
                                $cutString = implode('<', $cutStringArray);
                                $html = '<html><meta http-equiv="content-type" content="text/html; charset=utf-8">' .
                                    '<body>' . $cutString . '</body></html>';
                                $domDoc = new DOMDocument('1.0', 'utf-8');
                                @ $domDoc->loadHTML($html);
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
                                $array[] = $string;
                                $buffer = str_replace($cutString, '', $buffer);
                            }
                            $attribute = '';
                        }
                        unset($childXml);
                    } else if ((strlen($buffer) + strlen($attribute)) > $maxByte) {
                        $array[] = $buffer;
                        $buffer = '';
                    }
                    $buffer .= $attribute;
                }
            }
            $array[] = $buffer;
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
        function filterCssMobile(& $contents, $cssBaseDirs = '')
        {
            if (floatval(PHP_VERSION) < 5.1) {
                return $contents;
            }
            if (! empty($cssBaseDirs) && is_array($cssBaseDirs)) {
                if (! class_exists('Wizin_Filter_Css')) {
                    if (file_exists(dirname(__FILE__) . '/Css.class.php')) {
                        require dirname(__FILE__) . '/Css.class.php';
                    }
                }
                if (class_exists('Wizin_Filter_Css')) {
                    if (extension_loaded('mbstring')) {
                        $encodingSalt = mb_convert_kana(mb_internal_encoding(), 'A');
                        for($count = 0; $count < 10; $count++) {
                            $encodingSalt .= PHP_EOL . $encodingSalt;
                        }
                        $contents .= $encodingSalt;
                    }
                    // apply style
                    foreach ($cssBaseDirs as $cssDir) {
                        // directory check
                        if (! file_exists($cssDir) || ! is_dir($cssDir) || ! is_readable($cssDir)) {
                            continue;
                        }
                        $filterCss = Wizin_Filter_Css::getInstance();
                        $filterCss = $filterCss->setBaseDir($cssDir);
                        $contents = $filterCss->apply($contents);
                        unset($filterCss);
                    }
                    if (extension_loaded('mbstring')) {
                        $contents = str_replace($encodingSalt, '', $contents);
                    }
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
                    return null;
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
                        $pattern = '/(\[emj:)(' . $picObject->getCarrier() .')(\d+)()(\])/s';
                        preg_match_all($pattern, $converted[$key], $matches, PREG_SET_ORDER);
                        foreach ($matches as $match) {
                            $converted[$key] = str_replace($match[0], $match[1] . $match[3] . $carrier .
                                $match[5], $converted[$key]);
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
        function filterOutputPictogramMobile(& $contents, $pictImgDir = '/images/emoticons', $baseType = 'typecast')
        {
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
    }
}
