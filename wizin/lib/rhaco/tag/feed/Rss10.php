<?php
Rhaco::import("tag.feed.Rss");
Rhaco::import("tag.feed.model.RssChannel10");
Rhaco::import("tag.feed.model.RssItem10");
Rhaco::import("tag.feed.model.RssImage10");
Rhaco::import("tag.feed.model.RssTextinput10");
Rhaco::import("network.http.Http");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.FileUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Rss10 extends Rss{
	var $itemList = array();
	var $imageList = array();
	var $textinputList = array();	

	/**
	 * 文字列からRss10をセットする
	 *
	 * @param string $src
	 */	
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <rdf:RDF>
		 * <channel rdf:about="http://rhacoorg">
		 * <title>rhaco</title>
		 * <link>http://rhaco.org</link>
		 * <description>php</description>
		 * <image rdf:resource="hogeimage1" />
		 * <image rdf:resource="hogeimage2" />
		 * <textinput rdf:resource="text1" />
		 * <textinput rdf:resource="text2" />
		 * <items>
		 * <rdf:Seq>
		 * <rdf:li rdf:resource="http://rhaco.org" />
		 * <rdf:li rdf:resource="http://everes.net" />
		 * </rdf:Seq>
		 * </items>
		 * </channel>
		 * <image rdf:about="hogeimage1"><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>
		 * <image rdf:about="hogeimage2"><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>
		 * <textinput rdf:about="text1"><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>
		 * <textinput rdf:about="text2"><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>
		 * <item rdf:about="http://rhaco.org">
		 * 	<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>
		 * 	<dc:subject>rhaco</dc:subject>
		 * 	<dc:creator>tokushima</dc:creator>
		 * 	<dc:date>2007-07-18T16:16:31+00:00</dc:date>
		 * </item>
		 * <item rdf:about="http://everes.net">
		 * 	<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>
		 * 	<dc:subject>rhaco</dc:subject>
		 * 	<dc:creator>tokushima</dc:creator>
		 * 	<dc:date>2007-07-18T16:16:31+00:00</dc:date>
		 * </item>
		 * </rdf:RDF>
		 * __XML__;
		 * 
		 * $feed = new Rss10();
		 * assert($feed->set($src));
		 * 
		 * $xml = $feed->getChannel();
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("php",$xml->getDescription());
		 * 
		 * eq(2,sizeof($xml->getImage()));
		 * eq(2,sizeof($xml->getTextInput()));
		 * eq(2,sizeof($xml->getItem()));
		 * 
		 * $count = 0;
		 * foreach($feed->getImage() as $xml){
		 * 	$count++;
		 * 	eq("rhaco",$xml->getTitle());
		 * 	eq("http://rhaco.org",$xml->getUrl());
		 * 	eq("http://rhaco.org",$xml->getLink());
		 * 	eq("hogeimage".$count,$xml->getAbout());
		 * }
		 * 
		 * $count = 0;
		 * foreach($feed->getTextInput() as $xml){
		 * 	$count++;
		 * 	eq("rhaco",$xml->getTitle());
		 * 	eq("tokushima",$xml->getName());
		 * 	eq("http://rhaco.org",$xml->getLink());
		 * 	eq("rhaco desc",$xml->getDescription());
		 * 	eq("text".$count,$xml->getAbout());
		 * }
		 * 
		 * $count = 0;
		 * eq(2,sizeof($feed->getItem()));
		 * $about = array("http://rhaco.org","http://everes.net");
		 * foreach($feed->getItem() as $xml){
		 * 	eq("rhaco",$xml->getTitle());
		 * 	eq("http://rhaco.org",$xml->getLink());
		 * 	eq("rhaco desc",$xml->getDescription());
		 * 
		 * 	eq($about[$count],$xml->getAbout());
		 * 	eq("rhaco",$xml->getSubject());
		 * 	eq("tokushima",$xml->getCreator());
		 * 	eq("2007-07-19T01:16:31+09:00",DateUtil::formatW3C($xml->getDate()));
		 * 	$count++;
		 * }
		 */
		$this->itemList = array();
		$this->imageList = array();
		$this->textinputList = array();

		$tag = new SimpleTag();
		if(!$tag->set($src,"rdf:RDF")){
			return false;
		}		
		foreach($tag->getIn("channel",true) as $intag){
			$data = new RssChannel10();
			$data->set($intag->get());
			$this->channel = $data;
		}
		foreach($tag->getIn("image",true) as $intag){
			$data = new RssImage10();
			$data->set($intag->get());
			$this->imageList[] = $data;
		}
		foreach($tag->getIn("textinput",true) as $intag){
			$data = new RssTextinput10();
			$data->set($intag->get());
			$this->textinputList[] = $data;
		}
		foreach($tag->getIn("item",true) as $intag){
			$data = new RssItem10();
			$data->set($intag->get());
			$this->itemList[] = $data;
		}
		return true;
	}
	
	/**
	 * フォーマットされた文字列を取得
	 *
	 * @return string
	 */
	function get(){
		/***
		 * $src = <<< __XML__
		 * <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns="http://purl.org/rss/1.0/">
		 * <channel rdf:about="http://rhacoorg">
		 * <title>rhaco</title>
		 * <link>http://rhaco.org</link>
		 * <description>php</description>
		 * <image rdf:resource="hogeimage1" />
		 * <image rdf:resource="hogeimage2" />
		 * <textinput rdf:resource="text1" />
		 * <textinput rdf:resource="text2" />
		 * <items>
		 * <rdf:Seq>
		 * <rdf:li rdf:resource="http://rhaco.org" />
		 * <rdf:li rdf:resource="http://everes.net" />
		 * </rdf:Seq>
		 * </items>
		 * </channel>
		 * <image rdf:about="hogeimage1"><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>
		 * <image rdf:about="hogeimage2"><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url></image>
		 * <textinput rdf:about="text1"><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>
		 * <textinput rdf:about="text2"><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>
		 * <item rdf:about="http://rhaco.org">
		 * 	<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>
		 * 	<dc:subject>rhaco</dc:subject>
		 * 	<dc:creator>tokushima</dc:creator>
		 * 	<dc:date>2007-07-19T01:16:31+09:00</dc:date>
		 * </item>
		 * <item rdf:about="http://everes.net">
		 * 	<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>
		 * 	<dc:subject>rhaco</dc:subject>
		 * 	<dc:creator>tokushima</dc:creator>
		 * 	<dc:date>2007-07-19T01:16:31+09:00</dc:date>
		 * </item>
		 * </rdf:RDF>
		 * __XML__;
		 * 
		 * $feed = new Rss10();
		 * assert($feed->set($src));
		 * 
		 * eq(str_replace(array("\t","\n"),array("",""),$src),$feed->get(),"src");
		 */
		$outTag	= new SimpleTag("rdf:RDF");
		$outTag->setParameter("xmlns:rdf","http://www.w3.org/1999/02/22-rdf-syntax-ns#");
		$outTag->setParameter("xmlns","http://purl.org/rss/1.0/");		
		
		if(Variable::istype("RssChannel10",$this->getChannel())){
			$image		= "";
			$items		= "";
			$textinput	= "";

			$channel				= $this->getChannel();
			$channel->imageList		= array();
			$channel->itemsList		= array();
			$channel->textinputList	= array();

			foreach($this->getImage() as $data){
				if(Variable::istype("RssImage10",$data)){
					$image .= $data->get();
					$channel->setImage($data->getAbout());
				}
			}
			foreach($this->getTextinput() as $data){
				if(Variable::istype("RssTextinput10",$data)){
					$textinput .= $data->get();
					$channel->setTextinput($data->getAbout());
				}
			}
			foreach($this->getItem() as $data){
				if(Variable::istype("RssItem10",$data)){
					$items .= $data->get();
					$channel->setItem($data->getAbout());
				}
			}
			$outTag->addValue($channel->get());
			if(!empty($image)) $outTag->addValue($image);
			if(!empty($textinput)) $outTag->addValue($textinput);
			if(!empty($items)) $outTag->addValue($items);
		}
		return $outTag->get();
	}	
	function setChannel($titleOrChannel,$description="",$link="",$about=""){
		$this->channel = (Variable::istype("RssChannel10",$titleOrChannel)) ? Variable::copy($titleOrChannel) : new RssChannel10($titleOrChannel,$description,$link,$about);
	}
	function getChannel(){
		return (Variable::istype("RssChannel10",$this->channel)) ? $this->channel : new RssChannel10();
	}
	function setItem($title,$description="",$link="",$about=""){
		$this->itemList[] = new RssItem10($title,$description,$link,$about);
	}
	function setImage($title,$url,$link="",$about=""){
		$this->imageList[] = new RssImage10($title,$url,$link,$about);
	}
	function setTextinput($title,$name,$description="",$link="",$about=""){
		$this->textinputList[] = new RssTextinput10($title,$name,$description,$link,$about);
	}
	function getItem(){
		return $this->itemList;
	}
	function getImage(){
		return $this->imageList;
	}
	function getTextinput(){
		return $this->textinputList;
	}
}
?>