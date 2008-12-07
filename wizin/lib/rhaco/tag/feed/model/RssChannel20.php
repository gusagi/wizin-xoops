<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.feed.model.RssChannel09");
Rhaco::import("tag.feed.model.RssImage09");
Rhaco::import("tag.feed.model.RssItem20");
Rhaco::import("tag.feed.model.RssCloud");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssChannel20 extends RssChannel09{
	var $cloud = "";

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
		 * <cloud domain="http://rhaco.org" port="80" protocol="http" path="/" />
		 * </channel>
		 * __XML__;
		 * 
		 * 
		 * $xml = new RssChannel20();
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
		 * 
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
		 * 	assert(Variable::istype("RssItem20",$data));
		 * }
		 * 
		 * eq(2,sizeof($xml->getSkipDays()));
		 * eq(2,sizeof($xml->getSkipHours()));
		 * 
		 * assert(Variable::istype("RssCloud",$xml->getCloud()));
		 */
		if(parent::_set($tag)){
			foreach($tag->getIn("cloud") as $intag){
				$data = new RssCloud();
				$data->set($intag->get());
				$this->setCloud($data);
			}
			$this->itemList = array();
			foreach($tag->getIn("item") as $intag){
				$data = new RssItem20();
				$data->set($intag->get());
				$this->itemList[] = $data;
			}
			return true;
		}
		return false;
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
		 * <cloud domain="http://rhaco.org" port="80" protocol="http" path="/" />
		 * </channel>
		 * __XML__;
		 * 
		 * 
		 * $xml = new RssChannel20();
		 * assert($xml->set($src));
		 * 
		 * eq(str_replace("\n","",$src),$xml->get());
		 * 
		 */
		$outTag = parent::_get($outTag);
		if(Variable::istype("RssCloud",$this->cloud)){
			$cloud = $this->getCloud();
			$outTag->addValue($cloud->get());
		}
		return $outTag;
	}
	function setCloud($value){
		if(!empty($value)) $this->cloud = $value;
	}
	function getCloud(){
		return $this->cloud;
	}
	function setItem($titleOrObject,$description="",$link="",$about=""){
		if(Variable::istype("RssItem",$titleOrObject)){
			$this->itemList[] = $titleOrObject;
		}else{
			$this->itemList[] = new RssItem20($titleOrObject,$description,$link,$about);
		}
	}
	function setImage($titleOrObject,$url="",$link="",$about=""){
		if(Variable::istype("RssImage09",$titleOrObject)){
			$this->imageList[] = $titleOrObject;
		}else{
			$this->imageList[] = new RssImage09($titleOrObject,$url,$link,$about);
		}
	}
	function setTextinput($titleOrObject,$name="",$description="",$link="",$about=""){
		if(Variable::istype("RssTextinput",$titleOrObject)){
			$this->textinputList[] = $titleOrObject;
		}else{
			$this->textinputList[] = new RssTextinput($titleOrObject,$name,$description,$link,$about);
		}
	}
	function getItem(){
		return $this->itemList;
	}
}