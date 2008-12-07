<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class AtomLink{
	var $rel = "";
	var $type = "";
	var $href = "";

	function AtomLink($href="",$rel="",$type=""){
		$this->setHref($href);
		$this->setRel($rel);
		$this->setType($type);
	}
	function isEmpty(){
		return (empty($this->rel) && empty($this->type) && empty($this->href));
	}
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <link href="http://rhaco.org" rel="abc" type="xyz" />
		 * __XML__;
		 * 
		 * $xml = new AtomLink();
		 * assert($xml->set($src));
		 * eq("http://rhaco.org",$xml->getHref());
		 * eq("abc",$xml->getRel());
		 * eq("xyz",$xml->getType());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"link")){
			$this->setHref($tag->getParameter("href"));
			$this->setRel($tag->getParameter("rel"));
			$this->setType($tag->getParameter("type"));
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $xml = new AtomLink("http://rhaco.org","abc","xyz");
		 * eq('<link href="http://rhaco.org" rel="abc" type="xyz" />',$xml->get());
		 * 
		 * $xml = new AtomLink("http://rhaco.org","abc");
		 * eq('<link href="http://rhaco.org" rel="abc" />',$xml->get());
		 * 
		 * $xml = new AtomLink("http://rhaco.org");
		 * eq('<link href="http://rhaco.org" />',$xml->get());
		 * 
		 * $xml = new AtomLink();
		 * eq('<link />',$xml->get());
		 * 
		 */
		$list = array();
		if($this->href != "") $list["href"] = $this->getHref();
		if($this->rel != "") $list["rel"] = $this->getRel();
		if($this->type != "") $list["type"] = $this->getType();
		
		$outTag	= new SimpleTag("link","",$list);
		return $outTag->get();
	}	
	function setHref($value){
		$this->href = $value;
	}
	function getHref(){
		return $this->href;
	}
	function setRel($value){
		$this->rel = $value;
	}
	function getRel(){
		return $this->rel;
	}
	function setType($value){
		$this->type = $value;
	}
	function getType(){
		return $this->type;
	}
}