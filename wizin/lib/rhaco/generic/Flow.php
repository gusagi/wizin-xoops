<?php
Rhaco::import("tag.HtmlParser");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("network.http.RequestLogin");
Rhaco::import("network.http.Header");
Rhaco::import("lang.Env");
Rhaco::import("io.FileUtil");
Rhaco::import("util.Logger");
/**
 * リクエストとテンプレートを制御する
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class Flow extends RequestLogin{
	var $parser;

	function Flow(){
		$args = func_get_args();
		$this->__init__($args);
	}
	function __init__($args=null){
		$args = ArrayUtil::arrays($args);
		parent::__init__($args);
		$this->parser = new HtmlParser();
		$this->setFilter($args);
		$this->setFilter("generic.filter.RequestLoginFilter");
	}
	function setFilter($args){		
		$this->parser->setFilter($args);		
	}
	function setReplace($arrayOrSource,$dest=""){
		$this->setBlock($arrayOrSource,$dest);		
	}
	function setBlock($templateFileName){
		$this->parser->setBlock($templateFileName);
	}
	function write($template=""){
		/*** unit("generic.FlowTest"); */
		$parser = $this->parser($template);
		$parser->write();
	}
	function parser($template=null){
		$this->parser->setVariable($this->getVariable());
		if(!empty($template)) $this->setTemplate($template);
		return $this->parser;
	}
	function setTemplate($template){
		$this->parser->setTemplate($template);		
	}
	
	/**
	 * $varnameで指定されたファイルを$basedirより探して返す
	 *
	 * @param string $varname
	 * @param string $basedir
	 */
	function requestAttach($varname,$basedir){
		$path = "";
		if($this->isVariable($varname)) $path = FileUtil::path($basedir,$this->getVariable($varname));
		if(empty($path) && $this->map(0) == $varname) $path = FileUtil::path($basedir,ArrayUtil::implode($this->map(),"/",1));
		if(!empty($path) && is_file($path)){
			Logger::deep_debug("request attach [".$path."]");
			Logger::disableDisplay();
			Header::attach($path);
			Rhaco::end();
		}
	}
	
	/**
	 * $varnameで指定されたtemplateを表示する
	 * その際、$extensionで指定した拡張子のtemplateのurlを変換する
	 *
	 * @param string $varname
	 * @param string $extension
	 */	
	function viewer($varname,$extension="html"){
		$path = "";
		if($this->isVariable($varname)) $path = $this->getVariable($varname).".".$extension;
		if(empty($path) && $this->map(0) == $varname) $path = ArrayUtil::implode($this->map(),"/",1).".".$extension;
		if(!empty($path)){
			$url = str_replace("/","\\/",preg_quote(Rhaco::templateurl()));

			if($this->isVariable($varname)){
				print(preg_replace("/([\"\'])".$url."(.+)\.".$extension."?\\1/",Rhaco::uri()."?t=\\2",$this->read($path)));
			}else if(strpos(Rhaco::uri(),".") === false){
				print(preg_replace("/([\"\'])".$url."(.+)\.".$extension."?\\1/",dirname(Env::called())."/t/\\2",$this->read($path)));
			}else{
				print(preg_replace("/([\"\'])".$url."(.+)\.".$extension."?\\1/",Env::called()."/t/\\2",$this->read($path)));
			}
		}
	}
}
?>