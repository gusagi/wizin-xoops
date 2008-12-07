<?php
Rhaco::import("lang.Variable");
Rhaco::import("exception.model.ExceptionBase");
Rhaco::import("exception.model.GenericException");
/**
 * 疑似Exceptionを操作するクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ExceptionTrigger{
	/**
	 * 疑似Exceptionを発行する
	 *
	 * @param exception.model.ExceptionBase $exception
	 * @param string $name
	 */
	function raise($exception,$name=""){
		/***
		 * $ex = Rhaco::getVariable("RHACO_CORE_EXCEPTION");
		 * $before = sizeof($ex);
		 * assert(!ExceptionTrigger::raise(new GenericException("test")));
		 * $ex = Rhaco::getVariable("RHACO_CORE_EXCEPTION");
		 * if($before == 0) $before++; // exceptionsが生成されていない場合を考慮
		 * eq($before + 1,sizeof($ex));
		 */
		if(Variable::istype("ExceptionBase",$exception)){
			$target = debug_backtrace();
			$exception->target = (sizeof($target) > 0) ? $target[sizeof($target)-1] : $target;
			if(empty($name)) $name = preg_replace("/^.+\/([^\/]+)\.php$/","\\1",$target[0]["file"]);
			$exception->name = is_object($name) ? get_class($name) : $name;

			$ex = Rhaco::getVariable("RHACO_CORE_EXCEPTION",array());
			$ex["exceptions"][] = $exception;
			$ex[strtolower($name)][] = $exception;
			Rhaco::setVariable("RHACO_CORE_EXCEPTION",$ex);
		}
		return false;
	}
	
	/**
	 * 疑似Exceptionを発行されているか
	 * objectが指定されていた場合、そのExceptionが発行されているか
	 * @param exception.model.ExceptionBase $object
	 * @return boolean
	 */
	function isException($object=null){
		/***
		 * ExceptionTrigger::raise(new GenericException("test"));
		 * assert(ExceptionTrigger::isException(new GenericException()));
		 * assert(ExceptionTrigger::isException());
		 */
		if(empty($object)) return Rhaco::isVariable("RHACO_CORE_EXCEPTION");
		$objname = (is_object($object)) ? get_class($object) : $object;
		foreach(Rhaco::getVariable("RHACO_CORE_EXCEPTION",array(),"exceptions") as $exception){
			if(Variable::istype($objname,$exception)) return true;
		}
		return false;
	}
	
	/**
	 * 指定の疑似Exceptionが発行されているか
	 * 指定が無い場合は全体
	 *
	 * @param string $name
	 * @return boolean
	 */
	function invalid($name=null){
		/***
		 * ExceptionTrigger::raise(new GenericException("test"),"test_ex");
		 * assert(ExceptionTrigger::invalid("test_ex"));
		 * assert(ExceptionTrigger::invalid());
		 */
		if(empty($name)) $name = "exceptions";
		if(is_object($name)) $name = get_class($name);
		return Rhaco::isVariable("RHACO_CORE_EXCEPTION",strtolower($name));
	}
	
	/**
	 * 疑似Exceeption配列を取得
	 * $nameが指定されていた場合は、その疑似Exception配列のみを取得
	 *
	 * @param string $name
	 * @return array exception.model.ExceptionBase
	 */
	function get($name=null){
		/***
		 * ExceptionTrigger::raise(new GenericException("test"),"test_ex");
		 * $exs = ExceptionTrigger::get("test_ex");
		 * foreach($exs as $ex){
		 * 	assert(Variable::istype("ExceptionBase",$ex));
		 * }
		 */
		if(empty($name)) $name = "exceptions";
		$name = is_object($name) ? strtolower(get_class($name)) : strtolower($name);
		return Rhaco::getVariable("RHACO_CORE_EXCEPTION",array(),$name);
	}
	
	/**
	 * 発行した疑似Exceptionをクリアする
	 */
	function clear(){
		/***
		 * ExceptionTrigger::raise(new GenericException("test"),"test_ex");
		 * $exs = Rhaco::getVariable("RHACO_CORE_EXCEPTION");
		 * assert(!empty($exs));
		 * ExceptionTrigger::clear();
		 * $exs = Rhaco::getVariable("RHACO_CORE_EXCEPTION");
		 * assert(empty($exs));
		 * 
		 * Rhaco::setVariable("RHACO_CORE_EXCEPTION",$exs);
		 * 
		 */
		Rhaco::clearVariable("RHACO_CORE_EXCEPTION");
	}
}
?>