<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.feed.model.RssChannel");
Rhaco::import("tag.feed.model.RssImage09");
Rhaco::import("tag.feed.model.RssItem");
Rhaco::import("tag.feed.model.RssTextinput");
Rhaco::import("lang.DateUtil");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssChannel09 extends RssChannel{
	var $language = "";
	
	var $copyright = "";
	var $docs = "";
	var $lastBuildDate = "";
	var $managingEditor = "";
	var $pubDate = "";
	var $webMaster = "";
	
	var $skipDaysList = array();
	var $skipHoursList = array();
	
	var $imageList = array();
	var $itemList = array();
	var $textinputList = array();

	function RssChannel09($title="",$description="",$link="",$language=""){
		parent::RssChannel($title,$description,$link);
		$this->setLanguage($language);
	}
	function _set($tag){
		/***
		 * $src = <<< __XML__
		 * <channel>
		 * <title>rhaco</title>
		 * <link>http://rhaco.org</link>
		 * <description>php</description>
		 * <language>ja</language>
		 * <copyright>rhaco.org</copyright>
		 * <docs>hogehoge</docs>
		 * <lastBuildDate>2007-10-10T10:10:10+09:00</lastBuildDate>
		 * <managingEditor>tokushima</managingEditor>
		 * <pubDate>2007-10-10T10:10:10+09:00</pubDate>
		 * <webMaster>kazutaka</webMaster>
		 * <skipDays>
		 * <day>1</day>
		 * <day>20</day>
		 * </skipDays>
		 * <skipHours>
		 * <hour>2</hour>
		 * <hour>10</hour>
		 * </skipHours>
		 * <image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url><width>100</width><height>200</height></image>
		 * <image><title>everes</title><link>http://www.everes.net</link><url>http://everes.net</url><width>100</width><height>200</height></image>
		 * <textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>
		 * <textinput><title>everes</title><name>tsuyuki</name><link>http://www.everes.net</link><description>everes desc</description></textinput>
		 * <item><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description></item>
		 * <item><title>everes</title><link>http://www.everes.net</link><description>everes desc</description></item>
		 * </channel>
		 * __XML__;
		 * 
		 * 
		 * $xml = new RssChannel09();
		 * assert($xml->set($src));
		 * 
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("php",$xml->getDescription());
		 * 
		 * eq("ja",$xml->getLanguage());
		 * eq("rhaco.org",$xml->getCopyright());
		 * eq("hogehoge",$xml->getDocs());
		 * eq("2007-10-10T10:10:10+09:00",$xml->getLastBuildDate());
		 * eq("tokushima",$xml->getManagingEditor());
		 * eq("2007-10-10T10:10:10+09:00",$xml->getPubDate());
		 * eq("kazutaka",$xml->getWebMaster());
		 * eq(2,sizeof($xml->getImage()));
		 * foreach($xml->getImage() as $data){
		 * 	assert(Variable::istype("RssImage09",$data));
		 * }
		 * eq(2,sizeof($xml->getTextInput()));
		 * foreach($xml->getTextInput() as $data){
		 * 	assert(Variable::istype("RssTextinput",$data));
		 * }
		 * eq(2,sizeof($xml->getItem()));
		 * foreach($xml->getItem() as $data){
		 * 	assert(Variable::istype("RssItem",$data));
		 * }
		 * eq(2,sizeof($xml->getSkipDays()));
		 * eq(2,sizeof($xml->getSkipHours()));
		 */
		$src = $tag->get();
		$this->setLanguage($tag->getInValue("language"));
		$this->setCopyright($tag->getInValue("copyright"));
		$this->setDocs($tag->getInValue("docs"));
		$this->setLastBuildDate($tag->getInValue("lastBuildDate"));
		$this->setManagingEditor($tag->getInValue("managingEditor"));
		$this->setPubDate($tag->getInValue("pubDate"));
		$this->setWebMaster($tag->getInValue("webMaster"));

		foreach($tag->getIn("skipDays") as $intag){
			foreach($intag->getIn("day") as $day){
				$this->setSkipDays($day->getValue());
			}
		}
		foreach($tag->getIn("skipHours") as $intag){
			foreach($intag->getIn("hour") as $hour){
				$this->setSkipHours($hour->getValue());
			}
		}
		foreach($tag->getIn("image") as $intag){
			$data = new RssImage09();
			$data->set($intag->get());
			$this->imageList[] = $data;
			
			$src = str_replace($intag->getPlain(),"",$src);			
		}
		foreach($tag->getIn("textinput") as $intag){
			$data = new RssTextinput();
			$data->set($intag->get());
			$this->textinputList[] = $data;

			$src = str_replace($intag->getPlain(),"",$src);						
		}
		foreach($tag->getIn("item") as $intag){
			$data = new RssItem();
			$data->set($intag->get());
			$this->itemList[] = $data;
			
			$src = str_replace($intag->getPlain(),"",$src);			
		}
		return parent::_set(new SimpleTag("channel",$src));
	}
	function _get($outTag){
		/***
		 * $src = <<< __XML__
		 * <channel>
		 * <title>rhaco</title>
		 * <link>http://rhaco.org</link>
		 * <description>php</description>
		 * <language>ja</language>
		 * <copyright>rhaco.org</copyright>
		 * <docs>hogehoge</docs>
		 * <lastBuildDate>2007-10-10T10:10:10+09:00</lastBuildDate>
		 * <managingEditor>tokushima</managingEditor>
		 * <pubDate>2007-10-10T10:10:10+09:00</pubDate>
		 * <webMaster>kazutaka</webMaster>
		 * <skipDays>
		 * <day>1</day>
		 * <day>20</day>
		 * </skipDays>
		 * <skipHours>
		 * <hour>2</hour>
		 * <hour>10</hour>
		 * </skipHours>
		 * <image><title>rhaco</title><link>http://rhaco.org</link><url>http://rhaco.org</url><width>100</width><height>200</height></image>
		 * <image><title>everes</title><link>http://www.everes.net</link><url>http://everes.net</url><width>100</width><height>200</height></image>
		 * <textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>
		 * <textinput><title>everes</title><name>tsuyuki</name><link>http://www.everes.net</link><description>everes desc</description></textinput>
		 * <item><title>rhaco</title><link>http://rhaco.org</link><description>rhaco desc</description></item>
		 * <item><title>everes</title><link>http://www.everes.net</link><description>everes desc</description></item>
		 * </channel>
		 * __XML__;
		 * 
		 * 
		 * $xml = new RssChannel09();
		 * assert($xml->set($src));
		 * 
		 * eq(str_replace("\n","",$src),$xml->get());
		 * 
		 */
		$outTag = parent::_get($outTag);
		
		if(!empty($this->language)) $outTag->addValue(new SimpleTag("language",$this->getLanguage()));
		if(!empty($this->copyright)) $outTag->addValue(new SimpleTag("copyright",$this->getCopyright()));
		if(!empty($this->docs)) $outTag->addValue(new SimpleTag("docs",$this->getDocs()));

		if(intval($this->lastBuildDate) > 0) $outTag->addValue(new SimpleTag("lastBuildDate",$this->getLastBuildDate()));
		if(!empty($this->managingEditor)) $outTag->addValue(new SimpleTag("managingEditor",$this->getManagingEditor()));
		if(intval($this->pubDate) > 0) $outTag->addValue(new SimpleTag("pubDate",$this->getPubDate()));
		if(!empty($this->webMaster)) $outTag->addValue(new SimpleTag("webMaster",$this->getWebMaster()));

		if(!empty($this->skipDaysList)){
			$tag = new SimpleTag("skipDays");
			foreach($this->getSkipDays() as $data){
				$tag->addValue(new SimpleTag("day",$data));
			}
			$outTag->addValue($tag);
		}
		if(!empty($this->skipHoursList)){
			$tag = new SimpleTag("skipHours");
			foreach($this->getSkipHours() as $data){
				$tag->addValue(new SimpleTag("hour",$data));
			}
			$outTag->addValue($tag);
		}

		foreach($this->getImage() as $data){
			if(Variable::istype("RssImage09",$data)) $outTag->addValue($data->get());
		}
		foreach($this->getTextinput() as $data){
			if(Variable::istype("RssTextinput",$data)) $outTag->addValue($data->get());
		}
		foreach($this->getItem() as $data){
			if(Variable::istype("RssItem",$data)) $outTag->addValue($data->get());
		}
		return $outTag;
	}
	function setLanguage($value){
		$this->language = $value;
	}
	function getLanguage(){
		return $this->language;
	}
	function setCopyright($value){
		$this->copyright = $value;
	}
	function getCopyright(){
		return $this->copyright;
	}
	function setDocs($value){
		$this->docs = $value;
	}
	function getDocs(){
		return $this->docs;
	}
	function setLastBuildDate($value){
		$this->lastBuildDate = DateUtil::parseString($value);;
	}
	function getLastBuildDate($format=""){
		return (empty($format)) ? DateUtil::formatW3C($this->lastBuildDate) : DateUtil::format($this->lastBuildDate,$format);
	}
	function setManagingEditor($value){
		$this->managingEditor = $value;
	}
	function getManagingEditor(){
		return $this->managingEditor;
	}
	function setPubDate($value){
		$this->pubDate = DateUtil::parseString($value);
	}
	function getPubDate($format=""){
		return (empty($format)) ? DateUtil::formatW3C($this->pubDate) : DateUtil::format($this->pubDate,$format);
	}
	function setWebMaster($value){
		$this->webMaster = $value;
	}
	function getWebMaster(){
		return $this->webMaster;
	}
	function setSkipDays($value){
		$this->skipDaysList[] = intval($value);
	}
	function getSkipDays(){
		return $this->skipDaysList;
	}
	function setSkipHours($value){
		$this->skipHoursList[] = intval($value);
	}
	function getSkipHours(){
		return $this->skipHoursList;
	}
	function getImage(){
		return $this->imageList;
	}
	function getItem(){
		return $this->itemList;
	}
	function getTextinput(){
		return $this->textinputList;
	}
	function setItem($titleOrObject,$description="",$link="",$about=""){
		$this->itemList[] = (Variable::istype("RssItem",$titleOrObject)) ? $titleOrObject :
																			new RssItem($titleOrObject,$description,$link,$about);
	}
	function setImage($titleOrObject,$url="",$link="",$about=""){
		$this->imageList[] = (Variable::istype("RssImage09",$titleOrObject)) ? $this->imageList[] = $titleOrObject :
																				$this->imageList[] = new RssImage09($titleOrObject,$url,$link,$about);
	}
	function setTextinput($titleOrObject,$name="",$description="",$link="",$about=""){
		$this->textinputList[] = (Variable::istype("RssTextinput",$titleOrObject)) ? $this->textinputList[] = $titleOrObject :
																					new RssTextinput($titleOrObject,$name,$description,$link,$about);
	}
}