<?php
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Rhaco{
	var $var = array();
	var $def = array();
	var $shutdown = array();

	var $loaded = array();
	var $classese = array();

	function rhacoversion(){
		/*** #pass */
		return "1.6.1";
	}
	/**
	 * アプリケーションのバージョン
	 *
	 * @return string
	 */
	function version(){
		/*** eq(Rhaco::constant("PROJECT_VERSION"),Rhaco::version()); */
		return Rhaco::constant("PROJECT_VERSION","1.0");
	}
	
	/**
	 * 指定のバージョン以上か
	 *
	 * @param string $version
	 * @return boolean
	 */
	function isVersion($version){
		return (version_compare(Rhaco::version(),strval($version)) >= 0);		
	}
	/**
	 * 定数定義
	 *
	 * @param string $defineName
	 * @param string $value
	 * @return string
	 */
	function constant($defineName,$value=null){
		/***
		 * eq(null,Rhaco::constant("hoge"));
		 * eq(123,Rhaco::constant("hoge",123));
		 * eq(123,Rhaco::constant("hoge"));
		 * eq(123,Rhaco::constant("hoge",789));
		 * eq(123,Rhaco::constant("hoge"));
		 */
		if(!isset($GLOBALS["__RHACO__"]->def[$defineName]) && $value !== null) $GLOBALS["__RHACO__"]->def[$defineName] = $value;
		return (isset($GLOBALS["__RHACO__"]->def[$defineName])) ? $GLOBALS["__RHACO__"]->def[$defineName] : null;
	}
	/**
	 * 定数のクリア
	 */
	function undef(){
		unset($GLOBALS["__RHACO__"]->def);
		$GLOBALS["__RHACO__"]->def = array();
	}
	/**
	 * アプリケーションパスをルートとした絶対パスを取得
	 *
	 * @param string $filename
	 * @return string
	 */
	function path($filename=""){
		return ((Rhaco::constant("CONTEXT_PATH") != null) ? Rhaco::constant("CONTEXT_PATH") : dirname($_SERVER["SCRIPT_FILENAME"])."/").(!empty($filename) && ($filename[0] == "/") ? substr($filename,1) : $filename);
	}
	/**
	 * URLをルートとした絶対パスを取得
	 *
	 * @param string $url
	 * @return string
	 */
	function url($url=""){
		$path = Rhaco::constant("CONTEXT_URL");
		if(Rhaco::constant("CONTEXT_URL") == null){
			$path = "http://".$_SERVER["HTTP_HOST"];
			$path = (!empty($_SERVER["SCRIPT_NAME"]) && preg_match("/\/([^\/])\/.+/",$_SERVER["SCRIPT_NAME"],$null)) ? $path."/".$null[1] : $path;
		}
		return (str_replace("\\","/",(substr($path,-1) != "/") ? $path."/" : $path)).((substr($url,0,1) == "/") ? substr($url,1) : $url);
	}
	
	/**
	 * URIを返す
	 *
	 * @return string
	 */
	function uri(){
		/*** eq(preg_replace("/\?.+$/","",$_SERVER["REQUEST_URI"]),Rhaco::uri()); */
		return preg_replace("/\?.+$/","",$_SERVER["REQUEST_URI"]);
	}
	
	/**
	 * .../*.phpを返す
	 *
	 * @param string $pagename
	 * @return string
	 */
	function page($pagename){
		return Rhaco::url($pagename.".php");
	}
	/**
	 * リソースパスをルートとした絶対パスを取得
	 *
	 * @param string $filename
	 * @return string
	 */
	function resource($filename=""){
		$path = (Rhaco::constant("PROJECT_PATH") == null) ? Rhaco::path("resources/") : Rhaco::filepath(Rhaco::constant("PROJECT_PATH"),"resources")."/";
		if(empty($filename)) return $path;
		return Rhaco::filepath($path,$filename);
	}
	
	/**
	 * ライブラリパスを返す
	 *
	 * @param string $filename
	 * @return string
	 */
	function lib($filename=""){
		$path = (Rhaco::constant("PROJECT_PATH") == null) ? Rhaco::path("library/") : Rhaco::filepath(Rhaco::constant("PROJECT_PATH"),"library")."/";
		if(empty($filename)) return $path;
		return Rhaco::filepath($path,$filename);
	}
	/**
	 * セットアップパスをルートとした絶対パスを取得
	 *
	 * @param string $filename
	 * @return string
	 */
	function setuppath($filename=""){
		$path = (Rhaco::constant("PROJECT_PATH") == null) ? Rhaco::path("setup/") : Rhaco::filepath(Rhaco::constant("PROJECT_PATH"),"setup")."/";
		if(empty($filename)) return $path;
		return Rhaco::filepath($path,$filename);
	}

	/**
	 * テンプレートパスをルートとした絶対パスを取得
	 *
	 * @param string $filename
	 * @return string
	 */
	function templatepath($filename=""){
		$templatepath = Rhaco::constant("TEMPLATE_PATH");
		if(empty($templatepath)) $templatepath = Rhaco::resource("templates/");
		if(empty($filename)) return $templatepath;
		if($filename[0] === "/" || preg_match("/^[\w]+\:\/\//",$filename)) return $filename;
		return Rhaco::filepath($templatepath,$filename);
	}
	/**
	 * テンプレートURLをルートとした絶対パスを取得
	 *
	 * @param string $url
	 * @return string
	 */
	function templateurl($url=""){
		$path = Rhaco::constant("TEMPLATE_URL");
		return (str_replace("\\","/",(substr($path,-1) != "/") ? $path."/" : $path)).((substr($url,0,1) == "/") ? substr($url,1) : $url);
	}
	/**
	 * rhacoのリソースパスをルートとした絶対パスを取得
	 *
	 * @param string $resourceFileName
	 * @return string
	 */
	function rhacoresource($resourceFileName){
		return Rhaco::filepath(Rhaco::rhacopath(),sprintf("resources/%s",$resourceFileName));
	}	
	/**
	 * 評価する
	 */
	function execute($rhaco_execute_srcOrPath,$rhaco_execute_variables=array(),$rhaco_execute_include=false){
		/***
			Rhaco::import("io.Snapshot");
			$src = 'hoge<?php print($abc); ?>hoge';
			$snap = new Snapshot();
				Rhaco::execute($src,array("abc"=>123));
			$result = $snap->get();
			eq("hoge123hoge",$result);
		*/
		foreach($rhaco_execute_variables as $rhaco_execute_key => $rhaco_execute_variable){
			$c = substr($rhaco_execute_key,0,1);
			if(ctype_alnum($c) || $c == "_"){
				$$rhaco_execute_key = $rhaco_execute_variable;
			}
		}
		if($rhaco_execute_include){
			if(is_file($rhaco_execute_srcOrPath)) include($rhaco_execute_srcOrPath);
		}else{
			eval("?>".$rhaco_execute_srcOrPath);
		}
	}
	/**
	 * PHPスクリプトとして評価する
	 */
	function phpexe($php){
		/***
		 * eq("abc",Rhaco::phpexe("return 'abc';"));
		 */
		return eval($php);
	}
	function rhacopath(){
		/*** eq(constant("RHACO_DIR"),Rhaco::rhacopath()); */
		return constant("RHACO_DIR");
	}	
	/**
	 * スクリプトを終了する
	 *
	 * @param unknown_type $arg
	 */
	function end($arg=""){
		Rhaco::shutdown();
		exit($arg);
	}
	
	/**
	 * スクリプトの終了処理を呼ぶ
	 *
	 */
	function shutdown(){
		if(!empty($GLOBALS["__RHACO__"]->shutdown)) krsort($GLOBALS["__RHACO__"]->shutdown);
		while(!empty($GLOBALS["__RHACO__"]->shutdown)){
			call_user_func_array(array_shift($GLOBALS["__RHACO__"]->shutdown),array());
		}
		unset($GLOBALS["__RHACO__"]);
		return true;
	}
	
	/**
	 * スクリプトの終了処理を登録する
	 *
	 * @param function $func
	 * @return boolean
	 */
	function register_shutdown($func){
		$GLOBALS["__RHACO__"]->shutdown[] = $func;
	}

	/**
	 * 変数の取得する
	 *
	 * @static
	 * @param string $name
	 * @param unknown_type $default
	 * @param unknown_type $key
	 * @return unknown
	 */
	function getVariable($name,$default=null,$key=null){
		/***
		 * Rhaco::clearVariable("hoge");
		 * assert(!Rhaco::isVariable("hoge"));
		 * eq("abc",Rhaco::getVariable("hoge","abc"));
		 * eq(null,Rhaco::getVariable("hoge"));
		 * Rhaco::setVariable("hoge",123);
		 * eq(123,Rhaco::getVariable("hoge"));
		 * Rhaco::setVariable("hoge",array("abc","def"));
		 * eq(array("abc","def"),Rhaco::getVariable("hoge"));
		 * 
		 * Rhaco::clearVariable("kaeru");
		 * assert(!Rhaco::isVariable("kaeru"));
		 * Rhaco::addVariable("kaeru","abc");
		 * Rhaco::addVariable("kaeru","def");
		 * eq(array("abc","def"),Rhaco::getVariable("kaeru"));
		 * eq("def",Rhaco::getVariable("kaeru",null,1));
		 */
		return (!isset($GLOBALS["__RHACO__"]->var[$name])) ? $default : (($key === null) ? $GLOBALS["__RHACO__"]->var[$name] : (isset($GLOBALS["__RHACO__"]->var[$name][$key]) ? $GLOBALS["__RHACO__"]->var[$name][$key] : $default));
	}
	/**
	 * 変数が定義されているか
	 *
	 * @static 
	 * @param unknown_type $name
	 * @param unknown_type $key
	 * @return boolean
	 */
	function isVariable($name,$key=null){
		/***
		 * Rhaco::clearVariable("hoge");
		 * assert(!Rhaco::isVariable("hoge"));
		 * Rhaco::setVariable("hoge",123);
		 * assert(Rhaco::isVariable("hoge"));
		 */
		return ($key === null) ? isset($GLOBALS["__RHACO__"]->var[$name]) : isset($GLOBALS["__RHACO__"]->var[$name][$key]);
	}
	/**
	 * 変数を取得する
	 *
	 * @static 
	 * @param string $name
	 * @param unknown_type $value
	 */
	function setVariable($name,$value=null){
		/***
		 * Rhaco::clearVariable("hoge");
		 * assert(!Rhaco::isVariable("hoge"));
		 * Rhaco::setVariable("hoge",123);
		 * assert(Rhaco::isVariable("hoge"));
		 * eq(123,Rhaco::getVariable("hoge"));
		 */
		if(is_string($name)) $name = array($name=>$value);
		if(empty($GLOBALS["__RHACO__"]->var)) $GLOBALS["__RHACO__"]->var = array();
		$GLOBALS["__RHACO__"]->var = array_merge((array)$GLOBALS["__RHACO__"]->var,(array)$name);
	}
	/**
	 * 変数を配列として追加する
	 *
	 * @static
	 * @param string $name
	 * @param unknown_type $value
	 * @param unknown_type $key
	 */
	function addVariable($name,$value,$key=null){
		/***
		 * Rhaco::clearVariable("kaeru");
		 * assert(!Rhaco::isVariable("kaeru"));
		 * Rhaco::addVariable("kaeru","abc");
		 * Rhaco::addVariable("kaeru","def");
		 * Rhaco::addVariable("kaeru",123,"ab");
		 * eq(array("abc","def","ab"=>123),Rhaco::getVariable("kaeru"));
		 */
		if(!isset($GLOBALS["__RHACO__"]->var[$name])) $GLOBALS["__RHACO__"]->var[$name] = array();
		if($key === null){
			$GLOBALS["__RHACO__"]->var[$name][] = $value;
		}else{
			$GLOBALS["__RHACO__"]->var[$name][$key] = $value;			
		}
	}
	/**
	 * 変数をクリアする
	 *
	 * @static
	 * @param string $name
	 * @param unknown_type $key
	 */
	function clearVariable($name,$key=null){
		/***
		 * Rhaco::clearVariable("kaeru");
		 * assert(!Rhaco::isVariable("kaeru"));
		 * Rhaco::setVariable("kaeru",123);
		 * assert(Rhaco::isVariable("kaeru"));
		 * Rhaco::clearVariable("kaeru");
		 * assert(!Rhaco::isVariable("kaeru"));
		 * Rhaco::addVariable("kaeru","abc");
		 * Rhaco::addVariable("kaeru","def");
		 * Rhaco::addVariable("kaeru",123,"ab");
		 * eq(array("abc","def","ab"=>123),Rhaco::getVariable("kaeru"));
		 * Rhaco::clearVariable("kaeru",1);
		 * eq(array("abc","ab"=>123),Rhaco::getVariable("kaeru"));
		 */
		if($key === null){
			unset($GLOBALS["__RHACO__"]->var[$name]);
		}else if(isset($GLOBALS["__RHACO__"]->var[$name][$key])){
			unset($GLOBALS["__RHACO__"]->var[$name][$key]);
		}
	}
	
	function error($errno,$errstr,$errfile,$errline){
		if((version_compare(phpversion(),strval(5)) >= 0) && $errno == E_STRICT){
			if(strpos($errstr,"Non-static method") !== false) return true;
			if(strpos($errstr,"Declaration of") !== false) return true;			
		}
		return false;
	}
	function filepath($base,$path=""){
		$base = str_replace("\\","/",$base);
		if(substr($base,-1) != "/") $base .= "/";
		if($path == "") return $base;
		$path = str_replace("\\","/",$path);
		return $base.$path;
	}

	function realpath($path){
		$includefile = array(
							"lib"=>Rhaco::lib(str_replace(".","/",$path).".php"),
							"rhaco"=>((substr(Rhaco::rhacopath(),-1) != "/") ? Rhaco::rhacopath()."/" : Rhaco::rhacopath()).str_replace(".","/",$path).".php"
						);
		if(Rhaco::getVariable("RHACO_CORE_IMPORT_TESTS",false)){
			$includefile["test"] = Rhaco::setuppath("tests/".str_replace(".","/",$path).".php");
		}
		foreach($includefile as $route => $file){
			if(is_readable($file)) return $file;
		}
		return false;
	}
	/**
	 * ライブラリをimportする
	 *
	 * @param string $path
	 * @return boolean
	 */
	function import($path){
		if(isset($GLOBALS["__RHACO__"]->loaded[$path])) return true;
		$realpath = Rhaco::realpath($path);
		
		if($realpath !== false){
			$pos = strrpos($path,".");
			$name = substr($path,($pos !== false) ? $pos + 1 : $pos);
			$class = $name;

			$lowername = strtolower($name);
			if(!isset($GLOBALS["__RHACO__"]->classese[$lowername])) $GLOBALS["__RHACO__"]->classese[$lowername] = array();
			$GLOBALS["__RHACO__"]->classese[$lowername][] = strtolower($class);
			$GLOBALS["__RHACO__"]->loaded[$path] = array($name,$class,$realpath);
			include_once($realpath);
			return true;
		}
		return (is_file($path) && include_once($path));
	}

	function istype($type,$obj){
		$type = strtolower($type);
		if(isset($GLOBALS["__RHACO__"]->classese[$type])){
			$class = strtolower(get_class($obj));
			foreach($GLOBALS["__RHACO__"]->classese[$type] as $c){
				if($class == $c) return true;
				if(is_subclass_of($obj,$c)) return true;
			}
		}
		return false;
	}

	/**
	 * パッケージパスからインスタンスを生成する
	 *
	 * @param string $path
	 * @return object
	 */
	function obj(){
		/***
		 * eq("doctest",strtolower(get_class(Rhaco::obj("util.DocTest"))));
		 */
		$args = func_get_args();
		$path = array_shift($args);
		$argvalue = array();
		Rhaco::import($path);

		foreach($args as $key => $value){
			$argvalue[] = "\$args[".$key."]";
		}		
		if(isset($GLOBALS["__RHACO__"]->loaded[$path])){
			list($name,$class,$file) = $GLOBALS["__RHACO__"]->loaded[$path];
			return eval("return new ".$class."(".implode(",",$argvalue).");");
		}
		$name = preg_replace("/.*\/([^\/]+)\.php$/","\\1",$path);
		if(class_exists($name)){
			return eval("return new ".$name."(".implode(",",$argvalue).");");
		}		
		return null;
	}
	
	/**
	 * パッケージパスを別名で作成したインスタンスを生成する
	 *
	 * @param string $path
	 * @return object
	 */
	function alias(){
		$args = func_get_args();
		$path = array_shift($args);
		$realpath = Rhaco::realpath($path);
		if($realpath === false && is_file($path)) $realpath = $path;
		
		if($realpath !== false){
			$src = file_get_contents($realpath);
			$newname = uniqid("CLASS").uniqid();
			
			if(preg_match("/class[\s]+([^\s\{]+)/",$src,$match)){
				$src = str_replace($match[0],"class ".$newname,$src);
				$src = preg_replace("/([^\w])".$match[1]."::/","\\1".$newname."::/",$src);
				$src = preg_replace("/function[\s]+".$match[1]."[\s]*\(/","function ".$newname."(",$src);

				Rhaco::execute($src);
				return eval("return new ".$name."(".implode(",",$argvalue).");");
			}
		}
		return null;
	}
	
	
	/**
	 * Rhaco::importしたライブラリのフルパスを取得
	 */
	function importpath($path){
		/***
		 * Rhaco::import("util.DocTest");
		 * eq(Rhaco::rhacopath()."util/DocTest.php",Rhaco::importpath("util.DocTest"));
		 */
		return (isset($GLOBALS["__RHACO__"]->loaded[$path])) ? $GLOBALS["__RHACO__"]->loaded[$path][2] : null;
	}
	


	
	

	/**
	 * @deprecated 
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	function classname($path){
		/***
		 * eq("DocTest",Rhaco::classname("util.DocTest"));
		 * eq("DocTest",Rhaco::classname("/hoge/moge/dora/DocTest.php"));
		 */
		if(substr($path,-4) == ".php") $path = substr($path,0,-4);
		$list = explode(".",str_replace("/",".",$path));
		return (!empty($list)) ? array_pop($list) : null;
	}

	/**
	 * @deprecated 
	 * Rhaco::importされたパスを$classnameから抽出
	 */
	function get_class($classname){
		/***
		 * Rhaco::obj("util.DocTest");
		 * eq("util.DocTest",Rhaco::get_class("util.DocTest"));
		 */
		if(is_object($classname)) $classname = get_class($classname);
		return $classname;
	}
}
?>
<?php
if(!defined("RHACO_INITIALIZED")){
	if(function_exists("date_default_timezone_get")){
		$timezone = @date_default_timezone_get();
		date_default_timezone_set((empty($timezone) ? "Asia/Tokyo" : $timezone));
	}
	$__file__ = dirname(__FILE__);
	define("RHACO_DIR",str_replace("\\","/",(preg_match("/[^\/]$/",$__file__,$null)) ? $__file__."/" : $__file__));	
	define("RHACO_INITIALIZED",true);
	$GLOBALS["__RHACO__"] = new Rhaco();
	set_error_handler(array($GLOBALS["__RHACO__"],"error"));
	register_shutdown_function(array($GLOBALS["__RHACO__"],"shutdown"));
	ini_set("display_errors","On");
	ini_set("display_startup_errors","On");
	error_reporting((version_compare(phpversion(),"5") == -1) ? E_ALL : E_ALL | E_STRICT);
}
?>