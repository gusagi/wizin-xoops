<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssImage{
	var $title = "";
	var $link = "";
	var $url = "";

	function RssImage($title="",$url="",$link=""){
		$this->setTitle($title);
		$this->setLink($link);
		$this->setUrl($url);
	}
	function set($src){
		/***
		 * $src = '<image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>';
		 * $xml = new RssImage();
		 * 
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getUrl());
		 * eq("http://rhaco.org",$xml->getLink());
		 */
		$tag = new SimpleTag();
		$tag->set($src,"image");
		return $this->_set($tag);
	}
	function _set($tag){
		$this->setTitle($tag->getInValue("title"));
		$this->setLink($tag->getInValue("link"));
		$this->setUrl($tag->getInValue("url"));
		return true;
	}
	function get(){
		/***
		 * $xml = new RssImage();
		 * eq('<image />',$xml->get());
		 * 
		 * $xml->setTitle("rhaco");
		 * eq('<image><title>rhaco</title></image>',$xml->get());
		 * 
		 * $xml->setLink("http://rhaco.org");
		 * eq('<image><title>rhaco</title><link>http://rhaco.org</link></image>',$xml->get());
		 * 
		 * $xml->setUrl("http://rhaco.org");
		 * eq('<image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>',$xml->get());
		 */
		$outTag	= new SimpleTag("image");
		$outTag	= $this->_get($outTag);
		return $outTag->get();
	}
	function _get($outTag){
		if($this->title !== "") $outTag->addValue(new SimpleTag("title",$this->getTitle()));
		if($this->link !== "") $outTag->addValue(new SimpleTag("link",$this->getLink()));
		if($this->url !== "") $outTag->addValue(new SimpleTag("url",$this->getUrl()));
		return $outTag;
	}
	function setTitle($value){
		$this->title = $value;
	}
	function getTitle(){
		return $this->title;
	}
	function setLink($value){
		$this->link = $value;
	}
	function getLink(){
		return $this->link;
	}	
	function setUrl($value){
		$this->url = $value;
	}
	function getUrl(){
		return $this->url;
	}
}