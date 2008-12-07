<?php
/**
 * #ignore
 * 検証結果クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright rhaco project. All rights reserved.
 */
class AssertResult{
	var $type;
	var $path;
	var $line;
	var $class;
	var $method;
	var $comment;
	var $result;
	
	/**
	 * @static 
	 *
	 * @return int
	 */
	function typeSuccess(){
		return 1;
	}
	
	/**
	 * @static 
	 *
	 * @return int
	 */
	function typeFail(){
		return 2;
	}
	
	/**
	 * @static 
	 *
	 * @return int
	 */
	function typeNone(){
		return 3;
	}
	
	/**
	 * @static 
	 *
	 * @return int
	 */
	function typePass(){
		return 4;
	}
	/**
	 * @static 
	 *
	 * @return int
	 */	
	function typeViewing(){
		return 5;
	}
	
	function AssertResult($type,$path,$line){
		$this->type = intval($type);
		$this->path = $path;
		$this->line = intval($line);
	}
	
	function set($class,$method,$comment,$result){
		$this->comment = $comment;
		$this->class = $class;
		$this->method = $method;
		$this->result = $result;

		$counts = Rhaco::addVariable("RHACO_CORE_ASSERT_COUNT",Rhaco::getVariable("RHACO_CORE_ASSERT_COUNT",0,$this->type) + 1,$this->type);
		if($this->type != AssertResult::typeSuccess()) Rhaco::addVariable("RHACO_CORE_ASSERT",$this);
	}
	
	
	/**
	 * @static 
	 *
	 * @return unknown
	 */
	function results(){
		/*** #pass */
		return Rhaco::getVariable("RHACO_CORE_ASSERT",array());
	}
	
	/**
	 * @static 
	 *
	 */
	function clear(){
		Rhaco::clearVariable("RHACO_CORE_ASSERT");
		Rhaco::clearVariable("RHACO_CORE_ASSERT_COUNT");
	}
	
	/**
	 * @static 
	 *
	 * @param unknown_type $type
	 */
	function count($type=0){
		$counts = Rhaco::getVariable("RHACO_CORE_ASSERT_COUNT");
		if(empty($counts)) $counts = array();

		if($type == 0){
			$count = 0;
			foreach($counts as $c) $count += $c;
			return $count;
		}
		if(!isset($counts[$type])) return 0;
		return $counts[$type];
	}
	
	function getType(){
		return $this->type;
	}
	function getPath(){
		return $this->path;
	}
	function getLine(){
		return $this->line;
	}
	function getMethod(){
		return $this->method;
	}
	function getClass(){
		return $this->class;
	}
	function getComment(){
		return $this->comment;
	}
	function getResult(){
		return $this->result;
	}
	function getTypeString(){
		switch($this->type){
			case AssertResult::typeSuccess(): return "SUCCESS";
			case AssertResult::typeFail(): return "FAIL";
			case AssertResult::typePass(): return "PASS";;
			case AssertResult::typeNone(): return "NONE";
			case AssertResult::typeViewing(): return "VIEWING";
			default:
				return strval($this->type);
		}
	}
}
?>