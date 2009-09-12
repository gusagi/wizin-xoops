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

/**
 * Jsphon_Error
 * 
 * @author Hawk
 */
class Jsphon_Error
{
    var $_isPHP5;

    var $_existsPEAR;

    var $_throwException = true;

    var $_exceptionCreator;

    /**
     * Constructor
     * 
     * @access private
     */
    function Jsphon_Error()
    {
        $this->_isPHP5     = version_compare(phpversion(), '5', '>=');
        $this->_existsPEAR = (@include_once "PEAR/ErrorStack.php");
        
        if($this->_isPHP5) {
            if($this->_existsPEAR) {
                require_once(dirname(__FILE__) .'/Exception.php');
                $method = '_createJsphonException';
            } else {
                $method = '_createException';
            }
            $this->_exceptionCreator = array(__CLASS__, $method);
        }
    }

    /**
     * 
     * 
     * @since 06/09/05 13:21
     * @return Jsphon_Error
     */
    function &singleton()
    {
        static $instance = null;
        if($instance === null) {
            $instance = new Jsphon_Error();
        }
        return $instance;
    }

    /**
     * 
     * @static
     * @param  int      $code
     * @param  string   $level
     * @param  array    $params
     * @param  string   $msg
     * @param  mixed    $repackage
     * @param  array    $backtrace
     * @return array
     */
    function push($code, $level = 'error', $params = array(), $msg = false,
                  $repackage = false, $backtrace = false)
    {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }

        $self  =& Jsphon_Error::singleton();

        if(!$self->_isPHP5 || !$self->_throwException) {
            return $self->_push(
                $code, $level, $params, $msg, $repackage, $backtrace);
        }

        $pushOnly = true;
        $err = $self->_push(
                $code, $level, $params, $msg, $repackage, $backtrace, $pushOnly);

        array_shift($backtrace);
        throw (call_user_func($self->_exceptionCreator, $msg, $code, $backtrace));
        return ;
    }

    /**
     * 
     * 
     * @access private
     * @static
     * @param  int      $code
     * @param  string   $level
     * @param  array    $params
     * @param  string   $msg
     * @param  mixed    $repackage
     * @param  array    $backtrace
     * @param  bool     $pushOnly
     * @return array or null unless PEAR_ErrorStack exists.
     */
    function _push($code, $level='error', $params=array(), $msg=false,
                              $repackage=false, $backtrace=false, $pushOnly=false)
    {
        if(!$this->_existsPEAR) {
            return;
        }

        $stack =& PEAR_ErrorStack::singleton('Jsphon');
        if($pushOnly) {
            $stack->pushCallback(array(__CLASS__, 'pushOnly'));
        }
        
        $err = $stack->push($code, $level, $params, $msg, $repackage, $backtrace);

        if($pushOnly) {
            $stack->popCallback();
        }
        return $err;
    }

    /**
     * 
     * @since 06/09/05 13:34
     * @param  String    $bool
     * @param  String    $exceptionClass
     * @return String
     */
    function setThrowException($bool)
    {
        $this->_throwException = $bool;
    }

    /**
     * error handler which do nothing
     * 
     * @since 06/09/05 13:52
     * @param  array    $err
     * @return int
     */
    function pushOnly($err)
    {
        return PEAR_ERRORSTACK_PUSH;
    }

    /**
     * 
     * @static
     * @param  String    $msg
     * @param  int       $code
     * @param  array    $trace
     * @return Exception
     */
    function _createJsphonException($msg, $code, $trace)
    {
        return new Jsphon_Exception($msg, $code, $trace);
    }

    /**
     * 
     * @static
     * @param  String    $msg
     * @param  int       $code
     * @param  array    $trace
     * @return Exception
     */
    function _createException($msg, $code, $trace)
    {
        return new Exception($msg, $code);
    }
}


?>
