<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssSource{
	var $url = "";
	var $value = "";

	function RssSource($url="",$value=""){
		$this->setUrl($url);
		$this->setValue($value);
	}
	function isEmpty(){
		return (empty($this->url) && empty($this->value));
	}
	function set($src){
		/***
		 * $src = '<source url="http://rhaco.org">rhaco source</source>';
		 * 
		 * $xml = new RssSource();
		 * assert($xml->set($src));
		 * eq("http://rhaco.org",$xml->getUrl());
		 * eq("rhaco source",$xml->getValue());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"source")){
			$this->setUrl($tag->getParameter("url"));
			$this->setValue($tag->getValue());
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $xml = new RssSource();
		 * eq('<source />',$xml->get());
		 * 
		 * $xml->setUrl("http://rhaco.org");
		 * eq('<source url="http://rhaco.org" />',$xml->get());
		 * 
		 * $xml->setValue("rhaco source");
		 * eq('<source url="http://rhaco.org">rhaco source</source>',$xml->get());
		 * 
		 */
		$list = array();
		if($this->url != "") $list["url"] = $this->getUrl();
		$outTag	= new SimpleTag("source",$this->getValue(),$list);
		return $outTag->get();
	}	
	function setUrl($value){
		$this->url = $value;
	}
	function getUrl(){
		return $this->url;
	}
	function setValue($value){
		$this->value = SimpleTag::xmltext($value);
	}
	function getValue(){
		return $this->value;
	}
}