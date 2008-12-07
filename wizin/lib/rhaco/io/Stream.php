<?php
Rhaco::import("network.http.Http");
Rhaco::import("io.FileUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("resources.Message");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.PermissionException");
/**
 * ストリームを操作するクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Stream{
	/**
	 * ストリームから取得する
	 *
	 * @param string $url
	 * @return string
	 */
	function read($url){
		/*** #pass */
		$src = "";

		if(preg_match("/[\w]+:\/\/[\w]+/",$url)){
			if(preg_match("/php:\/\/(.+)/i",$url,$type)){
				$type = strtolower($type);
				
				if($type == "stdin"){
					$src = Stream::stdin();
				}else if($type == "input"){
					$src = Stream::input();					
				}
			}else{
				$src = Http::get($url);
			}
		}else{
			$src = FileUtil::read($url);
		}
		return StringUtil::encode($src);
	}
	
	/**
	 * ストリームに書き出す
	 *
	 * @param string $url
	 * @param string $value
	 */
	function write($url,$value){
		/*** #pass */
		if(preg_match("/[\w]+:\/\/[\w]+/",$url)){
			if(preg_macth("/php:\/\/(.+)/i",$url,$type)){
				$type = strtolower($type);
				
				if($type == "stdout"){
					Stream::stdout($value);
				}else if($type == "stderr"){
					Stream::stderr($value);					
				}else if($type == "output"){
					Stream::output($value);					
				}
			}
		}else{
			FileUtil::write($url,$value);
		}
	}
	function stdin(){
		/*** #pass */		
		$buffer	= "";
		$fp		= fopen("php://stdin","r");
		
		if(!$fp){
			ExceptionTrigger::raise(new PermissionException("php://stdin"));
			return false;
		}
		while(substr($buffer,-1) != "\n" && substr($buffer,-1) != "\r\n"){
			$buffer .= fgets($fp,4096);
		}
		fclose($fp);

		return rtrim($buffer);
	}
	function input(){
		/*** #pass */
		$buffer	= "";
		$fp	= fopen("php://input","r");

		if(!$fp){
			ExceptionTrigger::raise(new PermissionException("php://input"));
			return false;
		}
		while(!feof($fp)){
			$buffer .= fgets($fp,4096);
		}
		fclose($fp);

		return $buffer;
	}
	function stdout($value){
		/*** #pass */
		$fp		= fopen("php://stdout","w");

		if(!$fp){
			ExceptionTrigger::raise(new PermissionException("php://stdout"));
			return false;
		}
		fwrite($fp,$value);
		fclose($fp);
		return true;
	}	
	function stderr($value){
		/*** #pass */
		$fp		= fopen("php://stderr","w");
		if(!$fp){
			ExceptionTrigger::raise(new PermissionException("php://stderr"));
			return false;
		}
		fwrite($fp,$value);
		fclose($fp);
		return true;
	}
	function output($value){
		/*** #pass */
		$fp		= fopen("php://output","w");
		if(!$fp){
			ExceptionTrigger::raise(new PermissionException("php://output"));
			return false;
		}
		fwrite($fp,$value);
		fclose($fp);
		return true;
	}
}
?>