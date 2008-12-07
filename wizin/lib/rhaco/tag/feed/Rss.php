<?php
Rhaco::import("network.http.Http");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.feed.model.RssChannel");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Rss{
	var $channel;
		
	function Rss(){
	}
	function open($url){
		$this->set(Http::body($url));
	}
	function set($src){
		/***
		 * $xml = new Rss();
		 * assert(!$xml->set(""));
		 */
		unset($src);
		return false;
	}
	function get(){
		/***
		 * $xml = new Rss();
		 * eq('',$xml->get());
		 */
		return "";
	}
	function setChannel($titleOrChannel,$description="",$link=""){
		$this->channel = (Variable::istype("RssChannel",$titleOrChannel)) ? $titleOrChannel : new RssChannel($titleOrChannel,$description,$link);
	}
	function getChannel(){
		return $this->channel;
	}
	
	/**
	 * 出力する
	 *
	 */
	function output($name=""){
		/*** unit("tag.feed.rssTest"); */
		header(sprintf("Content-Type: application/rss+xml; name=%s",(empty($name)) ? uniqid("") : $name));
		print("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n".StringUtil::encode($this->get()));
		Rhaco::end();
	}
}
?>