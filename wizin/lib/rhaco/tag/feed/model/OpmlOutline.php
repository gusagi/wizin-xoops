<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class OpmlOutline{
	var $text = "";
	var $type = "";
	var $value = "";
	
	var $isComment = false;
	var $isBreakpoint = false;	
		
	var $htmlUrl = "";
	var $xmlUrl = "";
	var $title = "";
	var $description = "";
	var $outlineList = array();

	var $tags = "";

	function set($src,$tags=""){
		/***
		 * $src = '<outline title="りあふ の にっき" htmlUrl="http://riaf.g.hatena.ne.jp/riaf/" type="rss" xmlUrl="http://riaf.g.hatena.ne.jp/riaf/rss2" />';
		 * $xml = new OpmlOutline();
		 * assert($xml->set($src));
		 * eq("りあふ の にっき",$xml->getTitle());
		 * eq("http://riaf.g.hatena.ne.jp/riaf/rss2",$xml->getXmlUrl());
		 * eq("http://riaf.g.hatena.ne.jp/riaf/",$xml->getHtmlUrl());
		 * eq("rss",$xml->getType());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"outline")){
			$this->setText($tag->getParameter("text"));
			$this->setType($tag->getParameter("type"));
			$this->setIsComment($tag->getParameter("isComment",false));
			$this->setIsBreakpoint($tag->getParameter("isBreakpoint",false));		
	
			$this->setHtmlUrl($tag->getParameter("htmlUrl"));
			$this->setXmlUrl($tag->getParameter("xmlUrl"));
			$this->setTitle($tag->getParameter("title"));
			$this->setDescription($tag->getParameter("description"));
	
			if($this->getTitle() == ""){
				$tags = trim($tags)." ".trim($this->getText());
			}else{
				$this->setTags($tags);
			}
			foreach($tag->getIn("outline") as $outlinetag){
				$outline = new OpmlOutline();
				$outline->set($outlinetag->getPlain(),$tags);
				$this->outlineList[] = $outline;
			}
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $src = '<outline title="りあふ の にっき" htmlUrl="http://riaf.g.hatena.ne.jp/riaf/" type="rss" xmlUrl="http://riaf.g.hatena.ne.jp/riaf/rss2" />';
		 * $xml = new OpmlOutline();
		 * assert($xml->set($src));
		 * 
		 * eq($src,$xml->get());
		 */
		$outTag	= new SimpleTag("outline");
		if($this->getTitle() != "") $outTag->setParameter("title",$this->getTitle());
		if($this->getHtmlUrl() != "") $outTag->setParameter("htmlUrl",$this->getHtmlUrl());
		if($this->getType() != "") $outTag->setParameter("type",$this->getType());
		if($this->getXmlUrl() != "") $outTag->setParameter("xmlUrl",$this->getXmlUrl());
		if($this->getIsComment() != "") $outTag->setParameter("isComment",$this->getIsComment());
		if($this->getIsBreakpoint() != "") $outTag->setParameter("isBreakpoint",$this->getIsBreakpoint());
		if($this->getText() != "") $outTag->setParameter("text",$this->getText());
		if($this->getDescription() != "") $outTag->setParameter("description",$this->getDescription());
		if($this->getTags() != "") $outTag->setParameter("tags",$this->getTags());

		$outTag->addValue($this->getValue());
		
		foreach(ArrayUtil::arrays($this->outlineList) as $outline){
			$outTag->addValue($outline->get());
		}
		return $outTag->get();
	}
	function setText($value){
		$this->text = SimpleTag::xmltext($value);
	}
	function getText(){
		return $this->text;
	}
	function setType($value){
		$this->type = $value;
	}
	function getType(){
		return $this->type;
	}
	function setIsComment($value){
		$this->isComment = Variable::bool($value);
	}
	function getIsComment(){
		return $this->isComment;
	}
	function setIsBreakpoint($value){
		$this->isBreakpoint = Variable::bool($value);
	}
	function getIsBreakpoint(){
		return $this->isBreakpoint;
	}
	function setValue($value){
		$this->value = SimpleTag::xmltext($value);
	}
	function getValue(){
		return $this->value;
	}
	function setHtmlUrl($value){
		$this->htmlUrl = $value;
	}
	function getHtmlUrl(){
		return $this->htmlUrl;
	}
	function setXmlUrl($value){
		$this->xmlUrl = $value;
	}
	function getXmlUrl(){
		return $this->xmlUrl;
	}
	function setTitle($value){
		$this->title = SimpleTag::xmltext($value);
	}
	function getTitle(){
		return $this->title;
	}
	function setDescription($value){
		$this->description = SimpleTag::xmltext($value);
	}
	function getDescription(){
		return $this->description;
	}
	function getHtmlOutlines(){
		$list = array();
		if($this->getHtmlUrl() != "") $list[] = $this;
		foreach($this->outlineList as $outline) $list = array_merge($list,$outline->getHtmlOutlines());
		return $list;
	}
	function getXmlOutlines(){
		$list = array();
		if($this->getXmlUrl() != "") $list[] = $this;
		foreach($this->outlineList as $outline) $list = array_merge($list,$outline->getXmlOutlines());
		return $list;
	}
	function getItems(){
		$items = $this->getItem();
		foreach($this->getBlock() as $block) $items = array_merge($items,$block->getItems());
		return $items;
	}
	function setTags($value){
		if($this->tags != "") $this->tags = trim($this->tags)." ";
		$this->tags .= trim($value);
	}
	function getTags(){
		return $this->tags;
	}
}
?>