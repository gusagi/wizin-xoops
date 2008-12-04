<?php
/**
 * Wizin framework standard class for PHP4.x
 *
 * PHP Versions 4
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_StdClass') ) {

    /**
     * Wizin framework standard class for PHP4.x
     *
     * @access public
     */
    class Wizin_StdClass
    {
        var $_aVars = array();  // protected

        /**
         * constructor
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
         * magic method "__construct" fallback function for PHP4.x
         *
         */
        function __construct()
        {
        }

        /**
         * magic method "__destruct" fallback function for PHP4.x
         *
         */
        function __destruct()
        {
        }

        /**
         * set value to this object vars(magic method "__set" fallback function for PHP4.x)
         *
         * @param string $key
         * @param mixed $value
         */
        function __set( $key, $value )
        {
            if ( is_object($value) && get_class($value) === 'wizin_ref' ) {
                $var =& $value->get();
            } else {
                $var =& $value;
            }
            $this->_aVars[$key] =& $var;
            return true;
        }

        /**
         * get value from this object vars(magic method "__get" fallback function for PHP4.x)
         *
         * @param string $key
         * @return mixed
         */
        function __get( $key, &$return )
        {
            if ( isset($this->_aVars[$key]) ) {
                $var =& $this->_aVars[$key];
                $return = $var;
            } else {
                $var = null;
                $return = $var;
            }
            return true;
        }

    }
}
