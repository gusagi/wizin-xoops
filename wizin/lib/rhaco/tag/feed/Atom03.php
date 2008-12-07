<?php
Rhaco::import("tag.feed.Atom");
Rhaco::import("tag.feed.model.AtomAuthor");
Rhaco::import("tag.feed.model.AtomLink");
Rhaco::import("tag.feed.model.AtomEntry03");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.Variable");
/**
 * Atom03 Model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Atom03 extends Atom{
	var $version = "0.3";
	var $title = "";
	var $modified = null;

	var $link = array();
	var $author = null;
	var $entryList = array();
	
	function Atom03(){
	}
	
	/**
	 * 文字列からAtom03をセットする
	 *
	 * @param string $src
	 */
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <feed version="0.3">
		 * <title>atom03 feed</title>
		 * <modified>2007-07-18T16:16:31+00:00</modified>
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
		 * 
		 * <entry>
		 * 	<title>django</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://djangoproject.jp" rel="abc" type="xyz" />
		 * 
		 *  <modified>2007-07-18T16:16:31+00:00</modified>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <created>2007-07-18T16:16:31+00:00</created>
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
		 * $xml = new Atom03();
		 * assert($xml->set($src));
		 * eq("atom03 feed",$xml->getTitle());
		 * eq("http://tokushimakazutaka.com",$xml->getLinkHref());
		 * eq("2007-07-19T01:16:31+09:00",$xml->getModified());
		 * 
		 * $author = $xml->getAuthor();
		 * eq("http://tokushimakazutaka.com",$author->getUrl());
		 * eq("tokushima",$author->getName());
		 * eq("tokushima@hoge.hoge",$author->getEmail());
		 * 
		 * eq(2,sizeof($xml->getEntry()));
		 * foreach($xml->getEntry() as $entry){
		 * 	assert(Variable::istype("AtomEntry03",$entry));
		 * }
		 */
		if(SimpleTag::setof($tag,$src,"feed")){
			if($tag->getParameter("version") != "0.3") return false;

			foreach($tag->getIn("entry") as $intag){
				$data = new AtomEntry03();
				$data->set($intag->get());
				$this->entryList[] = $data;
	
				$src = str_replace($intag->getPlain(),"",$src);
			}
			$tag->set($src,"feed");
			$this->setVersion($tag->getParameter("version"));

			$this->setTitle($tag->getInValue("title"));
			$this->setModified($tag->getInValue("modified"));

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
		 * $src = <<< __XML__
		 * <feed version="0.3">
		 * <title>atom03 feed</title>
		 * <modified>2007-07-18T16:16:31+00:00</modified>
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
		 * 
		 * <entry>
		 * 	<title>django</title>
		 * 	<summary type="xml" xml:lang="ja">summary test</summary>
		 * 	<content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * 	<link href="http://djangoproject.jp" rel="abc" type="xyz" />
		 * 
		 *  <modified>2007-07-18T16:16:31+00:00</modified>
		 *  <issued>2007-07-18T16:16:31+00:00</issued>
		 *  <created>2007-07-18T16:16:31+00:00</created>
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
		 * $xml = new Atom03();
		 * assert($xml->set($src));
		 * 
		 * $result = <<< __XML__
		 * <feed version="0.3" xmlns="http://purl.org/atom/ns#">
		 * <title>atom03 feed</title>
		 * <modified>2007-07-19T01:16:31+09:00</modified>
		 * <link href="http://tokushimakazutaka.com" rel="abc" type="xyz" />
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
		 * <created>2007-07-19T01:16:31Z</created>
		 * <modified>2007-07-19T01:16:31Z</modified>
		 * <issued>2007-07-19T01:16:31+09:00</issued>
		 * </entry>
		 * <entry>
		 * <title>django</title>
		 * <summary type="xml" xml:lang="ja">summary test</summary>
		 * <content type="text/xml" mode="abc" xml:lang="ja" xml:base="base">atom content</content>
		 * <link href="http://djangoproject.jp" rel="abc" type="xyz" />
		 * <id>django</id>
		 * <author><url>http://www.everes.net</url><name>everes</name><email>everes@hoge.hoge</email></author>
		 * <created>2007-07-19T01:16:31Z</created>
		 * <modified>2007-07-19T01:16:31Z</modified>
		 * <issued>2007-07-19T01:16:31+09:00</issued>
		 * </entry>
		 * </feed>
		 * __XML__;
		 * 
		 * $result = str_replace("\n","",$result);
		 * 
		 * eq($result,$xml->get());
		 */
		$outTag	= new SimpleTag("feed");
		$outTag->setParameter("version",$this->getVersion());
		$outTag->setParameter("xmlns","http://purl.org/atom/ns#");

		if($this->title != "") $outTag->addValue(new SimpleTag("title",$this->getTitle()));
		if($this->modified !== null) $outTag->addValue(new SimpleTag("modified",$this->getModified()));
		foreach($this->getLink() as $data){
			if(Variable::istype("AtomLink",$data)){
				if(!$data->isEmpty()) $outTag->addValue($data->get());
			}
		}
		if($this->author !== null){
			$data = $this->getAuthor();
			$outTag->addValue($data->get());
		}
		foreach($this->getEntry() as $data){
			if(Variable::istype("AtomEntry03",$data)){
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
	function setTitle($value){
		$this->title = $value;
	}
	function getTitle(){
		return $this->title;
	}
	function setModified($value){
		$this->modified = DateUtil::parseString($value);
	}
	function getModified($format=""){
		return (empty($format)) ? DateUtil::formatW3C($this->modified) : DateUtil::format($this->modified,$format);
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
	function getEntry(){
		return $this->entryList;
	}
	function setEntry($title,$summary="",$content=""){
		$this->entryList[] = new AtomEntry03($title,$summary,$content);
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
?>