<?php
/**
 * Wizin framework reference class
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Ref') ) {
    require dirname( dirname(__FILE__) ) . '/Wizin.class.php';

    /**
     * Wizin framework reference class for PHP4.x
     *
     * @access public
     */
    class Wizin_Ref
    {
        var $_mReference;

        /**
         * constructor
         *
         * @param mixed $value
         * @return Wizin_Ref
         */
        function Wizin_Ref( & $value )
        {
            $this->_mReference =& $value;
        }

        /**
         * return reference
         *
         * @return string mixed
         */
        function & get()
        {
            if ( isset($this->_mReference) ) {
                $return = & $this->_mReference;
                return $return;
            } else {
                return null;
            }
        }
    }
}
