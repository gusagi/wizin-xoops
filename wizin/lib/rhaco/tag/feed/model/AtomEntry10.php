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
class AtomEntry10 extends AtomEntry{
	var $author = null;
	var $updated = null;
	var $issued = null;
	var $id = "";
	var $published = null;

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
		 *  <updated>2007-07-18T16:16:31+00:00</updated>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <published>2007-07-18T16:16:31+00:00</published>
		 *  <id>rhaco</id>
		 * <author>
		 * 	<url>http://rhaco.org</url>
		 * 	<name>rhaco</name>
		 * 	<email>rhaco@rhaco.org</email>
		 * </author>
		 * </entry>
		 * __XML__;
		 * 
		 * $xml = new AtomEntry10();
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
		 * eq("2007-07-19T01:16:31Z",$xml->getIssued());
		 * eq("2007-07-19T01:16:31Z",$xml->getPublished());
		 * eq("2007-07-19T01:16:31Z",$xml->getUpdated());
		 * 
		 * $author = $xml->getAuthor();
		 * eq("http://rhaco.org",$author->getUrl());
		 * eq("rhaco",$author->getName());
		 * eq("rhaco@rhaco.org",$author->getEmail());
		 */
		if(parent::_set($tag)){
			foreach($tag->getIn("author") as $intag){
				$this->setAuthor($intag->get());
			}
			$this->setUpdated($tag->getInValue("updated"));
			$this->setIssued($tag->getInValue("issued"));
			$this->setPublished($tag->getInValue("published"));
			$this->setId($tag->getInValue("id"));
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
		 *  <updated>2007-07-18T16:16:31+00:00</updated>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <published>2007-07-18T16:16:31+00:00</published>
		 *  <id>rhaco</id>
		 * <author>
		 * 	<url>http://rhaco.org</url>
		 * 	<name>rhaco</name>
		 * 	<email>rhaco@rhaco.org</email>
		 * </author>
		 * </entry>
		 * __XML__;
		 * 
		 * $xml = new AtomEntry10();
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
		 * <published>2007-07-19T01:16:31Z</published>
		 * <updated>2007-07-19T01:16:31Z</updated>
		 * <issued>2007-07-19T01:16:31Z</issued>
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
		if($this->published !== null) $outTag->addValue(new SimpleTag("published",$this->getPublished()));
		if($this->updated !== null) $outTag->addValue(new SimpleTag("updated",$this->getUpdated()));
		if($this->issued !== null) $outTag->addValue(new SimpleTag("issued",$this->getIssued()));
		return $outTag;
	}
	function setUpdated($value){
		if(!empty($value)) $this->updated = DateUtil::parseString($value);
	}
	function getUpdated($format=""){
		return (empty($format)) ? DateUtil::formatAtom($this->updated) : DateUtil::format($this->updated,$format);
	}
	function setIssued($value){
		if(!empty($value)) $this->issued = DateUtil::parseString($value);
	}
	function getIssued($format=""){
		return (empty($format)) ? DateUtil::formatAtom($this->issued) : DateUtil::format($this->issued,$format);
	}
	function setPublished($value){
		if(!empty($value)) $this->published = DateUtil::parseString($value);
	}
	function getPublished($format=""){
		return (empty($format)) ? DateUtil::formatAtom($this->published) : DateUtil::format($this->published,$format);
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
		return (Variable::istype("AtomAuthor",$this->author)) ? $this->author : new AtomAuthor();
	}
}