<?php
/**
 * Wizin framework mail class
 *
 * PHP Version 4.4/5.0 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if (! class_exists('Wizin_Mail')) {
    require dirname(__FILE__) .'/mail/Qdmail.class.php';

    /**
     * Wizin framework mail class
     *
     */
    class Wizin_Mail extends Wizin_Mail_Qdmail
    {
    }
}