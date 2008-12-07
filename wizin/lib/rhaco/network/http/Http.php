<?php
Rhaco::import("resources.Message");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("util.Logger");
Rhaco::import("network.http.Header");
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotConnectionException");
/**
 * HTTPプロトコル
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Http{
	/**
	 * リクエストを発行する
	 *
	 * @param string $url
	 * @param string $method
	 * @param array $headers
	 * @param int $timeout
	 * @param int $blocking
	 * @return array
	 */
	function request($url,$method="GET",$headers=array(),$timeout=5,$blocking=1){
		/***
		 * assert(sizeof(Http::request(Rhaco::url("index.php"))) == 2);
		 * assert(sizeof(Http::request(Rhaco::url("index.php?abc=123&def=456"),"POST",array("rawdata"=>"<test>rhaco</test>","headdata"=>"piyopiyo"))) == 2);
		 * 
		 * unit("network.http.HttpTest");
		 */
		if(!empty($url)){
			if(preg_match("/^([\w]+:\/\/)(.+?):(.+)(@.+)$/",$url,$matchs)) $url = $matchs[1].urlencode($matchs[2]).":".urlencode($matchs[3]).$matchs[4];
			$urlList = parse_url($url);
			$header = "";
			$body = "";
			$cmd = "";
			$isssl = (isset($urlList["scheme"]) && ($urlList["scheme"] == "ssl" || $urlList["scheme"] == "https"));
			$port = (!isset($urlList["port"]) || empty($urlList["port"])) ? (($isssl) ? 443 : 80) : $urlList["port"];
			$path = (!isset($urlList["path"]) || empty($urlList["path"])) ? "/" : $urlList["path"];
			$query = (isset($urlList["query"])) ? sprintf("?%s",$urlList["query"]) : "";
			$host = (isset($urlList["host"])) ? $urlList["host"] : "";
			$fp	= @fsockopen((($isssl) ? "ssl://" : "").$host,$port,$errorno,$errormsg,$timeout);

			if($fp == false || false == stream_set_blocking($fp,$blocking) || false == stream_set_timeout($fp,$timeout)){
				return ExceptionTrigger::raise(new NotConnectionException(Message::_("URL [{1}] {2} {3}",$url,$errormsg,$errorno)));
			}
			$headkeys = array_change_key_case($headers);
			$cmd .= sprintf("%s %s%s HTTP/1.1\r\n",$method,$path,$query);
			$cmd .= sprintf("Host: %s\r\n",$host);
			if(isset($_SERVER["HTTP_USER_AGENT"]) && !array_key_exists("user-agent",$headkeys)) $cmd .= sprintf("User-Agent: %s\r\n",$_SERVER["HTTP_USER_AGENT"]);
			if(isset($_SERVER["HTTP_ACCEPT"]) && !array_key_exists("accept",$headkeys)) $cmd .= sprintf("Accept: %s\r\n",$_SERVER["HTTP_ACCEPT"]);
			if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && !array_key_exists("accept-language",$headkeys)) $cmd .= sprintf("Accept-Language: %s\r\n",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			if(isset($_SERVER["HTTP_ACCEPT_CHARSET"]) && !array_key_exists("accept-charset",$headkeys)) $cmd .= sprintf("Accept-Charset: %s\r\n",$_SERVER["HTTP_ACCEPT_CHARSET"]);

			$cmd .= sprintf("Content-type: %s\r\n",(isset($headers["type"])) ? $headers["type"] : "application/x-www-form-urlencoded");
			$cmd .= sprintf("Connection: Close\r\n");
            
			if(isset($urlList['user']) && isset($urlList['pass'])) {
			    $cmd .= sprintf("Authorization: Basic %s\r\n",base64_encode(sprintf("%s:%s",urldecode($urlList["user"]),urldecode($urlList["pass"]))));
			}
			if(!empty($headers) && is_array($headers)){
				if(isset($headers["agent"])) unset($headers["agent"]);
				if(isset($headers["type"])) unset($headers["type"]);
				foreach($headers as $key => $value){
					if(is_string($value) && $key != "rawdata" && $key != "var") $cmd .= sprintf("%s: %s\r\n",$key,$value);
				}
			}
			$var = "";
			if(isset($headers["var"])) $var = TemplateFormatter::httpBuildQuery($headers["var"]);
			if(isset($headers["rawdata"])) $var = ($var == "") ? urldecode($headers["rawdata"]) : $var."&rawdata=".urldecode($headers["rawdata"]);

			if(!empty($var)){
				$cmd .= "Content-length: ".strlen($var)."\r\n\r\n";
				$cmd .= $var;
			}
			fwrite($fp,$cmd."\r\n");
			stream_set_timeout($fp,$timeout);
			Logger::deep_debug("Http(".$port."): ".$method." ".$host." ".$path.$query);			

			while(!feof($fp) && !preg_match("/\r\n\r\n$/",$header)){
				$header .= fgets($fp,4096);
			}
			if(preg_match("/Content\-Length:[\s]+([a-f0-9]+)\r\n/i",$header,$match)){
				$length	= hexdec($match[1]);

				if($length > 0){
					$rest	= $length % 4096;
					$count	= ($length - $rest) / 4096;

					while(!feof($fp)){
						if($count-- > 0){
							$body .= fread($fp,4096);
						}else{
							$body .= fread($fp,$rest);
							break;
						}
					}
				}
			}else if(preg_match("/Transfer\-Encoding:[\s]+chunked/i",$header)){
				while(!feof($fp)){
					$size = hexdec(trim(fgets($fp,4096)));
					$buffer = "";

					while($size > 0 && strlen($buffer) < $size){
						$value = fgets($fp,$size);
						if($value === feof($fp)) break;
						$buffer .= $value;
					}
					$body .= substr($buffer,0,$size);
				}
			}else{
				while(!feof($fp)) $body .= fread($fp,4096);
			}
			fclose($fp);
			return array($header,$body);
		}
		return array("","");
	}
	
	/**
	 * http requestを発行しbodyを取得する
	 *
	 * @param string $url
	 * @param string $method
	 * @param array $headers
	 * @param int $timeout
	 * @return string
	 */
	function body($url,$method="GET",$headers=array(),$timeout=5){
		/***
		 * assert(sizeof(Http::body(Rhaco::url("index.php"))) == 1);
		 * assert(sizeof(Http::body(Rhaco::url("index.php?abc=123&def=456"),"POST",array("rawdata"=>"<test>rhaco</test>","headdata"=>"piyopiyo"))) == 1);
		 */		
		$result = Http::request($url,$method,$headers,$timeout);
		if(is_bool($result) && $result == false){
			return false;
		}
		list($head,$body) = $result;

		if(!empty($head)){
			$status = Http::parseStatus($head);
			if($status != 200){
				if(Http::isRedirect($redirectUrl,$status,$head)){
					return Http::body($redirectUrl,$method);
				}
				return false;
			}
		}
		return $body;		
	}
	
	/**
	 * ヘッダからstatusコードを取得
	 *
	 * @param string $head
	 * @return int
	 */
	function parseStatus($head){
		/*** eq(404,Http::parseStatus("HTTP/1.1 404 Not Found")); */
		if(preg_match("/HTTP\/.+[\040](\d\d\d)/i",$head,$httpCode)){
			return intval($httpCode[1]);
		}
		return null;
	}
	
	/**
	 * $statusがリダイレクト対象だった場合に$urlにリダイレクト先ＵＲＬを登録する
	 *
	 * @param unknown_type $url
	 * @param int $status
	 * @param string $head
	 */
	function isRedirect(&$url,$status,$head=""){
		switch($status){
			case 300:
			case 301:
			case 302:
			case 307:
				if(preg_match("/Location:[\040](.*)/i",$head,$redirectUrl)){
					$url = preg_replace("/[\r\n]/","",$redirectUrl[1]);
					return true;
				}
		}
		return false;
	}
	
	/**
	 * http requestを発行しheadを取得する
	 *
	 * @param string $url
	 * @param array $headers
	 * @param int $timeout
	 * @return string
	 */
	function head($url,$headers=array(),$timeout=5){
		/***
		 * assert(sizeof(Http::head(Rhaco::url("index.php"))) == 1);
		 */
		$result = Http::request($url,"HEAD",$headers,$timeout);
		if(is_bool($result) && $result == false){
			return false;
		}
		list($head) = $result;
		return $head;
	}
	/**
	 * http requestを発行しmodifiedを取得する
	 *
	 * @param string $url
	 * @param int $time 
	 * @param int $timeout
	 * @return string
	 */	
	function modified($url,$time,$timeout=5){
		return Http::body($url,"GET",array("If-Modified-Since"=>date("r",$time)),$timeout);
	}
	
	/**
	 * REFERERを取得
	 *
	 * @return string
	 */
	function referer(){
		if(preg_match("/:\/\//",$_SERVER["HTTP_REFERER"],$null)){
			return $_SERVER["HTTP_REFERER"];
		}
		return Rhaco::url();
	}
	
	/**
	 * ステータスヘッダを書き出し
	 * @param unknown_type $statuscode
	 */
	function status($statuscode){
		switch($statuscode){
			case 100: Header::write("HTTP/1.1 100 Continue"); break;
			case 101: Header::write("HTTP/1.1 101 Switching Protocols"); break;
			case 200: Header::write("HTTP/1.1 200 OK"); break;
			case 201: Header::write("HTTP/1.1 201 Created"); break;
			case 202: Header::write("HTTP/1.1 202 Accepted"); break;
			case 203: Header::write("HTTP/1.1 203 Non-Authoritative Information"); break;
			case 204: Header::write("HTTP/1.1 204 No Content"); break;
			case 205: Header::write("HTTP/1.1 205 Reset Content"); break;
			case 206: Header::write("HTTP/1.1 206 Partial Content"); break;
			case 300: Header::write("HTTP/1.1 300 Multiple Choices"); break;
			case 301: Header::write("HTTP/1.1 301 MovedPermanently"); break;
			case 302: Header::write("HTTP/1.1 302 Found"); break;
			case 303: Header::write("HTTP/1.1 303 See Other"); break;
			case 304: Header::write("HTTP/1.1 304 Not Modified"); break;
			case 305: Header::write("HTTP/1.1 307 Temporary Redirect"); break;
			case 307: Header::write("HTTP/1.1 404 Not Found"); break;
			case 400: Header::write("HTTP/1.1 400 Bad Request"); break;
			case 401: Header::write("HTTP/1.1 401 Unauthorized"); break;
			case 403: Header::write("HTTP/1.1 403 Forbidden"); break;
			case 404: Header::write("HTTP/1.1 404 Not Found"); break;
			case 405: Header::write("HTTP/1.1 405 Method Not Allowed"); break;
			case 406: Header::write("HTTP/1.1 406 Not Acceptable"); break;
			case 407: Header::write("HTTP/1.1 407 Proxy Authentication Required"); break;
			case 408: Header::write("HTTP/1.1 408 Request Timeout"); break;			
			case 409: Header::write("HTTP/1.1 409 Conflict"); break;
			case 410: Header::write("HTTP/1.1 410 Gone"); break;
			case 411: Header::write("HTTP/1.1 411 Length Required"); break;
			case 412: Header::write("HTTP/1.1 412 Precondition Failed"); break;
			case 413: Header::write("HTTP/1.1 413 Request Entity Too Large"); break;
			case 414: Header::write("HTTP/1.1 414 Request-Uri Too Long"); break;
			case 415: Header::write("HTTP/1.1 415 Unsupported Media Type"); break;
			case 416: Header::write("HTTP/1.1 416 Requested Range Not Satisfiable"); break;
			case 417: Header::write("HTTP/1.1 417 Expectation Failed"); break;
			case 500: Header::write("HTTP/1.1 500 Internal Server Error"); break;			
			case 501: Header::write("HTTP/1.1 501 Not Implemented"); break;
			case 502: Header::write("HTTP/1.1 502 Bad Gateway"); break;
			case 503: Header::write("HTTP/1.1 503 Service Unavailable"); break;
			case 504: Header::write("HTTP/1.1 504 Gateway Timeout"); break;
			case 505: Header::write("HTTP/1.1 505 Http Version Not Supported"); break;
		}
	}
	function rawdata(){
		return file_get_contents("php://input");
	}
	function get($url,$headers=array(),$timeout=5){
		/*** unit("network.http.HttpTest"); */
		return Http::body($url,"GET",$headers,$timeout);
	}
	function post($url,$variables=array(),$headers=array(),$timeout=5){
		/*** unit("network.http.HttpTest"); */
		if(!is_array($headers)) $headers = array();
		if(!empty($variables)) $headers["var"] = ArrayUtil::arrays($variables);
		return Http::body($url,"POST",$headers,$timeout);
	}
}
?>