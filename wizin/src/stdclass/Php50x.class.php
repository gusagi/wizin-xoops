<?php
/**
 * Wizin framework standard class for PHP5.0.x, PHP5.1.x
 *
 * PHP Versions 5
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_StdClass') ) {

    /**
     * Wizin framework standard class for PHP5.0.x or PHP5.1.x
     *
     * @access public
     */
    class Wizin_StdClass
    {
        protected $_aVars = array();

        /**
         * set value to this object vars
         *
         * @param string $key
         * @param mixed $value
         */
        public function __set( $key, $value )
        {
            if ( is_object($value) && get_class($value) === 'Wizin_Ref' ) {
                $var =& $value->get();
            } else {
                $var =& $value;
            }
            $this->_aVars[$key] =& $var;
        }

        /**
         * get value from this object vars
         *
         * @param string $key
         * @return mixed
         */
        public function & __get( $key )
        {
            if ( isset($this->_aVars[$key]) ) {
                $var =& $this->_aVars[$key];
                return $var;
            } else {
                $var = null;
                return $var;
            }
        }
    }
}
