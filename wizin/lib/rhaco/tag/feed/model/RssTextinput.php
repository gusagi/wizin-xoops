<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssTextinput{
	var $title = "";
	var $link = "";
	var $description = "";
	var $name = "";

	function RssTextinput($title="",$name="",$description="",$link=""){
		$this->setTitle($title);
		$this->setLink($link);
		$this->setDescription($description);
		$this->setName($name);
	}

	function set($src){
		/***
		 * $src = '<textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>';
		 * $xml = new RssTextinput();
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("tokushima",$xml->getName());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("rhaco desc",$xml->getDescription());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"textinput")){
			return $this->_set($tag);
		}
		return false;
	}
	function _set($tag){
		$this->setTitle($tag->getInValue("title"));
		$this->setName($tag->getInValue("name"));
		$this->setLink($tag->getInValue("link"));
		$this->setDescription($tag->getInValue("description"));
		return true;
	}
	function get(){
		/***
		 * $xml = new RssTextinput();
		 * eq('<textinput />',$xml->get());
		 * 
		 * $xml->setTitle("rhaco");
		 * eq('<textinput><title>rhaco</title></textinput>',$xml->get());
		 * 
		 * $xml->setName("tokushima");
		 * eq('<textinput><title>rhaco</title><name>tokushima</name></textinput>',$xml->get());
		 * 
		 * $xml->setLink("http://rhaco.org");
		 * eq('<textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link></textinput>',$xml->get());
		 * 
		 * $xml->setDescription("rhaco desc");
		 * eq('<textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>',$xml->get());
		 * 
		 */
		$outTag	= new SimpleTag("textinput");
		$outTag	= $this->_get($outTag);
		return $outTag->get();
	}
	function _get($outTag){
		if($this->title != "") $outTag->addValue(new SimpleTag("title",$this->getTitle()));
		if($this->name != "") $outTag->addValue(new SimpleTag("name",$this->getName()));
		if($this->link != "") $outTag->addValue(new SimpleTag("link",$this->getLink()));
		if($this->description != "") $outTag->addValue(new SimpleTag("description",$this->getDescription()));
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
	function setDescription($value){
		$this->description = SimpleTag::xmltext($value);
	}
	function getDescription(){
		return $this->description;
	}
	function setName($value){
		$this->name = $value;
	}
	function getName(){
		return $this->name;
	}
}