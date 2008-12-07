<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.feed.model.AtomContent");
Rhaco::import("tag.feed.model.AtomSummary");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class AtomEntry{
	var $link = array();
	var $title = "";
	var $summary = null;
	var $content = null;

	function AtomEntry($title="",$summary="",$content=""){
		$this->setTitle($title);

		$this->setSummary(new AtomSummary($summary));
		$this->setContent(new AtomContent($content));
	}	
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <entry>
		 * 	<title>rhaco</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://rhaco.org" rel="abc" type="xyz" />
		 * 	<link href="http://conveyor.rhaco.org" rel="abc" type="conveyor" />
		 * 	<link href="http://lib.rhaco.org" rel="abc" type="lib" />
		 * </entry>
		 * __XML__;
		 * 
		 * $xml = new AtomEntry();
		 * assert($xml->set($src),"set");
		 * eq("rhaco",$xml->getTitle());
		 * assert(Variable::istype("AtomSummary",$xml->getSummary()),"AtomSummary");
		 * eq("summary test",$xml->getSummaryValue());
		 * assert(Variable::istype("AtomContent",$xml->getContent()),"AtomContent");
		 * eq("atom content",$xml->getContentValue());
		 * 
		 * eq(3,sizeof($xml->getLink()));
		 * foreach($xml->getLink() as $link){
		 * 	assert(Variable::istype("AtomLink",$link),"AtomLink");
		 * }
		 * eq("http://rhaco.org",$xml->getLinkHref());
		 * 
		 */
		if(SimpleTag::setof($tag,$src,"entry")){
			return $this->_set($tag);
		}
		return false;
	}
	function _set($tag){
		$this->setTitle($tag->getInValue("title"));

		foreach($tag->getIn("summary") as $intag){
			$data = new AtomSummary();
			if($data->set($intag->get())) $this->setSummary($data);
		}
		foreach($tag->getIn("content",true) as $intag){
			$data = new AtomContent();
			if($data->set($intag->get())) $this->setContent($data);
		}
		foreach($tag->getIn("link",true) as $intag){
			$data = new AtomLink();
			if($data->set($intag->get())) $this->link[] = $data;
		}
		return true;
	}
	function get(){
		/***
		 * $src = <<< __XML__
		 * <entry>
		 * 	<title>rhaco</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://rhaco.org" rel="abc" type="xyz" />
		 * 	<link href="http://conveyor.rhaco.org" rel="abc" type="conveyor" />
		 * 	<link href="http://lib.rhaco.org" rel="abc" type="lib" />
		 * </entry>
		 * __XML__;
		 * 
		 * $xml = new AtomEntry();
		 * assert($xml->set($src),"set");
		 * 
		 * 
		 * $result = <<< __XML__
		 * <entry>
		 * <title>rhaco</title>
		 * <summary type="xml" xml:lang="ja">summary test</summary>
		 * <content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * <link href="http://rhaco.org" rel="abc" type="xyz" />
		 * <link href="http://conveyor.rhaco.org" rel="abc" type="conveyor" />
		 * <link href="http://lib.rhaco.org" rel="abc" type="lib" />
		 * </entry>
		 * __XML__;
		 * 
		 * $result = str_replace("\n","",$result);
		 * 
		 * eq($result,$xml->get());
		 */
		$outTag = new SimpleTag("entry");
		$outTag = $this->_get($outTag);
		return $outTag->get();
	}
	function _get($outTag){	
		$outTag->addValue(new SimpleTag("title",$this->getTitle()));

		if(Variable::istype("AtomSummary",$this->getSummary())){
			$data = $this->getSummary();
			if(!$data->isEmpty()) $outTag->addValue($data->get());
		}		
		if(Variable::istype("AtomContent",$this->getContent())){
			$data = $this->getContent();
			if(!$data->isEmpty()) $outTag->addValue($data->get());
		}		
		foreach($this->link as $data){
			if(Variable::istype("AtomLink",$data)){
				if(!$data->isEmpty()) $outTag->addValue($data->get());
			}
		}
		return $outTag;
	}
	
	function setTitle($value){
		$this->title = SimpleTag::xmltext($value);
	}
	function getTitle(){
		return $this->title;
	}
	function setSummary($value){
		$this->summary = (Variable::istype("AtomSummary",$value)) ? $value : new AtomSummary(SimpleTag::xmltext($value));
	}
	function getSummary(){
		return $this->summary;
	}
	function getSummaryValue(){
		return (Variable::istype("AtomSummary",$this->summary)) ? $this->summary->getValue() : "";
	}
	function setContent($value){
		$this->content = (Variable::istype("AtomContent",$value)) ? $value : new AtomContent(SimpleTag::xmltext($value));
	}
	function getContent(){
		return $this->content;
	}
	function getContentValue(){
		return (Variable::istype("AtomContent",$this->content)) ? $this->content->getValue() : "";
	}
	function setLink($href,$rel="",$type=""){
		$this->link[] = new AtomLink($href,$rel,$type);
	}
	function getLink(){
		return $this->link;
	}
	function getLinkHref(){
		foreach($this->link as $link) return $link->getHref();
		return "";
	}
}