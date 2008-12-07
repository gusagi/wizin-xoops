<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssChannel{
	var $title = "";
	var $link = "";
	var $description = "";

	function RssChannel($title="",$description="",$link=""){
		$this->setTitle($title);
		$this->setLink($link);
		$this->setDescription($description);
	}
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <channel>
		 * <title>rhaco</title>
		 * <link>http://rhaco.org</link>
		 * <description>php</description>
		 * </channel>
		 * __XML__;
		 * 
		 * $xml = new RssChannel();
		 * assert($xml->set($src));
		 * 
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("php",$xml->getDescription());
		 */
		if(SimpleTag::setof($tag,$src,"channel")){
			return $this->_set($tag);
		}
		return false;
	}
	function _set($tag){
		$this->setTitle($tag->getInValue("title"));
		$this->setLink($tag->getInValue("link"));
		$this->setDescription($tag->getInValue("description"));
		return true;
	}
	function get(){
		/***
		 * $src = <<< __XML__
		 * <channel>
		 * <title>rhaco</title>
		 * <link>http://rhaco.org</link>
		 * <description>php</description>
		 * </channel>
		 * __XML__;
		 * 
		 * $xml = new RssChannel();
		 * assert($xml->set($src));
		 * 
		 * eq(str_replace("\n","",$src),$xml->get());
		 */
		$outTag	= $this->_get(new SimpleTag("channel"));
		return $outTag->get();
	}
	function _get($outTag){
		if(!empty($this->title)) $outTag->addValue(new SimpleTag("title",$this->getTitle()));
		if(!empty($this->link)) $outTag->addValue(new SimpleTag("link",$this->getLink()));
		if(!empty($this->description)) $outTag->addValue(new SimpleTag("description",$this->getDescription()));		
		return $outTag;
	}
	function setTitle($value){
		$this->title = SimpleTag::xmltext($value);
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