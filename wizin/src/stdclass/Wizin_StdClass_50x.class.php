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
     * @access public
     *
     */
    class Wizin_StdClass
    {
        protected $_aVars = array();

        /**
         * @access public
         *
         * @param string $key
         * @param mixed $value
         */
        public function __set( $key, $value )
        {
            $this->_aVars[$key] =& $value;
        }

        /**
         * @access public
         *
         * @param string $key
         * @return mixed
         */
        public function __get( $key )
        {
            if ( isset($this->_aVars[$key]) ) {
                return $this->_aVars[$key];
            } else {
                return NULL;
            }
        }
    }
}
