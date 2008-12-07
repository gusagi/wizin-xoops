<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("util.Logger");
/**
 * 順次実行処理ユーティリティ
 * 
 * @author kazutaka tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class Automator{
	var $list = array();


	/**
	 * 実行
	 *
	 * @param unknown_type $variable
	 * @return unknown_type $variable
	 */
	function execute($variable=null){
		/*** unit("util.AutomatorTest"); */
		$list = array();

		if(func_num_args() > 0){
			foreach(func_get_args() as $arg){
				foreach(ArrayUtil::arrays($arg) as $method) $list[] = $method;
			}
		}else{
			foreach(ArrayUtil::arrays($this->list) as $method) $list[] = $method;
		}
		foreach($list as $method){
			list($class,$function) = explode("::",$method);

			$object = Rhaco::obj($class);
			if($object !== null && method_exists($object,$function)){
				$variable = $object->$function($variable);
				Logger::deep_debug(Message::_("load module [{1}]",$method));
			}
		}
		return $variable;
	}
	
	/**
	 * 実行メソッドを追加
	 *
	 * @param string $method
	 */
	function add($method){
		/*** unit("util.AutomatorTest"); */
		$this->list[] = $method;
	}
}
?>