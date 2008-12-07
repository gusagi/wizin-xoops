<?php
Rhaco::import("network.http.Http");
Rhaco::import("lang.StringUtil");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Atom{
	function Atom(){
	}
	function set($src){
		/***
		 * $xml = new Atom();
		 * assert(!$xml->set(""));
		 */
		unset($src);
		return false;
	}
	function get(){
		/***
		 * $xml = new Atom();
		 * eq("",$xml->get());
		 */		
		return "";
	}

	/**
	 * urlから開く
	 *
	 * @param string $url
	 */
	function open($url){
		$this->set(Http::body($url));
	}
	
	/**
	 * 出力
	 *
	 * @param string $name
	 */
	function output($name=""){
		/*** unit("tag.feed.atomTest"); */
		header(sprintf("Content-Type: application/atom+xml; name=%s",(empty($name)) ? uniqid("") : $name));
		print("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n".StringUtil::encode($this->get()));
		Rhaco::end();
	}
}
?>