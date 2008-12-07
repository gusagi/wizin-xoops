<?php
Rhaco::import("lang.StringUtil");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008- rhaco project. All rights reserved.
 */
class DocFunction{
	var $description;
	var $name;
	var $vars = array();
	var $tests = array();

	function DocFunction($name,$contents,$description=""){
		$this->description = StringUtil::comments($description);
		$this->name = trim($name);
		$this->tests = $this->_test($contents);
		$this->vars = $this->_one_variable($contents);
	}
	function _one_variable($value){
		$result = array();

		if(preg_match_all("/[^\\$\\\\](\\$[\w_]+)/"," ".$value,$variables)){
			$vars = array();
			foreach($variables[1] as $variable){
				$vars[$variable] = (isset($vars[$variable])) ? true : false;
			}
			foreach($vars as $key => $bool){
				if(!$bool && !in_array($key,array("\$this","\$GLOBALS","\$_SERVER","\$_SESSIO","\$_COOKIE"))) $result[] = $key;
			}
		}
		return $result;
	}
	function _test($value){
		$result = array();
		if(preg_match_all("/\/\*\*\*.+?\*\//s",$value,$match)){
			foreach($match[0] as $test){
				$result[] = StringUtil::comments($test);
			}
		}
		return $result;
	}
	function isLocal(){
		return !(!empty($this->name) && substr($this->name,0,1) != "_");
	}
}
?>