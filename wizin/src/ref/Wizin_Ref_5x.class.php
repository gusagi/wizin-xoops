<?php
/**
 * Wizin framework reference class
 *
 * PHP Versions 5
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
     * @access public
     *
     */
    class Wizin_Ref
    {
        private $_mReference;

        public function Wizin_Ref( & $value )
        {
            $this->_mReference =& $value;
        }

        public function & get()
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
