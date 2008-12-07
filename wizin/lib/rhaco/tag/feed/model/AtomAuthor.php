<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class AtomAuthor{
	var $name = "";
	var $url = "";
	var $email = "";

	function AtomAuthor($name=null,$email=null,$url=null){
		$this->setUrl($url);
		$this->setName($name);
		$this->setEmail($email);
	}
	function isEmpty(){
		return (empty($this->name) && empty($this->url) && empty($this->email));
	}
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <author>
		 * 	<url>http://rhaco.org</url>
		 * 	<name>rhaco</name>
		 * 	<email>rhaco@rhaco.org</email>
		 * </author>
		 * __XML__;
		 * 
		 * $author = new AtomAuthor();
		 * assert($author->set($src));
		 * eq("http://rhaco.org",$author->getUrl());
		 * eq("rhaco",$author->getName());
		 * eq("rhaco@rhaco.org",$author->getEmail());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"author")){
			$this->setUrl($tag->getInValue("url"));
			$this->setName($tag->getInValue("name"));
			$this->setEmail($tag->getInValue("email"));
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $author = new AtomAuthor("rhaco","rhaco@rhaco.org","http://rhaco.org");
		 * eq("<author><url>http://rhaco.org</url><name>rhaco</name><email>rhaco@rhaco.org</email></author>",$author->get());
		 * 
		 * $author = new AtomAuthor("rhaco","rhaco@rhaco.org");
		 * eq("<author><name>rhaco</name><email>rhaco@rhaco.org</email></author>",$author->get());
		 * 
		 * $author = new AtomAuthor("rhaco");
		 * eq("<author><name>rhaco</name></author>",$author->get());
		 * 
		 * $author = new AtomAuthor();
		 * eq("<author />",$author->get());
		 * 
		 */
		$list = array();
		if($this->url != "") $list["url"] = new SimpleTag("url",$this->getUrl());
		if($this->name != "") $list["name"] = new SimpleTag("name",$this->getName());
		if($this->email != "") $list["email"] = new SimpleTag("email",$this->getEmail());

		$outTag	= new SimpleTag("author",$list);
		return $outTag->get();
	}
	
	function setUrl($value){
		$this->url = trim($value);
	}
	function getUrl(){
		return $this->url;
	}
	function setEmail($value){
		$this->email = trim($value);
	}
	function getEmail(){
		return $this->email;
	}
	function setName($value){
		$this->name = trim(SimpleTag::xmltext($value));
	}
	function getName(){
		return $this->name;
	}
}