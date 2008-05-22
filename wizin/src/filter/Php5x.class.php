<?php
/**
 * Wizin framework filter class
 *
 * PHP Versions 5
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Filter') ) {
    require dirname( __FILE__ ) . '/Common.class.php';

    class Wizin_Filter extends Wizin_Filter_Common
    {
        function addInputFilter( $function, & $params = null )
        {
            if ( is_null($params) ) {
                $params = array();
            }
            if ( is_null($this->_aInputFilter) ) {
                $this->_aInputFilter = array();
            }
            $this->_aInputFilter[] = array( $function, $params );
        }

        function addOutputFilter( $function, & $params = null )
        {
            if ( is_null($params) ) {
                $params = array();
            }
            if ( is_null($this->_aOutputFilter) ) {
                $this->_aOutputFilter = array();
            }
            $this->_aOutputFilter[] = array( $function, $params );
        }

    }
}
