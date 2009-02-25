<?php
/**
 * Wizin framework "HTML_CSS_Mobile" wrapper class
 *
 * PHP Versions 5.2
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license MIT License
 *
 * As for this class, it depends on HTML_CSS_Mobile which is distributed with the license.
 * Special thanks : Daichi Kamemoto <daikame@gmail.com>
 */

if (! class_exists('Wizin_Filter_Css')) {
    if (class_exists('DOMDocument') && class_exists('SimpleXMLElement') &&
            method_exists('SimpleXMLElement','getName')) {
        // If class 'HTML_CSS_Mobile' does not exists, include HTML_CSS_Mobile
        if (! class_exists('HTML_CSS_Mobile')) {
            if (! class_exists('HTML_Common')) {
                @ include_once 'HTML/Common.php';
            }
            if (class_exists('HTML_Common') && ! class_exists('HTML_CSS')) {
                @ include_once 'HTML/CSS.php';
            }
            if (class_exists('HTML_CSS') && ! class_exists('HTML_CSS_Mobile')) {
                @ include_once 'HTML/CSS/Mobile.php';
            }
        }

        if (class_exists('HTML_CSS_Mobile')) {
            /**
             * Wizin framework "HTML_CSS_Mobile" wrapper class
             *
             * @access public
             */
            class Wizin_Filter_Css extends HTML_CSS_Mobile
            {
                public static function getInstance()
                {
                    return new Wizin_Filter_Css();
                }
            }
        }
    }
}
