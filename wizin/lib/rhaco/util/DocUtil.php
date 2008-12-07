<?php
Rhaco::import("lang.StringUtil");
Rhaco::import("io.FileUtil");
Rhaco::import("util.model.DocFunction");
/**
 * #ignore
 * DocUtil
 * 
 * ソースドキュメントを操作します
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008- rhaco project. All rights reserved.
 */
class DocUtil{
	var $path;
	var $name;
	var $author;
	var $version;
	var $type;
	var $extends;
	var $description;
	var $function = array();

	/**
	 * $path内の.phpファイルを解析してドキュメント配列を返します
	 * 
	 * @static 
	 * @param $path 対象のフォルダのフルパス
	 * @return array
	 */
	function parse($path){
		$result = array();

		if(is_dir($path)){
			foreach(FileUtil::find("/\.php$/",$path,true) as $file){
				if(!in_array($file->getName(),array("__settings__.php","__init__.php","setup.php"))){
					$result[] = new DocUtil($file->read(),str_replace($path,"",$file->getFullname()));
				}
			}
		}else if(is_file($path)){
			$file = new File($path);
			$result[] = new DocUtil($file->read(),$path);			
		}
		return $result;
	}
	
	function DocUtil($src,$path){
		$this->path = $path;
		$this->name = preg_replace("/([^\/]+)\.php$/","\\1",$this->path);
		
		if(preg_match("/class([\r\n\t\s\w]+)\{/",$src,$matches,PREG_OFFSET_CAPTURE)){
			$this->type = "class";
			$this->name = trim($matches[1][0]);
			
			if(preg_match("/^(.+)[\s]+extends[\s]+(.+)$/",$this->name,$match)){
				$this->name = $match[1];
				$this->extends = $match[2];				
			}
			if(preg_match("/(\/\*\*.+?\*\/)[\s]+class[\r\n\t\s\w]+\{/s",$src,$match)){
				$this->description = StringUtil::comments($match[1]);
				if(preg_match("/@author(.+)/",$this->description,$m)){
					$this->author = trim($m[1]);
				}
				if(preg_match("/@version (.+)/",$this->description,$m)){
					$this->version = trim($m[1]);
				}
			}
			$src = substr($src,$matches[0][1] + strlen($matches[0][0]));
		}else{
			if(preg_match("/(\/\*\*.+?\*\/)/s",$src,$match)){
				$this->description = StringUtil::comments($match[1]);
			}
		}

		$src = str_replace(array("\\\'","\\\""),array("__QUOTE__","__DQUOTE__"),$src);
		$repcount = 0;
		$search = $replace = array();
		
		while(preg_match("/([\"\']).+?\\1/",$src,$match)){
			$repcount++;
			$search[] = $match[0];
			$replace[] = "__REP__".$repcount."__";
			$src = str_replace($match[0],"__REP__".$repcount."__",$src);
		}
		$size = strlen($src);
		$start = $end = 0;
		$value = "";	
		
		for($i=0;$i<$size;$i++){
			$char = $src[$i];
	
			if($char == "{"){
				$start++;
			}else if($char == "}"){
				$end++;
			}
			$value .= $char;
			if($start > 0 && $start == $end){
				$start = $end = 0;
				if(!empty($replace)) $value = trim(str_replace($replace,$search,$value));
				$obj = null;

				if(preg_match("/^(\/\*\*.+?\*\/)[\s]+function([\s\w]+)\((.+)$/s",$value,$match)){
					$obj = new DocFunction($match[2],$match[3],$match[1]);
				}else if(preg_match("/[\s]{0,1}function([\s\w]+)\((.+)$\(/s",$value,$match)){
					$obj = new DocFunction($match[1],$match[2]);
				}
				if($obj !== null && !$obj->isLocal()){
					$this->function[] = $obj;
				}
				$value = "";
			}
		}	
	}
	function isSubclass(){
		return !empty($this->extends);
	}
}
?>