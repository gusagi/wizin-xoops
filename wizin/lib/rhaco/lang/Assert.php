<?php
Rhaco::import("util.Logger");
Rhaco::import("io.Snapshot");
Rhaco::import("io.FileUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("lang.model.AssertResult");
/**
 * #ignore
 * 検証用クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Assert{
	var $path;	
	var $class;
	var $method;
	var $line;

	function assertEquals($arg1,$arg2,$comment=""){
		if(Variable::equal($arg1,$arg2)){
			return $this->_success($comment);
		}else{
			$snap = new Snapshot();
				var_dump($arg1);
			$result1 = $snap->get();
			$snap = new Snapshot();
				var_dump($arg2);
			$result2 = $snap->get();
			return $this->_fail($comment,sprintf("expectation [%s] : Result [%s]",$result1,$result2));
		}
	}
	function assertNotEquals($arg1,$arg2,$comment=""){
		return (!Variable::equal($arg1,$arg2)) ?
					$this->_success($comment) :
					$this->_fail($comment,sprintf("expectation [%s] : Result [%s]",print_r($arg1,true),print_r($arg2,true)));
	}
	function assertTrue($arg1,$comment=""){
		return ($arg1 === true) ?
					$this->_success($comment) :
					$this->_fail($comment,sprintf("expectation [%s] : Result [%s]",print_r(true,true),print_r($arg1,true)));
	}	
	function assertFalse($arg1,$comment=""){
		return ($arg1 === false) ?
					$this->_success($comment) :
					$this->_fail($comment,sprintf("expectation [%s] : Result [%s]",print_r(false,true),print_r($arg1,true)));
	}
	function assertEqualsFile($filepath,$arg,$comment=""){
		return $this->assertEquals(FileUtil::read($filepath),$arg,$comment);
	}
	function none(){
		$this->_setCalled(1);
		$obj = new AssertResult(AssertResult::typeNone(),$this->path,$this->line);
		$obj->set($this->class,$this->method,"","");
		return $this->_clearCalled(true);
	}
	function pass($comment=""){
		$this->_setCalled(1);
		$obj = new AssertResult(AssertResult::typePass(),$this->path,$this->line);
		$obj->set($this->class,$this->method,$comment,"");
		return $this->_clearCalled(true);
	}
	function _success($comment=""){
		$this->_setCalled(2);
		$obj = new AssertResult(AssertResult::typeSuccess(),$this->path,$this->line);
		$obj->set($this->class,$this->method,$comment,"");
		return $this->_clearCalled(true);
	}
	function _fail($comment="",$result=""){
		$this->_setCalled(2);
		$obj = new AssertResult(AssertResult::typeFail(),$this->path,$this->line);
		$obj->set($this->class,$this->method,$comment,$result);
		return $this->_clearCalled(false);
	}
	function _setCalled($pos){
		if(empty($this->method)){
			$debug = debug_backtrace();
			$this->line = $debug[$pos]["line"];
			$this->method = $debug[$pos+1]["function"];
		}
	}
	function _clearCalled($bool){
		$this->line = 0;
		$this->method = "";
		return $bool;
	}
	function setMethod($method,$line){
		$this->method = $method;
		$this->line = $line;
	}
	function setClass($class,$path){
		$this->class = $class;
		$this->path = $path;
	}
	
	/**
	 * @static 
	 *
	 */
	function flush(){
		/*** #pass */
		print(sprintf("success: %d / fail: %d / pass: %d / none: %d / all: %d\n\n",
				AssertResult::count(AssertResult::typeSuccess())
				,AssertResult::count(AssertResult::typeFail())
				,AssertResult::count(AssertResult::typePass())
				,AssertResult::count(AssertResult::typeNone())
				,AssertResult::count()));

		foreach(AssertResult::results() as $assert){
			print(sprintf("(%s::%s)%s %s:%d\n",$assert->getClass(),$assert->getMethod(),$assert->getTypeString(),$assert->getPath(),$assert->getLine()));
		}
		AssertResult::clear();
	}
}
?>