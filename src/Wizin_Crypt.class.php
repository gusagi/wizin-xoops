<?php
/**
 * Wizin framework PEAR::Crypt_Blowfish wrapper class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Crypt')) {
    require dirname(__FILE__) . '/Wizin.class.php';
    if (! class_exists('Crypt_Blowfish')) {
        require WIZIN_ROOT_PATH . '/lib/PEAR/Crypt/Blowfish.php';
    }

    /**
     * PEAR::Crypt_Blowfish wrapper class
     *
     * @access public
     *
     */
    class Wizin_Crypt
    {
        function & getBlowfish($key = '')
        {
            static $blowfish;
            if (! isset($blowfish)) {
                if ($key === '') {
                    $key = Wizin::salt();
                }
                $iv = substr(md5(Wizin::salt() ."\t" .__FILE__, 1), 0, 8);
                $blowfish =& Crypt_Blowfish::factory('cbc', $key, $iv);
            }
            return $blowfish;
        }
    }
}
