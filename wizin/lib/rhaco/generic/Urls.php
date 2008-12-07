<?php
Rhaco::import("generic.Flow");
Rhaco::import("network.http.Request");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("util.Logger");
/**
 * URLマッピングでGenericViewを実現するクラス
 * @author kazutaka tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class Urls{
	/**
	 * デフォルトのparserを返す
	 *
	 * @return tag.HtmlParser
	 */
	function getParser(){
		$flow = new Flow();
		$flow->setTemplate(Rhaco::rhacoresource("templates/generic/404.html"));
		return $flow->parser();
	}
	
	/**
	 * 定義リストに基づきURLを解釈し実行する
	 *
	 * @param array $list 定義リスト
	 * @return tag.TagParser
	 */
	function parser(){
		$request = new Request();
		$parser = null;
		$url = substr($request->args,1);

		$template = $var = $class = $method = $args = null;
		$execute = null;
		$default = null;

		$funcagrs = func_get_args();
		list($list) = ArrayUtil::arrays($funcagrs,0,1);
		$initargs = ArrayUtil::arrays($funcagrs,1);

		foreach($list as $pre => $array){
			$pre = str_replace(array("\/","/","__SLASH__"),array("__SLASH__","\/","\/"),$pre);
			if(preg_match(sprintf("/%s/",$pre),$url,$param)){
				$execute = array($param,$array);
				break;
			}
			if(isset($array["default"]) && Variable::bool($array["default"]) === true){
				 $execute = array(array(),$array);
			}
		}
		if($execute == null){
			ExceptionTrigger::raise(new IllegalArgumentException("no pattern"));
		}else{
			list($param,$array) = $execute;
			$param = ArrayUtil::arrays($param,1);
			if(array_key_exists("template",$array)) $template = $array["template"];
			if(array_key_exists("var",$array)) $var = $array["var"];
			if(array_key_exists("class",$array)) $class = $array["class"];
			if(array_key_exists("method",$array)) $method = $array["method"];
			if(array_key_exists("args",$array)) $args = $array["args"];
			if(empty($class) && (!empty($method) && !function_exists($method))) $class = "generic.Views";
			if(empty($args)) $args = $param;

			if(!empty($class)){
				array_unshift($initargs,$class);
				$object = call_user_func_array(array("Rhaco","obj"),$initargs);

				if(is_object($object)){
					if(method_exists($object,"setParam")) call_user_func_array(array(&$object,"setParam"),array($param));
					if(method_exists($object,$method)){
						$parser = call_user_func_array(array(&$object,$method),ArrayUtil::arrays($args));
					}else{
						ExceptionTrigger::raise(new NotFoundException(Message::_("`{1}` in `{2}`",$class,$method)));
					}
				}
			}else if(!empty($method)){
				$parser = call_user_func_array($method,ArrayUtil::arrays($args));
			}else{
				$parser = Urls::getParser();
			}
			if(!Variable::istype("TagParser",$parser)) ExceptionTrigger::raise(new IllegalArgumentException("extends tag.TagParser"));
		}
		if(!Variable::istype("TagParser",$parser)){
			Logger::warning("require TagParser");
			$parser = Urls::getParser();
		}
		$parser->setVariable($var);
		if(!empty($template)) $parser->setTemplate($template);
		return $parser;
	}
}
?>