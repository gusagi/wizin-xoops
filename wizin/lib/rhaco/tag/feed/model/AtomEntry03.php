<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.feed.model.AtomEntry");
Rhaco::import("lang.DateUtil");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class AtomEntry03 extends AtomEntry{
	var $author = null;
	var $created = null;	
	var $modified = null;
	var $issued = null;
	var $id = "";

	function _set($tag){
		/***
		 * $src = <<< __XML__
		 * <entry>
		 * 	<title>rhaco</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://rhaco.org" rel="abc" type="xyz" />
		 * 	<link href="http://conveyor.rhaco.org" rel="abc" type="conveyor" />
		 * 	<link href="http://lib.rhaco.org" rel="abc" type="lib" />
		 * 
		 *  <modified>2007-07-18T16:16:31+00:00</modified>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <created>2007-07-18T16:16:31+00:00</created>
		 *  <id>rhaco</id>
		 * <author>
		 * 	<url>http://rhaco.org</url>
		 * 	<name>rhaco</name>
		 * 	<email>rhaco@rhaco.org</email>
		 * </author>
		 * </entry>
		 * __XML__;
		 * 
		 * $xml = new AtomEntry03();
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
		 * 
		 * eq("rhaco",$xml->getId());
		 * eq("2007-07-19T01:16:31+09:00",$xml->getIssued());
		 * eq("2007-07-19T01:16:31Z",$xml->getCreated());
		 * eq("2007-07-19T01:16:31Z",$xml->getModified());
		 * 
		 * $author = $xml->getAuthor();
		 * eq("http://rhaco.org",$author->getUrl());
		 * eq("rhaco",$author->getName());
		 * eq("rhaco@rhaco.org",$author->getEmail());
		 */
		if(parent::_set($tag)){
			$this->setId($tag->getInValue("id"));			
			foreach($tag->getIn("author") as $intag){
				$this->setAuthor($intag->get());
			}
			$this->setCreated($tag->getInValue("created"));
			$this->setModified($tag->getInValue("modified"));
			$this->setIssued($tag->getInValue("issued"));
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $src = <<< __XML__
		 * <entry>
		 * 	<title>rhaco</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://rhaco.org" rel="abc" type="xyz" />
		 * 	<link href="http://conveyor.rhaco.org" rel="abc" type="conveyor" />
		 * 	<link href="http://lib.rhaco.org" rel="abc" type="lib" />
		 * 
		 *  <modified>2007-07-18T16:16:31+00:00</modified>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <created>2007-07-18T16:16:31+00:00</created>
		 *  <id>rhaco</id>
		 * <author>
		 * 	<url>http://rhaco.org</url>
		 * 	<name>rhaco</name>
		 * 	<email>rhaco@rhaco.org</email>
		 * </author>
		 * </entry>
		 * __XML__;
		 * 
		 * $xml = new AtomEntry03();
		 * assert($xml->set($src),"set");
		 * 
		 * 
		 * $rsult = <<< __XML__
		 * <entry>
		 * <title>rhaco</title>
		 * <summary type="xml" xml:lang="ja">summary test</summary>
		 * <content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * <link href="http://rhaco.org" rel="abc" type="xyz" />
		 * <link href="http://conveyor.rhaco.org" rel="abc" type="conveyor" />
		 * <link href="http://lib.rhaco.org" rel="abc" type="lib" />
		 * <id>rhaco</id>
		 * <author><url>http://rhaco.org</url><name>rhaco</name><email>rhaco@rhaco.org</email></author>
		 * <created>2007-07-19T01:16:31Z</created>
		 * <modified>2007-07-19T01:16:31Z</modified>
		 * <issued>2007-07-19T01:16:31+09:00</issued>
		 * </entry>
		 * __XML__;
		 * 
		 * $result = str_replace("\n","",$rsult);
		 * 
		 * eq($result,$xml->get());
		 */
		$outTag = parent::_get($outTag);
		if($this->id != "") $outTag->addValue(new SimpleTag("id",$this->getId()));
		if($this->author !== null){
			$data = $this->getAuthor();
			$outTag->addValue($data->get());
		}
		if($this->created !== null) $outTag->addValue(new SimpleTag("created",$this->getCreated()));
		if($this->modified !== null) $outTag->addValue(new SimpleTag("modified",$this->getModified()));
		if($this->issued !== null) $outTag->addValue(new SimpleTag("issued",$this->getIssued()));


		return $outTag;
	}
	function setModified($value){
		if(!empty($value)) $this->modified = DateUtil::parseString($value);
	}
	function getModified($format=""){
		return (empty($format)) ? DateUtil::formatAtom($this->modified) : DateUtil::format($this->modified,$format);
	}
	function setIssued($value){
		if(!empty($value)) $this->issued = DateUtil::parseString($value);;
	}
	function getIssued($format=""){
		return (empty($format)) ? DateUtil::formatW3C($this->issued) : DateUtil::format($this->issued,$format);
	}
	function setCreated($value){
		if(!empty($value)) $this->created = DateUtil::parseString($value);
	}
	function getCreated($format=""){
		return (empty($format)) ? DateUtil::formatAtom($this->created) : DateUtil::format($this->created,$format);
	}
	function setId($value){
		$this->id = $value;
	}
	function getId(){
		return $this->id;
	}
	function setAuthor($value){
		if(!Variable::istype("AtomAuthor",$value)){
			$author = new AtomAuthor();
			if(!$author->set($value)) $author->setName($value);
			$value = $author;
		}
		$this->author = $value;
	}
	function getAuthor(){
		return (Variable::istype("AtomAuthor",$this->author)) ? $this->author : new AtomAuthor($this->author);
	}
}