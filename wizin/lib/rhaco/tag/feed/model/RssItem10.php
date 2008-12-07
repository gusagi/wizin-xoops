<?php
Rhaco::import("tag.feed.model.RssItem");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.DateUtil");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssItem10 extends RssItem{
	var $about	= "";
	var $subject = "";
	var $creator = "";
	var $date = null;

	function RssItem10($title="",$description="",$link="",$about=""){
		parent::RssItem($title,$description,$link);
		$this->setAbout($about);
	}
	function _set($tag){
		/***
		 * $src = <<< __XML__
		 * 
		 * <item rdf:about="hoge">
		 * 	<title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>
		 * 	<dc:subject>rhaco</dc:subject>
		 * 	<dc:creator>tokushima</dc:creator>
		 * 	<dc:date>2007-07-18T16:16:31+00:00</dc:date>
		 * </item>
		 * 
		 * __XML__;
		 * 
		 * $xml = new RssItem10();
		 * 
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("rhaco desc",$xml->getDescription());
		 * 
		 * eq("hoge",$xml->getAbout());
		 * eq("rhaco",$xml->getSubject());
		 * eq("tokushima",$xml->getCreator());
		 * eq("2007-07-19T01:16:31+09:00",DateUtil::formatW3C($xml->getDate()));
		 * 
		 */
		if(parent::_set($tag)){
			$this->setAbout($tag->getParameter("rdf:about"));
			$this->setSubject($tag->getInValue("dc:subject"));
			$this->setCreator($tag->getInValue("dc:creator"));
			$this->setDate($tag->getInValue("dc:date"));
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $xml = new RssItem10();
		 * 
		 * eq('<item />',$xml->get());
		 * 
		 * $xml->setTitle("rhaco");
		 * eq('<item><title>rhaco</title></item>',$xml->get());
		 * 
		 * $xml->setLink("http://rhaco.org");
		 * eq('<item><title>rhaco</title><link>http://rhaco.org</link></item>',$xml->get());
		 * 
		 * $xml->setDescription("rhaco desc");
		 * eq('<item><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description></item>',$xml->get());
		 * 
		 * 
		 * $xml->setAbout("hoge");
		 * eq('<item rdf:about="hoge"><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description></item>',$xml->get());
		 * 
		 * $xml->setSubject("rhaco");
		 * $str = '<item rdf:about="hoge"><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>'.
		 * 			'<dc:subject>rhaco</dc:subject>'.
		 * 			'</item>';
		 * eq($str,$xml->get());
		 * 
		 * $xml->setCreator("tokushima");
		 * $str = '<item rdf:about="hoge"><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>'.
		 * 			'<dc:subject>rhaco</dc:subject><dc:creator>tokushima</dc:creator>'.
		 * 			'</item>';
		 * eq($str,$xml->get());
		 * 
		 * $xml->setDate("2007-07-19T01:16:31+09:00");
		 * $str = '<item rdf:about="hoge"><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description>'.
		 * 			'<dc:subject>rhaco</dc:subject><dc:creator>tokushima</dc:creator><dc:date>2007-07-19T01:16:31+09:00</dc:date>'.
		 * 			'</item>';
		 * eq($str,$xml->get());
		 * 
		 */
		$outTag = parent::_get($outTag);
		if($this->about != "") $outTag->setParameter("rdf:about",$this->getAbout());
		if($this->subject != "") $outTag->addValue(new SimpleTag("dc:subject",$this->getSubject()));
		if($this->creator != "") $outTag->addValue(new SimpleTag("dc:creator",$this->getCreator()));
		if($this->date != null) $outTag->addValue(new SimpleTag("dc:date",$this->getDate()));
		return $outTag;
	}

	function setAbout($value){
		$this->about = $value;
	}
	function getAbout(){
		return $this->about;
	}
	function setSubject($value){
		$this->subject = $value;
	}
	function getSubject(){
		return $this->subject;
	}
	function setCreator($value){
		$this->creator = $value;
	}
	function getCreator(){
		return $this->creator;
	}
	function setDate($value){
		if(!empty($value)) $this->date = DateUtil::parseString($value);
	}
	function getDate($format=""){
		return (empty($format)) ? DateUtil::formatW3C($this->date) : DateUtil::format($this->date,$format);
	}
}