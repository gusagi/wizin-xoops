<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssEnclosure{
	var $url = "";
	var $length = null;
	var $type = "";

	function RssEnclosure($url="",$type="",$length=null){
		$this->setUrl($url);
		$this->setType($type);
		$this->setLength($length);
	}
	function isEmpty(){
		return (empty($this->url) && empty($this->length) && empty($this->type));
	}
	function set($src){
		/***
		 * $src = '<enclosure url="http://rhaco.org" type="hoge" length="10" />';
		 * $xml = new RssEnclosure();
		 * 
		 * assert($xml->set($src));
		 * eq("http://rhaco.org",$xml->getUrl());
		 * eq("hoge",$xml->getType());
		 * eq(10,$xml->getLength());
		 */
		if(SimpleTag::setof($tag,$src,"enclosure")){
			$this->setUrl($tag->getParameter("url"));
			$this->setType($tag->getParameter("type"));
			$this->setLength($tag->getParameter("length"));
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $xml = new RssEnclosure();
		 * 
		 * eq('<enclosure />',$xml->get());
		 * 
		 * $xml->setUrl("http://rhaco.org");
		 * eq('<enclosure url="http://rhaco.org" />',$xml->get());
		 * 
		 * $xml->setType("hoge");
		 * eq('<enclosure url="http://rhaco.org" type="hoge" />',$xml->get());
		 * 
		 * $xml->setLength(10);
		 * eq('<enclosure url="http://rhaco.org" type="hoge" length="10" />',$xml->get());
		 * 
		 * 
		 */
		$list = array();
		if($this->url !== "") $list["url"] = $this->getUrl();
		if($this->type !== "") $list["type"] = $this->getType();
		if($this->length !== null) $list["length"] = $this->getLength();
		$outTag	= new SimpleTag("enclosure","",$list);
		return $outTag->get();
	}
	
	function setUrl($value){
		$this->url = $value;
	}
	function getUrl(){
		return $this->url;
	}
	function setType($value){
		$this->type = $value;
	}
	function getType(){
		return $this->type;
	}
	function setLength($value){
		if(!empty($value)) $this->length = intval($value);
	}
	function getLength(){
		return $this->length;
	}
}