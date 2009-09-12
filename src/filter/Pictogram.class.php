<?php
/**
 * Wizin framework "Text_Pictogram_Mobile" wrapper class
 *
 * PHP Versions 5.2
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.php.net/license/3_0.txt  PHP License 3.0
 *
 * As for this class, it depends on Text_Pictogram_Mobile which is distributed with the license.
 * Special thanks : yudoufu
 */

if (! class_exists('Wizin_Filter_Pictogram')) {
    if (function_exists('json_decode')) {
        if (! class_exists('Text_Pictogram_Mobile')) {
            // include Text_Pictogram_Mobile
            @ include_once 'Text/Pictogram/Mobile.php';
        }

        if (class_exists('Text_Pictogram_Mobile')) {
            /**
             * Wizin framework "Text_Pictogram_Mobile" wrapper class
             *
             * @access public
             */
            class Wizin_Filter_Pictogram extends Text_Pictogram_Mobile
            {
                public static function factory($agent = null, $type = 'sjis')
                {
                    if (isset($agent) && $agent != "") {
                        switch (strtolower($agent)) {
                            case 'docomo':
                            case 'imode':
                            case 'i-mode':
                            case 'emobile':
                            case 'willcom':
                                $agent = 'docomo';
                                break;
                            case 'ezweb':
                            case 'au':
                            case 'kddi':
                                $agent = 'ezweb';
                                break;
                            case 'disney':
                            case 'softbank':
                            case 'vodafone':
                            case 'jphone':
                            case 'j-phone':
                                $agent = 'softbank';
                                break;
                            default:
                                $agent = 'nonmobile';
                        }
                        $agent = ucfirst(strtolower($agent));
                    } else {
                        $agent = 'Nonmobile';
                    }

                    $className = "Text_Pictogram_Mobile_{$agent}";
                    if (!class_exists($className)) {
                        $file = str_replace('_', '/', $className) . '.php';
                        if (!include_once $file) {
                            throw new Text_Pictogram_Mobile_Exception('Class file not found:' . $file);
                        }
                    }

                    $instance = new $className($type);
                    return $instance;
                }

                /**
                 * return pictograms array
                 *
                 * @param object $picObject
                 * @return array
                 */
                public static function & getPictograms(& $picObject)
                {
                    static $pictograms;
                    if (! isset($pictograms)) {
                        $dataDir = Wizin_Filter_Pictogram::pictogramDataDir();
                        $suffix = Wizin_Util::cipher($dataDir);
                        $pictograms = array();
                        $picCarrier = $picObject->getCarrier();
                        if (! empty($picCarrier)) {
                            foreach (array('docomo', 'ezweb', 'softbank') as $carrier) {
                                $cache = WIZIN_CACHE_DIR . '/wizin_pictogram_' . $picCarrier .
                                    '_' . $carrier . '_'. $suffix;
                                if (Wizin_Filter_Pictogram::isCached($carrier, $cache)) {
                                    $pictograms[$carrier] = unserialize(file_get_contents($cache));
                                } else {
                                    // get pictograms array
                                    $pictograms[$carrier] = array();
                                    if (isset($picObject) && is_object($picObject)) {
                                        $pictograms[$carrier] = $picObject->getFormattedPictogramsArray($carrier);
                                    }
                                    $fp = fopen($cache, 'wb');
                                    fwrite($fp, serialize($pictograms[$carrier]));
                                    fclose($fp);
                                }
                            }
                        }
                    }
                    return $pictograms;
                }

                /**
                 * return json data array
                 *
                 * @param string $carrier
                 * @param string $jsonFile
                 * @return array
                 */
                public static function & getJsonData($carrier = 'docomo', $jsonFile)
                {
                    $jsonData = array();
                    $suffix = Wizin_Util::cipher($jsonFile);
                    $cache = WIZIN_CACHE_DIR . '/wizin_pic_json_' . $carrier . '_' . $suffix;
                    if (Wizin_Filter_Pictogram::isCached($carrier, $cache)) {
                        $jsonData = unserialize(file_get_contents($cache));
                    } else {
                        // get pictograms array
                        $json = file_get_contents($jsonFile);
                        $jsonData = json_decode($json, true);
                        $fp = fopen($cache, 'wb');
                        fwrite($fp, serialize($jsonData));
                        fclose($fp);
                    }
                    return $jsonData;
                }

                /**
                 * return convert data array
                 *
                 * @param string $carrier
                 * @param string $jsonFile
                 * @return array
                 */
                public static function & getConvertData($carrier = 'docomo', $jsonFile)
                {
                    static $jsonData;
                    if (! isset($jsonData)) {
                        $jsonData = array();
                    }
                    if (! isset($jsonData[$carrier])) {
                        $suffix = Wizin_Util::cipher($jsonFile);
                        $cache = WIZIN_CACHE_DIR . '/wizin_pic_convert_' . $carrier . '_' . $suffix;
                        if (Wizin_Filter_Pictogram::isCached($carrier, $cache)) {
                            $jsonData[$carrier] = unserialize(file_get_contents($cache));
                        } else {
                            // get pictograms array
                            $jsonData[$carrier] = array();
                            $json = file_get_contents($jsonFile);
                            $jsonData[$carrier] = json_decode($json, true);
                            $fp = fopen($cache, 'wb');
                            fwrite($fp, serialize($jsonData[$carrier]));
                            fclose($fp);
                        }
                    }
                    return $jsonData;
                }

                /**
                 * check cached data
                 *
                 * @return boolean $return
                 */
                public static function isCached($carrier, $cache)
                {
                    clearstatcache();
                    $return = true;
                    if (! file_exists($cache)) {
                        $return = false;
                    }
                    $dataDir = Wizin_Filter_Pictogram::pictogramDataDir();
                    if ($return && ($handler = opendir($dataDir))) {
                        while (($file = readdir($handler)) !== false) {
                            if ($file === '.' || $file === '..') {
                                continue;
                            }
                            if (strpos($file, $carrier) === false) {
                                continue;
                            }
                            if (substr($file, -5) === '.json') {
                                if (filemtime($dataDir . '/' . $file) > filemtime($cache)) {
                                    $return = false;
                                    break;
                                }
                            }
                        }
                        closedir($handler);
                    }
                    return $return;
                }

                /**
                 * set/return json data directory path
                 *
                 * @return string $_dataDir
                 */
                public static function pictogramDataDir()
                {
                    static $dataDir;
                    if (is_null($dataDir)) {
                        $includeFiles = get_included_files();
                        $needle = str_replace('_', DIRECTORY_SEPARATOR, 'Text_Pictogram_Mobile') . '.php';
                        foreach ($includeFiles as $file) {
                            if (strpos($file, $needle) !== false) {
                                $dataDir = dirname($file) . '/Mobile/data';
                                Wizin_Filter_Pictogram::pictogramDataDir($dataDir);
                                break;
                            }
                        }
                    }
                    return $dataDir;
                }
            }
        }
    }
}
