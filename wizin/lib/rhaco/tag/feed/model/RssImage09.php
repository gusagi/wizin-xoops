<?php
Rhaco::import("tag.feed.model.RssImage");
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssImage09 extends RssImage{
	var $width = null;
	var $height = null;	

	function _set($tag){
		/***
		 * $src = '<image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url><width>100</width><height>200</height></image>';
		 * $xml = new RssImage09();
		 * 
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getUrl());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq(100,$xml->getWidth());
		 * eq(200,$xml->getHeight());
		 * 
		 */
		if(parent::_set($tag)){
			$this->setWidth($tag->getInValue("width"));
			$this->setHeight($tag->getInValue("height"));
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $xml = new RssImage09();
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
		 * $xml->setWidth(100);
		 * eq('<image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url><width>100</width></image>',$xml->get());
		 * 
		 * $xml->setHeight(200);
		 * eq('<image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url><width>100</width><height>200</height></image>',$xml->get());
		 * 
		 */
		$outTag = parent::_get($outTag);
		if($this->width !== null) $outTag->addValue(new SimpleTag("width",$this->getWidth()));
		if($this->height !== null) $outTag->addValue(new SimpleTag("height",$this->getHeight()));
		return $outTag;
	}
	function setWidth($value){
		if(!empty($value)) $this->width = intval($value);
	}
	function getWidth(){
		return $this->width;
	}
	function setHeight($value){
		if(!empty($value)) $this->height = intval($value);
	}
	function getHeight(){
		return $this->height;
	}
}