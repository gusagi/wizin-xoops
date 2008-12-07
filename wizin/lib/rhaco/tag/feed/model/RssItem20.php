<?php
Rhaco::import("tag.feed.model.RssItem");
Rhaco::import("tag.feed.model.RssEnclosure");
Rhaco::import("tag.feed.model.RssSource");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.DateUtil");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssItem20 extends RssItem{
	var $author = "";
	var $category = "";
	var $comments = "";
	var $pubDate = null;
	
	var $guid = "";
	var $source = null;
	var $enclosure = null;
	
	function _set($tag){
		/***
		 * $src = '<item>'.
		 * 			'<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>'.
		 * 			'<author>tokushima</author><category>php</category><comments>hoge</comments><pubDate>Wed, 10 Oct 2007 10:10:10 +0900</pubDate><guid>123</guid>'.
		 * 			'<enclosure url="http://rhaco.org" type="hoge" length="10" />'.
		 * 			'<source url="http://rhaco.org">rhaco source</source>'.
		 * 			'</item>';
		 * $xml = new RssItem20();
		 * 
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("rhaco desc",$xml->getDescription());
		 * 
		 * eq("tokushima",$xml->getAuthor());
		 * eq("php",$xml->getCategory());
		 * eq("hoge",$xml->getComments());
		 * eq("Wed, 10 Oct 2007 10:10:10 +0900",$xml->getPubDate());
		 * eq("123",$xml->getGuid());
		 * assert(Variable::istype("RssEnclosure",$xml->getEnclosure()));
		 * assert(Variable::istype("RssSource",$xml->getSource()));
		 */
		if(parent::_set($tag)){
			$this->setAuthor($tag->getInValue("author"));
			$this->setCategory($tag->getInValue("category"));
			$this->setComments($tag->getInValue("comments"));
			$this->setPubDate($tag->getInValue("pubDate"));
			$this->setGuid($tag->getInValue("guid"));
	
			foreach($tag->getIn("enclosure") as $intag){
				$newtag = new RssEnclosure();
				$newtag->set($intag->get());
				$this->setEnclosure($newtag);
			}
			foreach($tag->getIn("source") as $intag){
				$newtag = new RssSource();
				$newtag->set($intag->get());
				$this->setSource($newtag);
			}
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $src = '<item>'.
		 * 			'<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>'.
		 * 			'<author>tokushima</author><category>php</category><comments>hoge</comments><pubDate>Wed, 10 Oct 2007 10:10:10 +0900</pubDate><guid>123</guid>'.
		 * 			'<enclosure url="http://rhaco.org" type="hoge" length="10" />'.
		 * 			'<source url="http://rhaco.org">rhaco source</source>'.
		 * 			'</item>';
		 * $xml = new RssItem20();
		 * 
		 * assert($xml->set($src));
		 * eq($src,$xml->get());
		 */
		$outTag = parent::_get($outTag);
		if($this->author != "") $outTag->addValue(new SimpleTag("author",$this->getAuthor()));
		if($this->category != "") $outTag->addValue(new SimpleTag("category",$this->getCategory()));
		if($this->comments != "") $outTag->addValue(new SimpleTag("comments",$this->getComments()));

		if($this->pubDate !== null) $outTag->addValue(new SimpleTag("pubDate",$this->getPubDate()));

		if(!empty($this->guid)){
			$tag = new SimpleTag("guid",$this->getGuid());
			if(preg_match("/:\/\//",$this->getGuid())) $tag->setParameter("isPermaLink","true");
			$outTag->addValue($tag->get());
		}
		if(Variable::istype("RssEnclosure",$this->getEnclosure())){
			$enclosure = $this->getEnclosure();		
			if(!$enclosure->isEmpty()) $outTag->addValue($enclosure->get());
		}
		if(Variable::istype("RssSource",$this->getSource())){
			$source = $this->getSource();
			if(!$source->isEmpty()) $outTag->addValue($source->get());
		}
		return $outTag;
	}
	function setAuthor($value){
		$this->author = $value;
	}
	function getAuthor(){
		return $this->author;
	}
	function setCategory($value){
		$this->category = $value;
	}
	function getCategory(){
		return $this->category;
	}
	function setComments($value){
		$this->comments = SimpleTag::xmltext($value);
	}	
	function getComments(){
		return $this->comments;
	}
	function setEnclosure($enclosureOrUrl,$type=null,$length=null){
		if(Variable::istype("RssEnclosure",$enclosureOrUrl)){
			$this->enclosure = $enclosureOrUrl;
		}else{
			$enclosure = new RssEnclosure($enclosureOrUrl,$type,$length);
		}
	}
	function getEnclosure(){
		return $this->enclosure;
	}
	function setGuid($value){
		$this->guid = $value;
	}
	function getGuid(){
		return $this->guid;
	}
	function setSource($value){
		if(Variable::istype("RssSource",$value)) $this->source = $value;
	}
	function getSource(){
		return $this->source;
	}
	function setPubDate($value){
		$this->pubDate = DateUtil::parseString($value);
	}
	function getPubDate($format=""){
		return (empty($format)) ? DateUtil::formatRfc2822($this->pubDate) : DateUtil::format($this->pubDate,$format);
	}
	function formatPubDate($format="Y/m/d H:i:s"){
		return DateUtil::format($this->pubDate,$format);
	}
}