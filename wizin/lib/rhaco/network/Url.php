<?php
/**
 * URLを解析するクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Url{
	/**
	 * $src内のURLを$remotePathをもとにした絶対パスにした文字列を取得
	 *
	 * @param string $src
	 * @param string $remotePath
	 * @return string
	 */
	function parse($src,$remotePath){
		/***
		 * $src  = '<a href="./index.html">a</a>'.
		 * 			'<a href="./hoge/hoge/hoge.html">b</a>'.
		 * 			'<a href="../../boo/test.html">c</a>';
		 * $res  = '<a href="http://www.rhaco.org/hee/hoo/index.html">a</a>'.
		 * 			'<a href="http://www.rhaco.org/hee/hoo/hoge/hoge/hoge.html">b</a>'.
		 * 			'<a href="http://www.rhaco.org/boo/test.html">c</a>';
		 * 
		 * eq($res,Url::parse($src,"http://www.rhaco.org/hee/hoo/"));
		 * 
		 * $src  = '<img src="./index.html" />'.
		 * 			'<img src="./hoge/hoge/hoge.html" />'.
		 * 			'<img src="../../boo/test.html" />';
		 * $res  = '<img src="http://www.rhaco.org/hee/hoo/index.html" />'.
		 * 			'<img src="http://www.rhaco.org/hee/hoo/hoge/hoge/hoge.html" />'.
		 * 			'<img src="http://www.rhaco.org/boo/test.html" />';
		 * 
		 * eq($res,Url::parse($src,"http://www.rhaco.org/hee/hoo/"));
		 */
		$get = array();
		$parse = array();
		$rep = array();

		if(!empty($remotePath)){
			$urlParentList	= array();
			$parseObject		= array();
			$urlList			= parse_url($remotePath);
			$urlList["scheme"]	= (isset($urlList["scheme"])) ? $urlList["scheme"] : "";
			$urlList["host"]	= (isset($urlList["host"])) ? $urlList["host"] : "";
			$urlList["port"]	= (isset($urlList["port"])) ? $urlList["port"] : "";
			$urlList["path"]	= (isset($urlList["path"])) ? $urlList["path"] : "";
			$rootScheme			= $urlList["scheme"];
			$rootPort			= $urlList["port"];
			$rootHost 			= $urlList["host"];

			if(empty($rootHost)){
				$rootHost = $_SERVER["HTTP_HOST"];
			}
			if(empty($rootScheme)){
				$rootScheme = "http";
			}

			$pathArray = split("/",$urlList["path"]);

			$urlParentSize = 0;
			foreach($pathArray as $value){
				if(!empty($value) && false === strpos($value,".")){
					$urlParentList[$urlParentSize] = $value;
					$urlParentSize++;
				}
			}
			if(preg_match_all("/(<[\s]*([a-z]+)[\s]+[^>]+?>)/i",$src,$rep)){
				foreach($rep[1] as $tagKey => $tag){
					$tagName		= strtolower($rep[2][$tagKey]);
					$name		= "";
					$type		= "";
					$path		= "";
					$scheme		= "";

					if(preg_match_all("/[\s](href|src|action|background|name|type|scheme)[\s]*=[\s]*([\"\'])([^\\2]+?)\\2/i",$tag,$parse)){
						foreach($parse[1] as $key => $target){
							$parameter = strtolower($target);

							if($parameter == "name"){
								$name	= $parse[3][$key];
							}else if($parameter == "type"){
								$type	= $parse[3][$key];
							}else if($parameter == "scheme"){
								$scheme	= $parse[3][$key];
							}else{
								if(strpos($parse[3][$key],"'") === false) $path = $parse[3][$key];
							}
						}
					}
					if(!empty($path)){
						if($tagName == "param" || $tagName == "input" || $tagName == "option"){
							if(!(
								($tagName == "param" && $name == "movie") ||
								($tagName == "input" && $type == "image")
							)){
								$path = "";
							}
						}
						if(!empty($path)){
							$parseObject[$tag]["path"] = $path;
							$parseObject[$tag]["scheme"] = $scheme;
						}
					}
				}
			}
			if(preg_match_all("/[\s]url\(([^\$]+?)\)/i",$src,$rep)){
				foreach($rep[1] as $key => $url){
					$parseObject[$rep[0][$key]]["path"] = $url;
					$parseObject[$rep[0][$key]]["scheme"] = "";
				}
			}
			if(!empty($parseObject)){
				foreach($parseObject as $tag => $object){
					if(!preg_match("/(^javascript:)|(^mailto:)|(^[\w]+:\/\/)|(^[#])|(^PHP_TAG_START)|(^\{\\$)/i",$object["path"])){
						$path = "";

						if("/" != substr($object["path"],0,1)){
							$pathSplit	= split("/",$object["path"]);
							$pathList	= $urlParentList;
							$pathSize	= sizeof($urlParentList) - 1;

							foreach($pathSplit as $pathStr){
								if($pathStr == "."){
								}else if($pathStr == ".."){
									$pathSize -= 1;
								}else{
									$pathSize += 1;
									$pathList[$pathSize] = $pathStr;
								}
							}
							for($i=0;$i<=$pathSize;$i++){
								$path .= "/".$pathList[$i];
							}
						}
						if(empty($path)){
							$path = $object["path"];
						}
						if(!empty($object["scheme"])){
							$absolute	= sprintf("%s://%s",$object["scheme"],$rootHost);
						}else{
							$absolute	= sprintf("%s://%s",$rootScheme,$rootHost);

							if(!empty($rootPort)){
								$absolute = sprintf("%s:%s",$absolute,$rootPort);
							}
						}
						$absolute	= $absolute.$path;
						$absolute	= str_replace($object["path"],$absolute,$tag);
						$src		= str_replace($tag,$absolute,$src);
					}
				}
			}
		}
		return $src;
	}

	/**
	 * 絶対パスを取得
	 *
	 * @param string $baseUrl
	 * @param string $targetUrl
	 * @return string
	 */
	function parseAbsolute($baseUrl,$targetUrl){
		/***
			eq("http://www.rhaco.org/doc/ja/index.html",Url::parseAbsolute("http://www.rhaco.org/","/doc/ja/index.html"));
			eq("http://www.rhaco.org/doc/ja/index.html",Url::parseAbsolute("http://www.rhaco.org/","../doc/ja/index.html"));
			eq("http://www.rhaco.org/doc/ja/index.html",Url::parseAbsolute("http://www.rhaco.org/","./doc/ja/index.html"));
			eq("http://www.rhaco.org/doc/ja/index.html",Url::parseAbsolute("http://www.rhaco.org/doc/ja/","./index.html"));
			eq("http://www.rhaco.org/doc/index.html",Url::parseAbsolute("http://www.rhaco.org/doc/ja/","../index.html"));
			eq("http://www.rhaco.org/index.html",Url::parseAbsolute("http://www.rhaco.org/doc/ja/","../../index.html"));
			eq("http://www.rhaco.org/index.html",Url::parseAbsolute("http://www.rhaco.org/doc/ja/","../././.././index.html"));
			eq("/www.rhaco.org/doc/index.html",Url::parseAbsolute("/www.rhaco.org/doc/ja/","../index.html"));
			eq("/www.rhaco.org/index.html",Url::parseAbsolute("/www.rhaco.org/doc/ja/","../../index.html"));
			eq("/www.rhaco.org/index.html",Url::parseAbsolute("/www.rhaco.org/doc/ja/","../././.././index.html"));
			eq("c:/www.rhaco.org/doc/index.html",Url::parseAbsolute("c:/www.rhaco.org/doc/ja/","../index.html"));
			eq("http://www.rhaco.org/index.html",Url::parseAbsolute("http://www.rhaco.org/doc/ja","/index.html"));
		 */
		$baseUrl	= str_replace("\\","/",$baseUrl);
		$targetUrl	= str_replace("\\","/",$targetUrl);
		$isnet		= preg_match("/^[\w]+\:\/\/[^\/]+/",$baseUrl,$basehost);
		$isroot		= (substr($targetUrl,0,1) == "/");
		
		if(empty($baseUrl) || preg_match("/^[a-zA-Z]\:/",$targetUrl) || (!$isnet && $isroot) || preg_match("/^[\w]+\:\/\/[^\/]+/",$targetUrl)) return $targetUrl;
		if($isnet && $isroot && isset($basehost[0])) return $basehost[0].$targetUrl;

		$srclist	= array("://","/./","//");
		$dstlist	= array("#REMOTEPATH#","/","/");
		$psrclist	= array("/^\/(.+)$/","/^(\w):\/(.+)$/");
		$pdstlist	= array("#ROOT#\\1","\\1#WINPATH#\\2","");
		$rsrclist	= array("#REMOTEPATH#","#ROOT#","#WINPATH#");
		$rdstlist	= array("://","/",":/");

		$baseUrl	= preg_replace($psrclist,$pdstlist,str_replace($srclist,$dstlist,$baseUrl));
		$targetUrl	= preg_replace($psrclist,$pdstlist,str_replace($srclist,$dstlist,$targetUrl));
		$basedir	= "";
		$targetdir	= "";
		$rootpath	= "";

		if(strpos($baseUrl,"#REMOTEPATH#")){
			list($rootpath)	= explode("/",$baseUrl);
			$baseUrl			= substr($baseUrl,strlen($rootpath));
			$targetUrl		= str_replace("#ROOT#","",$targetUrl);
		}
		$baseList	= preg_split("/\//",$baseUrl,-1,PREG_SPLIT_NO_EMPTY);
		$targetList	= preg_split("/\//",$targetUrl,-1,PREG_SPLIT_NO_EMPTY);

		for($i=0;$i<sizeof($baseList)-substr_count($targetUrl,"../");$i++){
			if($baseList[$i] != "." && $baseList[$i] != "..") $basedir .= $baseList[$i]."/";
		}
		for($i=0;$i<sizeof($targetList);$i++){
			if($targetList[$i] != "." && $targetList[$i] != "..") $targetdir .= "/".$targetList[$i];
		}
		$targetdir = (!empty($basedir)) ? substr($targetdir,1) : $targetdir;
		$basedir = (!empty($basedir) && substr($basedir,0,1) != "/" && substr($basedir,0,6) != "#ROOT#" && !strpos($basedir,"#WINPATH#")) ? "/".$basedir : $basedir;

		return str_replace($rsrclist,$rdstlist,$rootpath.$basedir.$targetdir);
	}

	/**
	 * 相対パスを取得
	 *
	 * @param string $baseUrl
	 * @param string $targetUrl
	 * @return string
	 */
	function parseRelative($baseUrl,$targetUrl){
		/***
			eq("./overview.html",Url::parseRelative("http://www.rhaco.org/doc/ja/","http://www.rhaco.org/doc/ja/overview.html"));
			eq("../overview.html",Url::parseRelative("http://www.rhaco.org/doc/ja/","http://www.rhaco.org/doc/overview.html"));
			eq("../../overview.html",Url::parseRelative("http://www.rhaco.org/doc/ja/","http://www.rhaco.org/overview.html"));
			eq("../en/overview.html",Url::parseRelative("http://www.rhaco.org/doc/ja/","http://www.rhaco.org/doc/en/overview.html"));
			eq("./doc/ja/overview.html",Url::parseRelative("http://www.rhaco.org/","http://www.rhaco.org/doc/ja/overview.html"));
			eq("./ja/overview.html",Url::parseRelative("http://www.rhaco.org/doc/","http://www.rhaco.org/doc/ja/overview.html"));
			eq("http://www.goesby.com/user.php/rhaco",Url::parseRelative("http://www.rhaco.org/doc/ja/","http://www.goesby.com/user.php/rhaco"));
			eq("./doc/ja/overview.html",Url::parseRelative("/www.rhaco.org/","/www.rhaco.org/doc/ja/overview.html"));
			eq("./ja/overview.html",Url::parseRelative("/www.rhaco.org/doc/","/www.rhaco.org/doc/ja/overview.html"));
			eq("/www.goesby.com/user.php/rhaco",Url::parseRelative("/www.rhaco.org/doc/ja/","/www.goesby.com/user.php/rhaco"));
			eq("./ja/overview.html",Url::parseRelative("c:/www.rhaco.org/doc/","c:/www.rhaco.org/doc/ja/overview.html"));
			eq("c:/www.goesby.com/user.php/rhaco",Url::parseRelative("c:/www.rhaco.org/doc/ja/","c:/www.goesby.com/user.php/rhaco"));
			eq("./Documents/workspace/prhagger/__settings__.php",Url::parseRelative("/Users/kaz/","/Users/kaz/Documents/workspace/prhagger/__settings__.php"));
		 */
		$srclist	= array("://","\\");
		$dstlist	= array("#REMOTEPATH#","/");
		$psrclist	= array("/^\/(.+)$/","/^(\w):\/(.+)$/");
		$pdstlist	= array("#ROOT#\\1","\\1#WINPATH#\\2");
		$rsrclist	= array("#REMOTEPATH#","#ROOT#","#WINPATH#");
		$rdstlist	= array("://","/",":/");

		$baseUrl	= preg_replace($psrclist,$pdstlist,str_replace($srclist,$dstlist,$baseUrl));
		$targetUrl	= preg_replace($psrclist,$pdstlist,str_replace($srclist,$dstlist,$targetUrl));
		$counter	= 0;
		$filename	= "";
		$url		= "";

		if(preg_match("/^(.+\/)[^\/]+\.[^\/]+$/",$baseUrl,$null)){
			$baseUrl = $null[1];
		}
		if(preg_match("/^(.+\/)([^\/]+\.[^\/]+)$/",$targetUrl,$null)){
			$targetUrl	= $null[1];
			$filename	= $null[2];
		}
		if(substr($baseUrl,-1) == "/"){
			$baseUrl = substr($baseUrl,0,-1);
		}
		if(substr($targetUrl,-1) == "/"){
			$targetUrl = substr($targetUrl,0,-1);
		}
		$baseList	= explode("/",$baseUrl);
		$targetList	= explode("/",$targetUrl);
		$baseSize	= sizeof($baseList);		

		if($baseList[0] != $targetList[0]){
			return str_replace($rsrclist,$rdstlist,$targetUrl);
		}
		foreach($baseList as $key => $value){
			if(!isset($targetList[$key]) || $targetList[$key] != $value)	break;
			$counter++;
		}
		for($i=sizeof($targetList)-1;$i>=$counter;$i--){
			$filename = $targetList[$i]."/".$filename;
		}
		if($counter == $baseSize){
			return sprintf("./%s",$filename);
		}
		return sprintf("%s%s",str_repeat("../",$baseSize - $counter),$filename);
	}
}
?>