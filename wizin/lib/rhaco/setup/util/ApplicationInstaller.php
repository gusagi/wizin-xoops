<?php
Rhaco::import("network.Url");
Rhaco::import("io.FileUtil");
Rhaco::import("setup.util.SetupUtil");
/**
 * ApplicationInstaller
 * 
 * 標準アプリケーションのインストーラ
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ApplicationInstaller{
	/**
	 * __init__.phpの生成
	 * $relativePathとの相対パスで定義します
	 *
	 * @param string $relativePath
	 */
	function writeInitFile($relativePath="",$secure=false){
		/*** #pass */
		$relativePath = (preg_match("/^(\/)|(\w\:)/",$relativePath)) ? $relativePath : Rhaco::path($relativePath);
		$definepath = Url::parseRelative($relativePath,Rhaco::path("__settings__.php"));
		$defineString = sprintf("include_once(\"%s\");\n",$definepath);
		if($secure){
			$defineString .= "Rhaco::import(\"network.http.RequestLogin\");\n".
								"RequestLogin::loginRequired();\n";
		}
		if(!FileUtil::exist(FileUtil::path($relativePath,"__init__.php"))){
			FileUtil::write(
				FileUtil::path($relativePath,"__init__.php"),
				sprintf("<?php\n%s\n?>",$defineString)
			);
		}
	}
	
	/**
	 * PHPの構文として返す
	 *
	 * @param string $value
	 * @return PHPの構文とったvalue
	 */
	function getPhp($value){
		return sprintf("<?php\n%s\n?>",$value);
	}
	
	/**
	 * アプリケーション情報
	 * setup.phpで表示される
	 *
	 * @return アプリケーション情報をもつリスト
	 */
	function config(){
		/*** #pass */
		return array(
				);
	}
	
	/**
	 * Rewriteさせるhtaccessを生成する
	 *
	 * @param unknown_type $url
	 * @param unknown_type $path
	 */
	function rewriteFile($url="index.php",$path=""){
		/*** #pass */
		$src = "RewriteEngine On"."\n";
		$src .= "RewriteBase ".preg_replace("/^([\w]+:\/\/[^\/]+)/","",Rhaco::url())."\n";
		$src .= "RewriteCond %{REQUEST_FILENAME} !-f"."\n";
		$src .= 'RewriteRule ^(.+)$ '.$url.'?%{QUERY_STRING}&pathinfo=$1 [L]'."\n";

		if(empty($path)) $path = Rhaco::path();
		FileUtil::write(FileUtil::path($path,".htaccess"),$src);
		Logger::debug(Message::_("generate .htaccess"));
	}
	
	function danyFile($path){
		FileUtil::write(FileUtil::path($path,".htaccess"),"order deny,allow\ndeny from all\n");
	}
}
?>