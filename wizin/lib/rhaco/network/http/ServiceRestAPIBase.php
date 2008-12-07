<?php
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("network.http.Browser");
Rhaco::import("lang.ArrayUtil");
/**
 * Restを返すAPIのベース
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class ServiceRestAPIBase{
	var $url = "";
	var $browser;

	function ServiceRestAPIBase($url=""){
		$this->browser = new Browser();
		if(!empty($url)) $this->url = $url;
	}
	
	/**
	 * URLを生成する
	 *
	 * @param array $hash
	 * @param array $addhash
	 * @param string $addurl
	 * @return string
	 */
	function buildUrl($hash=array(),$addhash=array(),$addurl=""){
		/***
		 * $obj = new ServiceRestAPIBase("http://rhaco.org/");
		 * eq("http://rhaco.org/xyz?hoge=abc&def=123",$obj->buildUrl(array("hoge"=>"abc"),array("def"=>123),"xyz"));
		 */
		$query = TemplateFormatter::httpBuildQuery(array_merge(ArrayUtil::arrays($hash),ArrayUtil::arrays($addhash)));
		return $this->url.$addurl.(empty($query) ? "" : "?").$query;
	}
	
	/**
	 * method GETを発行
	 * iscacheの場合取得した結果をキャッシュする
	 * またはキャッシュから取得する
	 *
	 * @param array $hash
	 * @param boolean $iscache
	 * @return string
	 */
	function get($hash,$iscache=false){
		/*** unit("network.http.ServiceRestAPIBaseTest"); */
		$url = $this->buildUrl($hash);

		if($iscache){
			$eurl = base64_encode($url);
			if(Cache::isExpiry($eurl)){
				$src = $this->browser->get($url);
				Cache::set($eurl,$src);
			}
			return Cache::get($eurl);
		}
		return $this->browser->get($url);
	}
	
	/**
	 * method POSTを発行する
	 *
	 * @param unknown_type $variables
	 * @return unknown
	 */
	function post($variables=array()){
		/*** unit("network.http.ServiceRestAPIBaseTest"); */
		$this->browser->setVariable($variables);
		return $this->browser->post($this->buildUrl());
	}
	
	/**
	 * ベーシック認証
	 *
	 * @param string $user
	 * @param string $password
	 */
	function setBasicAuthorization($user,$password){
		$this->browser->setBasicAuthorization($user,$password);
	}
}
?>