<?php
//
// +--------------------------------------------------------------------+
// | PHP version 4 and 5                                                |
// +--------------------------------------------------------------------+
// | Copyright (c) 2006 Hawk                                            |
// +--------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,    |
// | that is available at http://www.php.net/license/3_0.txt.           |
// | If you did not receive a copy of the PHP license and are unable to |
// | obtain it through the world-wide-web, please send a note to        |
// | license@php.net so we can mail you a copy immediately.             |
// +--------------------------------------------------------------------+
// | Authors: Hawk <scholar@hawklab.jp>                                 |
// +--------------------------------------------------------------------+
//
//

require_once('PEAR/Exception.php');

/**
 * Jsphon_Exception
 * 
 * @author Hawk
 */
class Jsphon_Exception extends PEAR_Exception
{
    private $_outerTrace;
    
    /**
     * Constructor
     * 
     */
    public function __construct($msg, $code, $outerTrace)
    {
        $this->_outerTrace = $outerTrace;
        parent::__construct($msg, $code);
    }
    
    /**
     * 
     * 
     * @override
     * @return array
     */
    public function getTraceSafe()
    {
        if(count($this->_outerTrace) > 0) {
            return $this->_outerTrace;
        }
        return parent::getTraceSafe();
    }

}

?>
