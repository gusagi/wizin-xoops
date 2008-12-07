<?php
Rhaco::import("tag.feed.Atom");
Rhaco::import("tag.feed.model.AtomAuthor");
Rhaco::import("tag.feed.model.AtomLink");
Rhaco::import("tag.feed.model.AtomEntry10");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.Variable");
/**
 * Atom10 Model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Atom10 extends Atom{
	var $version = "1.0";
	var $title = "";
	var $subtitle = "";	
	var $updated = null;
	var $id = "";
	var $link = array();
	var $generator = "";
	var $entryList = array();
	var $author = null;

	function Atom10(){
	}
	
	/**
	 * 文字列からAtom10をセットする
	 *
	 * @param string $src
	 */
	function set($src){
		/***
		 * unit("tag.feed.Atom10Test");
		 * 
		 * $src = <<< __XML__
		 * <feed xmlns="http://www.w3.org/2005/Atom">
		 * <title>atom10 feed</title>
		 * <subtitle>atom10 sub title</subtitle>
		 * <updated>2007-07-18T16:16:31+00:00</updated>
		 * <generator>tokushima</generator>
		 * <link href="http://tokushimakazutaka.com" rel="abc" type="xyz" />
		 * 
		 * <author>
		 * 	<url>http://tokushimakazutaka.com</url>
		 * 	<name>tokushima</name>
		 * 	<email>tokushima@hoge.hoge</email>
		 * </author>
		 * 
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
		 * 
		 * <entry>
		 * 	<title>django</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://djangoproject.jp" rel="abc" type="xyz" />
		 * 
		 *  <updated>2007-07-18T16:16:31+00:00</updated>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <published>2007-07-18T16:16:31+00:00</published>
		 *  <id>django</id>
		 * <author>
		 * 	<url>http://www.everes.net</url>
		 * 	<name>everes</name>
		 * 	<email>everes@hoge.hoge</email>
		 * </author>
		 * </entry>
		 * 
		 * </feed>
		 * __XML__;
		 * 
		 * $xml = new Atom10();
		 * assert($xml->set($src));
		 * eq("atom10 feed",$xml->getTitle());
		 * eq("atom10 sub title",$xml->getSubTitle());
		 * eq("http://tokushimakazutaka.com",$xml->getLinkHref());
		 * eq("2007-07-19T01:16:31Z",$xml->getUpdated());
		 * eq("tokushima",$xml->getGenerator());
		 * 
		 * $author = $xml->getAuthor();
		 * eq("http://tokushimakazutaka.com",$author->getUrl());
		 * eq("tokushima",$author->getName());
		 * eq("tokushima@hoge.hoge",$author->getEmail());
		 * 
		 * eq(2,sizeof($xml->getEntry()));
		 * foreach($xml->getEntry() as $entry){
		 * 	assert(Variable::istype("AtomEntry10",$entry));
		 * }
		 */
		if(simpleTag::setof($tag,$src,"feed")){
			if($tag->getParameter("xmlns") != "http://www.w3.org/2005/Atom") return false;

			foreach($tag->getIn("entry") as $intag){
				$data = new AtomEntry10();
				$data->set($intag->get());
				$this->entryList[] = $data;
				
				$src = str_replace($intag->getPlain(),"",$src);
			}
			$tag->set($src,"feed");

			$this->setId($tag->getInValue("id"));
			$this->setTitle($tag->getInValue("title"));
			$this->setSubtitle($tag->getInValue("subtitle"));
			$this->setUpdated($tag->getInValue("updated"));
			$this->setGenerator($tag->getInValue("generator"));
			foreach($tag->getIn("author") as $intag){
				$this->setAuthor($intag->get());
			}
			foreach($tag->getIn("link",true) as $intag){
				$data = new AtomLink();
				if($data->set($intag->get())) $this->link[] = $data;
			}
			return true;
		}
		return false;
	}
	
	/**
	 * フォーマットされた文字列を取得
	 *
	 * @return string
	 */
	function get(){
		/***
		 * unit("tag.feed.Atom10Test");
		 * 
		 * $src = <<< __XML__
		 * <feed xmlns="http://www.w3.org/2005/Atom">
		 * <title>atom10 feed</title>
		 * <subtitle>atom10 sub title</subtitle>
		 * <updated>2007-07-18T16:16:31+00:00</updated>
		 * <generator>tokushima</generator>
		 * <link href="http://tokushimakazutaka.com" rel="abc" type="xyz" />
		 * 
		 * <author>
		 * 	<url>http://tokushimakazutaka.com</url>
		 * 	<name>tokushima</name>
		 * 	<email>tokushima@hoge.hoge</email>
		 * </author>
		 * 
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
		 * 
		 * <entry>
		 * 	<title>django</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://djangoproject.jp" rel="abc" type="xyz" />
		 * 
		 *  <updated>2007-07-18T16:16:31+00:00</updated>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <published>2007-07-18T16:16:31+00:00</published>
		 *  <id>django</id>
		 * <author>
		 * 	<url>http://www.everes.net</url>
		 * 	<name>everes</name>
		 * 	<email>everes@hoge.hoge</email>
		 * </author>
		 * </entry>
		 * 
		 * </feed>
		 * __XML__;
		 * 
		 * 
		 * $xml = new Atom10();
		 * assert($xml->set($src));
		 * 
		 * $result = <<< __XML__
		 * <feed xmlns="http://www.w3.org/2005/Atom">
		 * <title>atom10 feed</title>
		 * <subtitle>atom10 sub title</subtitle>
		 * <updated>2007-07-19T01:16:31Z</updated>
		 * <link href="http://tokushimakazutaka.com" rel="abc" type="xyz" />
		 * <generator>tokushima</generator>
		 * <author>
		 * <url>http://tokushimakazutaka.com</url>
		 * <name>tokushima</name>
		 * <email>tokushima@hoge.hoge</email>
		 * </author>
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
		 * <entry>
		 * <title>django</title>
		 * <summary type="xml" xml:lang="ja">summary test</summary>
		 * <content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * <link href="http://djangoproject.jp" rel="abc" type="xyz" />
		 * <id>django</id>
		 * <author><url>http://www.everes.net</url><name>everes</name><email>everes@hoge.hoge</email></author>
		 * <published>2007-07-19T01:16:31Z</published>
		 * <updated>2007-07-19T01:16:31Z</updated>
		 * <issued>2007-07-19T01:16:31Z</issued>
		 * </entry>
		 * </feed>
		 * __XML__;
		 * 
		 * $result = str_replace("\n","",$result);
		 * 
		 * eq($result,$xml->get());
		 */	
		$outTag	= new SimpleTag("feed","",array("xmlns"=>"http://www.w3.org/2005/Atom"));
		if($this->id != "") $outTag->addValue(new SimpleTag("id",$this->getId()));
		if($this->title != "") $outTag->addValue(new SimpleTag("title",$this->getTitle()));
		if($this->subtitle != "") $outTag->addValue(new SimpleTag("subtitle",$this->getSubtitle()));
		if($this->updated !== null) $outTag->addValue(new SimpleTag("updated",$this->getUpdated()));
		foreach($this->getLink() as $data){
			if(Variable::istype("AtomLink",$data)){
				if(!$data->isEmpty()) $outTag->addValue($data->get());
			}
		}
		if($this->generator != "") $outTag->addValue(new SimpleTag("generator",$this->getGenerator()));
		if($this->author !== null){
			$data = $this->getAuthor();
			$outTag->addValue($data->get());
		}
		foreach($this->getEntry() as $data){
			if(Variable::istype("AtomEntry10",$data)){
				$outTag->addValue($data->get());
			}
		}
		return $outTag->get();
	}
	function setVersion($value){
		$this->version = $value;
	}
	function getVersion(){
		return $this->version;
	}
	function setId($value){
		$this->id = $value;
	}
	function getId(){
		return $this->id;
	}
	function setTitle($value){
		$this->title = $value;
	}
	function getTitle(){
		return $this->title;
	}
	function setSubtitle($value){
		$this->subtitle = $value;
	}
	function getSubtitle(){
		return $this->subtitle;
	}
	function setUpdated($value){
		$this->updated = DateUtil::parseString($value);
	}
	function getUpdated($format=""){
		return (empty($format)) ? DateUtil::formatAtom($this->updated) : DateUtil::format($this->updated,$format);
	}
	function setGenerator($value){
		$this->generator = $value;
	}
	function getGenerator(){
		return $this->generator;
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
	function getEntry(){
		return $this->entryList;
	}
	function setEntry($title,$summary="",$content=""){
		$this->entryList[] = (Variable::istype("AtomEntry10",$title)) ? 
									$title : 
									new AtomEntry10($title,$summary,$content);
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
?>