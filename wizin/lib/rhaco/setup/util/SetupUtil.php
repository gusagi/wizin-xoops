<?php
Rhaco::import("network.http.Request");
Rhaco::import("network.http.Header");
Rhaco::import("io.FileUtil");
/**
 * SetupUtil
 * 
 * セットアップで使用するユーティリティ
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class SetupUtil{
	function template($path=""){
		/*** #pass */
		return FileUtil::path(SetupUtil::resource("templates"),$path);
	}
	function resource($path=""){
		/*** #pass */
		return FileUtil::path(FileUtil::path(Rhaco::rhacopath(),"setup/resources/"),$path);
	}
	
	function getDefinedVariableName($path){
		$result = array();
		foreach(FileUtil::ls($path,true) as $file){
			if(preg_match_all("/Rhaco::(get|is|set|add)Variable\(([\"\'])(.+?)[\\2]/s",$file->read(),$match)){
				$result = array_merge($result,$match[3]);
			}
		}
		return $result;
	}
}
?>