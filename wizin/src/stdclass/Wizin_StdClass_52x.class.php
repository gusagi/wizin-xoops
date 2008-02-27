<?php
/**
 * Wizin framework standard class for PHP5.2.x
 *
 * PHP Versions 5
 *
 * @package  Wizin
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */

if ( ! class_exists('Wizin_StdClass') ) {

    /**
     * @public
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
            if ( is_array($value) ) {
                $this->_aVars[$key] = new ArrayObject($value, ArrayObject::ARRAY_AS_PROPS);
            } else {
                $this->_aVars[$key] = $value;
            }
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
