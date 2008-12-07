<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class AtomContent{
	var $type = "text/html";
	var $mode = "";
	var $lang = "";
	var $base = "";
	var $value = "";

	function AtomContent($value=""){
		$this->setValue($value);
	}
	function isEmpty(){
		return (empty($this->mode) && empty($this->lang) && empty($this->base) && empty($this->value));
	}
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * __XML__;
		 * 
		 * $atom = new AtomContent();
		 * assert($atom->set($src));
		 * eq("text/xml",$atom->getType());
		 * eq("abc",$atom->getMode());
		 * eq("ja",$atom->getLang());
		 * eq("base",$atom->getBase());
		 * eq("atom content",$atom->getValue());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"content")){
			$this->setType($tag->getParameter("type","text/html"));
			$this->setMode($tag->getParameter("mode"));
			$this->setLang($tag->getParameter("xml:lang"));
			$this->setBase($tag->getParameter("xml:base"));
			$this->setValue($tag->getValue());
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $xml = new AtomContent("content value");
		 * $xml->setType("text/xml");
		 * eq('<content type="text/xml">content value</content>',$xml->get());
		 * 
		 * $xml->setMode("abc");
		 * eq('<content type="text/xml" mode="abc">content value</content>',$xml->get());
		 * 
		 * $xml->setLang("ja");
		 * eq('<content type="text/xml" mode="abc" xml:lang="ja">content value</content>',$xml->get());
		 * 
		 * $xml->setBase("hoge");
		 * eq('<content type="text/xml" mode="abc" xml:lang="ja" xml:base="hoge">content value</content>',$xml->get());
		 * 
		 */		
		$list = array();
		if($this->type != "") $list["type"] = $this->getType();
		if($this->mode != "") $list["mode"] = $this->getMode();
		if($this->lang != "") $list["xml:lang"] = $this->getLang();
		if($this->base != "") $list["xml:base"] = $this->getBase();
		
		$outTag	= new SimpleTag("content",$this->getValue(),$list);
		return $outTag->get();
	}
	
	function setType($value){
		$this->type = $value;
	}
	function getType(){
		return $this->type;
	}
	function setMode($value){
		$this->mode = $value;
	}
	function getMode(){
		return $this->mode;
	}
	function setLang($value){
		$this->lang = $value;
	}
	function getLang(){
		return $this->lang;
	}
	function setBase($value){
		$this->base = $value;
	}
	function getBase(){
		return $this->base;
	}
	function setValue($value){
		$this->value = SimpleTag::xmltext($value);
	}
	function getValue(){
		return $this->value;
	}
}