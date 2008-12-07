<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssItem{
	var $title = "";
	var $link = "";
	var $description = "";

	function RssItem($title="",$description="",$link=""){
		$this->setTitle($title);
		$this->setLink($link);
		$this->setDescription($description);
	}
	function set($src){
		/***
		 * $src = '<item><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description></item>';
		 * $xml = new RssItem();
		 * 
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("rhaco desc",$xml->getDescription());
		 * 
		 */
		$tag = new SimpleTag();
		$tag->set($src,"item");
		return $this->_set($tag);
	}
	function _set($tag){
		$this->setTitle($tag->getInValue("title"));
		$this->setLink($tag->getInValue("link"));
		$this->setDescription($tag->getInValue("description"));
		return true;
	}
	function get(){
		/***
		 * $xml = new RssItem();
		 * 
		 * eq('<item />',$xml->get());
		 * 
		 * $xml->setTitle("rhaco");
		 * eq('<item><title>rhaco</title></item>',$xml->get());
		 * 
		 * $xml->setLink("http://rhaco.org");
		 * eq('<item><title>rhaco</title><link>http://rhaco.org</link></item>',$xml->get());
		 * 
		 * $xml->setDescription("rhaco desc");
		 * eq('<item><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description></item>',$xml->get());
		 * 
		 */
		$outTag	= new SimpleTag("item");
		$outTag = $this->_get($outTag);
		return $outTag->get();
	}
	function _get($outTag){
		if($this->title != "") $outTag->addValue(new SimpleTag("title",$this->getTitle()));
		if($this->link != "") $outTag->addValue(new SimpleTag("link",$this->getLink()));
		if($this->description != "") $outTag->addValue(new SimpleTag("description",$this->getDescription()));
		return $outTag;
	}
	function setTitle($value){
		$this->title = SimpleTag::xmltext($value);;
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
	function setDescription($value){
		$this->description = SimpleTag::xmltext($value);
	}
	function getDescription(){
		return $this->description;
	}
}