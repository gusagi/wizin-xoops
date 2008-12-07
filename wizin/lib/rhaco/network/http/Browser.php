<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("network.http.Http");
Rhaco::import("network.Url");
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
/**
 * ブラウザ
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class Browser{
	var $cookie = array();
	var $variables = array();
	var $agent = "Rhaco Browser";
	var $user = "";
	var $password = "";
	var $form = array();
	var $status = 200;
	var $bodys = array();
	var $headers = array();
	var $urls = array();
	var $requestHeader = array();
	var $history = 20;

	/**
	 * POSTを発行
	 *
	 * @param string $url
	 * @return string
	 */
	function post($url,$variables=array()){
		/*** unit("network.http.BrowserTest"); */
		$this->setVariable($variables);
		return $this->_request($url,"POST");
	}

	/**
	 * GETを発行
	 *
	 * @param string $url
	 * @return string
	 */
	function get($url,$variables=array()){
		/*** unit("network.http.BrowserTest"); */
		$this->setVariable($variables);
		return $this->_request($url,"GET");
	}

	/**
	 * agentを定義する
	 *
	 * @param string $agent
	 */
	function setAgent($agent){
		$this->agent = $agent;
	}
	/**
	 * ベーシック認証用ユーザ＆パスワードの設定
	 *
	 * @param string $user
	 * @param string $password
	 */
	function setBasicAuthorization($user,$password){
		$this->user		= $user;
		$this->password	= $password;
	}
	function setHistory($size){
		$this->history = intval($size);
	}
	
	/**
	 * 追加ヘッダを設定する
	 *
	 * @param array $headers ハッシュで定義名=>値
	 */
	function setRequestHeader($headers){
		$this->requestHeader = ArrayUtil::arrays($headers);
	}

	/**
	 * 送信する変数をセット
	 * getまたはpost後にクリアされる
	 *
	 */
	function setVariable(){
		$argList = func_get_args();

		if(is_array($argList[0])){
			foreach($argList[0] as $key => $value){
				$this->variables[$key] = $value;
			}
		}else{
			$this->variables[$argList[0]] = $argList[1];
		}
	}
	function status(){
		/*** unit("network.http.BrowserTest"); */
		return $this->status;
	}
	function body($num=0){
		/*** unit("network.http.BrowserTest"); */		
		if($num < 0) $num *= -1;
		return isset($this->bodys[$num]) ? $this->bodys[$num] : null;
	}
	function header($num=0){
		/*** unit("network.http.BrowserTest"); */
		if($num < 0) $num *= -1;		
		return isset($this->headers[$num]) ? $this->headers[$num] : null;
	}
	function url($num=0){
		/*** unit("network.http.BrowserTest"); */
		if($num < 0) $num *= -1;		
		return isset($this->urls[$num]) ? $this->urls[$num] : null;
	}

	function _request($url,$method="GET"){
		$cookies	= "";
		$variables	= "";
		$headers	= $this->requestHeader;

		foreach(ArrayUtil::arrays($this->cookie) as $name => $cookie){
			$cookies .= sprintf("%s=%s; ",$name,$cookie);
		}
		if(!empty($this->variables)){
			if(strtoupper($method) == "GET"){
				$url = (strpos($url,"?") === false) ? $url."?" : $url."&";
				$url .= TemplateFormatter::httpBuildQuery($this->variables);
			}else{
				$headers["Content-Length"] = strlen($variables);
				$headers["var"]	= $this->variables;
			}
		}
		if(!empty($cookies)) $headers["Cookie"] = $cookies;
		if(!empty($this->agent)) $headers["User-Agent"] = $this->agent;

		if(!empty($this->user)){
			if(preg_match("/^([\w]+:\/\/)(.+)$/",$url,$match)){
				$url = $match[1].$this->user.":".$this->password."@".$match[2];
			}else{
				$url = "http://".$this->user.":".$this->password."@".$url;
			}
		}
		list($head,$body) = Http::request($url,$method,$headers);
		$this->_parseHeader($head);
		$this->_parseBody($body,$url);

		array_unshift($this->bodys,$body);
		array_unshift($this->headers,$head);
		array_unshift($this->urls,$url);

		if(count($this->bodys) > $this->history){
			array_pop($this->bodys);
			array_pop($this->headers);
			array_pop($this->urls);
		}
		if(!empty($head)){
			$this->status = Http::parseStatus($head);
			
			if($this->status != 200){
				if(Http::isRedirect($redirectUrl,$this->status,$head)){
					return $this->_request($redirectUrl,$method);
				}
				return null;
			}else if(SimpleTag::setof($tag,$body,"head")){
				foreach($tag->getIn("meta") as $meta){
					if(strtolower($meta->param("http-equiv")) == "refresh"){
						if(preg_match("/^[\d]+;url=(.+)$/i",$meta->param("content"),$refresh)){
							$this->variables = array();
							return $this->get(URL::parseAbsolute(dirname($url),$refresh[1]));
						}
					}
				}
			}
		}
		$this->variables = array();
		return $body;
	}
	function submit($form=0,$submit=null){
		/*** unit("network.http.BrowserTest"); */
		foreach($this->form as $key => $f){
			if($f["name"] == $form || $key == $form){
				$form = $key;
				break;
			}
		}
		if(!isset($this->form[$form])) return ExceptionTrigger::raise(new NotFoundException($form));

		$inputcount = 0;
		$onsubmit = false;
		foreach($this->form[$form]["element"] as $element){
			switch($element["type"]){					
				case "hidden":
				case "textarea":
					if(!array_key_exists($element["name"],$this->variables)) $this->setVariable($element["name"],$element["value"]);
					break;
				case "text":
				case "password":
					$inputcount++;
					if(!array_key_exists($element["name"],$this->variables)) $this->setVariable($element["name"],$element["value"]); break;							
					break;
				case "checkbox":
				case "radio":
					if($element["selected"] !== false){
						if(!array_key_exists($element["name"],$this->variables)) $this->setVariable($element["name"],$element["value"]);
					}
					break;
				case "submit":
				case "image":
					if(($submit === null && $onsubmit === false) || $submit == $element["name"]){
						$onsubmit = true;
						if(!array_key_exists($element["name"],$this->variables)) $this->setVariable($element["name"],$element["value"]);
						break;
					}
					break;
				case "select":
					if(!array_key_exists($element["name"],$this->variables)){
						if($element["multiple"]){
							$list = array();
							foreach($element["value"] as $option){
								if($option["selected"]) $list[] = $option["value"];
							}
							$this->setVariable($element["name"],$list);
						}else{
							foreach($element["value"] as $option){
								if($option["selected"]){
									$this->setVariable($element["name"],$option["value"]);
								}
							}
						}
					}
					break;
				case "button":
					break;
			}
		}
		if($onsubmit || $inputcount == 1){
			return ($this->form[$form]["method"] == "post") ? 
						$this->post($this->form[$form]["action"]) :
						$this->get($this->form[$form]["action"]);
		}
		return false;
	}
	function _parseBody($body,$url){
		$tag = SimpleTag::anyhow($body);
		$this->form = array();
			
		foreach($tag->getIn("form") as $key => $formtag){
			$form = array();

			$form["name"] = $formtag->param("name",$formtag->param("id",$key));
			$form["action"] = URL::parseAbsolute(dirname($url),$formtag->param("action",$url));
			$form["method"] = strtolower($formtag->param("method","get"));
			$form["element"] = array();
			$form["multiple"] = false;
			
			foreach($formtag->getIn("input") as $count => $input){
				$form["element"][] = array("name"=>$input->param("name",$input->param("id","input_".$count)),
											"type"=>strtolower($input->param("type","text")),
											"value"=>TemplateFormatter::htmldecode($input->param("value")),
											"selected"=>("selected" === strtolower($input->param("checked",$input->attr("checked"))))
										);
			}
			foreach($formtag->getIn("textarea") as $input){
				$form["element"][] = array("name"=>$input->param("name",$input->param("id","textarea_".$count)),
											"type"=>"textarea",
											"value"=>TemplateFormatter::htmldecode($input->getValue()),
											"selected"=>true
									);
			}
			foreach($formtag->getIn("select") as $count => $input){
				$options = array();
				foreach($input->getIn("option") as $count => $option){
					$options[] = array("value"=>TemplateFormatter::htmldecode($option->param("value",$option->getValue())),
										"selected"=>("selected" == strtolower($option->param("selected",$option->attr("selected"))))
									);
				}
				$form["element"][] = array("name"=>$input->param("name",$input->param("id","select_".$count)),
											"type"=>"select",
											"value"=>$options,
											"selected"=>true,
											"multiple"=>("multiple" == strtolower($input->param("multiple",$input->attr("multiple"))))
									);
			}
			$this->form[] = $form;
		}
	}
	function _parseHeader($header){
		if(preg_match_all("/Set-Cookie: (.+)/i",$header,$matchs)){
			$unsetcookie = array();
			$setcookie = array();
			
			foreach($matchs[1] as $cookies){
				foreach(explode(";",$cookies) as $cookie){
					$cookie = trim($cookie);

					if(preg_match("/^(.+?)=(.+)$/",$cookie,$match)){
						if(strtolower($match[1]) == "expires" && DateUtil::parseString($match[1]) < time()){
							$unsetcookie[$cookies] = 1;
						}else{
							$setcookie[$cookies][$match[1]] = $match[2];
						}
					}
				}
			}
			foreach($setcookie as $cookies){
				foreach($cookies as $cookie => $value) $this->cookie[$cookie] = $value;
			}
			foreach($unsetcookie as $cookies => $value) unset($this->cookie[$cookie]);
		}
	}
}
?>