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
            $inputFilter = $this->_aInputFilter;
            if ( empty($inputFilter) ) {
                $inputFilter = array();
            }
            $inputFilter[] = array( $function, $params );
            $this->_aInputFilter = $inputFilter;
        }

        function addOutputFilter( $function, & $params = null )
        {
            if ( is_null($params) ) {
                $params = array();
            }
            $outputFilter = $this->_aOutputFilter;
            if ( is_null($outputFilter) ) {
                $outputFilter = array();
            }
            $outputFilter[] = array( $function, $params );
            $this->_aOutputFilter = $outputFilter;
        }

    }
}
