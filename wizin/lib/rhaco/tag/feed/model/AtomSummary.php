<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class AtomSummary{
	var $type = "text";
	var $lang = "";
	var $value = "";

	function AtomSummary($value=""){
		$this->setValue($value);
	}
	
	function isEmpty(){
		return (empty($this->lang) && empty($this->value));
	}
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <summary type="xml" xml:lang="ja">summary test</summary>
		 * __XML__;
		 * 
		 * $xml = new AtomSummary();
		 * assert($xml->set($src));
		 * eq("xml",$xml->getType());
		 * eq("ja",$xml->getLang());
		 * eq("summary test",$xml->getValue());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"summary")){
			$this->setType($tag->getParameter("type","text"));
			$this->setLang($tag->getParameter("xml:lang"));
			$this->setValue($tag->getValue());
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $xml = new AtomSummary("summary test");
		 * eq('<summary type="text">summary test</summary>',$xml->get());
		 * 
		 * $xml->setType("xml");
		 * eq('<summary type="xml">summary test</summary>',$xml->get());
		 * 
		 * $xml->setLang("ja");
		 * eq('<summary type="xml" xml:lang="ja">summary test</summary>',$xml->get());
		 *
		 */
		$list = array();
		if($this->type != "") $list["type"] = $this->getType();
		if($this->lang != "") $list["xml:lang"] = $this->getLang();

		$outTag	= new SimpleTag("summary",$this->getValue(),$list);
		return $outTag->get();
	}
	
	function setType($value){
		$this->type = $value;
	}
	function getType(){
		return $this->type;
	}
	function setLang($value){
		$this->lang = $value;
	}
	function getLang(){
		return $this->lang;
	}
	function setValue($value){
		$this->value = SimpleTag::xmltext($value);
	}
	function getValue(){
		return $this->value;
	}
}