<?php
Rhaco::import("tag.feed.model.RssImage");
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssImage10 extends RssImage{
	var $about = "";

	function RssImage10($title="",$url="",$link="",$about=""){
		$this->setAbout($about);
		$this->RssImage($title,$url,$link);
	}
	function _set($tag){
		/***
		 * $src = '<image rdf:about="hoge"><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>';
		 * $xml = new RssImage10();
		 * 
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getUrl());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("hoge",$xml->getAbout());
		 * 
		 */
		if(parent::_set($tag)){
			$this->setAbout($tag->getParameter("rdf:about"));
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $xml = new RssImage10();
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
		 * 
		 * $xml->setAbout("hoge");
		 * eq('<image rdf:about="hoge"><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>',$xml->get());
		 * 
		 */
		$outTag = parent::_get($outTag);
		if($this->about != "") $outTag->setParameter("rdf:about",$this->getAbout());
		return $outTag;
	}
	function setAbout($value){
		$this->about = $value;
	}
	function getAbout(){
		return $this->about;
	}
}