<?php
/**
 * Wizin framework standard class for PHP4.x
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  gusagi<gusagi@gusagi.com>
 * @copyright  2007 - 2008 gusagi
 * @license http://creativecommons.org/licenses/by-nc-sa/2.1/jp/  Creative Commons ( Attribution - Noncommercial - Share Alike 2.1 Japan )
 *
 */


if ( ! class_exists('Wizin_StdClass') ) {

    /**
     * @access public
     *
     */
    class Wizin_StdClass
    {
        var $_aVars = array();  // protected

        /**
         * @access public
         *
         */
        function Wizin_StdClass()
        {
            overload( get_class($this) );
            if ( method_exists($this, '__destruct') ) {
                register_shutdown_function( array(&$this, '__destruct') );
            }
            $args = func_get_args();
            call_user_func_array( array(&$this, '__construct'), $args );
        }

        /**
         * @access public
         *
         */
        function __construct()
        {
        }

        /**
         * @access public
         *
         */
        function __destruct()
        {
        }

        /**
         * @access public
         *
         * @param string $key
         * @param mixed $value
         */
        function __set( $key, $value )
        {
            $this->_aVars[$key] = $value;
            return true;
        }

        /**
         * @access public
         *
         * @param string $key
         * @return mixed
         */
        function __get( $key, &$return )
        {
            if ( isset($this->_aVars[$key]) ) {
                $return = $this->_aVars[$key];
            } else {
                $return = NULL;
            }
            return true;
        }

    }
}
