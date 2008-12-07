<?php
Rhaco::import("io.FileUtil");
Rhaco::import("io.model.File");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("util.Logger");
/**
 * headerユーティリティ
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Header{
	/**
	 * inlineヘッダを発行
	 * @static 
	 * @param io.model.File/string $fileOrUrl
	 * @param string $filename
	 * @param string $contentType
	 * 
	 * image/jpeg
	 * image/gif
	 * audio/mpeg
	 * video/mpeg
	 */	
	function inline($fileOrSrc,$filename="",$contentType=""){
		/*** unit("network.http.HeaderTest"); */
		$file = (Variable::istype("File",$fileOrSrc)) ? $fileOrSrc : (@is_file($fileOrSrc) ? new File($fileOrSrc) : new File(null,$fileOrSrc));
		$name = (!empty($filename)) ? $filename : $file->getName();
		if(empty($name)) $name = uniqid("");
		if(empty($contentType)) $contentType = "image/jpeg";
		header(sprintf("Content-Type: ".$contentType."; name=%s",$name));
		header(sprintf("Content-Disposition: inline; filename=%s",$name));
		if($file->getSize() > 0) header(sprintf("Content-length: %u",$file->getSize()));
		$file->output();
	}
	/**
	 * Attachmentヘッダの発行
	 * 
	 * @static
	 * @param io.model.File/string $fileOrUrl
	 * @param string $filename
	 * @param string $contentType 
	 * 
	 * text/plain
	 * text/richtext
	 * text/html
	 * text/css
	 * text/xml
	 * application/octet-stream
	 * application/xml
	 * 
	 */
	function attach($fileOrSrc,$filename="",$contentType=""){
		/*** unit("network.http.HeaderTest"); */
		$file = (Variable::istype("File",$fileOrSrc)) ? $fileOrSrc : (@is_file($fileOrSrc) ? new File($fileOrSrc) : new File($filename,$fileOrSrc));
		$name = (!empty($filename)) ? $filename : $file->getName();
		if(empty($name)) $name = uniqid("");
		if(empty($contentType)) $contentType = "application/octet-stream";
		header(sprintf("Content-Type: ".$contentType."; name=%s",$name));
		header(sprintf("Content-Disposition: attachment; filename=%s",$name));
		if($file->getSize() > 0) header(sprintf("Content-length: %u",$file->getSize()));
		$file->output();		
	}
	
	/**
	 * リダイレクトする
	 * 
	 * @static 
	 * @param $url
	 * @param $variable
	 * @return void
	 */
	function redirect($url,$variable=array()){
		/*** unit("network.http.HeaderTest"); */
		if(!empty($variable)){
			$requestString = TemplateFormatter::httpBuildQuery($variable);
			if(substr($requestString,0,1) == "?") $requestString = substr($requestString,1);
			$url = sprintf("%s?%s",$url,$requestString);
		}
		Logger::debug(sprintf("Location: %s",$url));
		header(sprintf("Location: %s",$url));
		Rhaco::end();
	}
	
	/**
	 * header情報を書き出す
	 * 
	 * @static 
	 * @param array $variable
	 */
	function write($variable){
		/*** unit("network.http.HeaderTest"); */
		if(is_array($variable)){
			foreach($variable as $key => $value){
				header(sprintf("%s: %s",$key,$value));
			}
		}else{
			header($variable);
		}
	}
}
?>