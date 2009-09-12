<?php
/**
 * Wizin framework function emulate script
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008-2009 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! defined('WIZIN_LOAD_FUNCTIONS')) {
    define('WIZIN_LOAD_FUNCTIONS', 1);

    /**
     * 'json_encode'
     */
    if (! function_exists('json_encode')) {
        if (file_exists(WIZIN_ROOT_PATH . '/lib/PEAR/Jsphon/Encoder.php')) {
            require_once WIZIN_ROOT_PATH . '/lib/PEAR/Jsphon/Encoder.php';
            function json_encode($values) {
                $encoder = new Jsphon_Encoder(true, true);
                $json = $encoder->encode($values);
                return $json;
            }
        }
    }

    if (! function_exists('json_decode')) {
        if (file_exists(WIZIN_ROOT_PATH . '/lib/PEAR/Jsphon/Decoder.php')) {
            require_once WIZIN_ROOT_PATH . '/lib/PEAR/Jsphon/Decoder.php';
            function json_decode($json = '', $assoc = false, $depth = 512) {
                $decorder = new Jsphon_Decoder(true);
                if ($assoc) {
                    $values = $decorder->decode($json);
                } else {
                    $values = new stdClass();
                    $array = $decorder->decode($json);
                    foreach ($array as $key => $value) {
                        $values->$key = $value;
                    }
                }
                return $values;
            }
        }
    }
}