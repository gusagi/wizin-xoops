<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.feed.Rss");
Rhaco::import("tag.feed.model.RssChannel20");
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Rss20 extends Rss{
	var $version;

	function Rss20($titleOrChannel="",$description="",$link="",$language=""){
		$this->channel = new RssChannel20($titleOrChannel,$description,$link,$language);
	}
	
	/**
	 * 文字列からRss20をセットする
	 *
	 * @param string $src
	 */	
	function set($src){
		/***
		 * unit("tag.feed.Rss20Test");
		 * 
		 * $src = <<< __XML__
		 * <rss version="2.0">
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
		 * </rss>
		 * __XML__;
		 * 
		 * 
		 * $feed = new Rss20();
		 * assert($feed->set($src));
		 * 
		 * eq("2.0",$feed->getVersion());
		 * $xml = $feed->getChannel();
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
		$tag = new SimpleTag();

		if($tag->set($src,"rss")){
			$this->setVersion($tag->getParameter("version"));
	
			foreach($tag->getIn("channel") as $intag){
				$data = new RssChannel20();
				$data->set($intag->get());
				$this->channel = $data;
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
		 * unit("tag.feed.Rss20Test");
		 * 
		 * $src = <<< __XML__
		 * <rss version="2.0">
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
		 * </rss>
		 * __XML__;
		 * 
		 * 
		 * $xml = new Rss20();
		 * assert($xml->set($src));
		 * 
		 * eq(str_replace("\n","",$src),$xml->get());
		 * 
		 */
		$outTag	= new SimpleTag("rss");
		$outTag->setParameter("version","2.0");
		
		if(Variable::istype("RssChannel20",$this->getChannel())){
			$channel = $this->getChannel();
			$outTag->addValue($channel->get());
		}
		return $outTag->get();
	}
	function setChannel($titleOrChannel,$description="",$link="",$language=""){
		$this->channel = (Variable::istype("RssChannel20",$titleOrChannel)) ? Variable::copy($titleOrChannel) : new RssChannel20($titleOrChannel,$description,$link,$language);
	}
	function getChannel(){
		return (Variable::istype("RssChannel20",$this->channel)) ? $this->channel : new RssChannel20();
	}
	function setVersion($value){
		$this->version = $value;
	}
	function getVersion(){
		return $this->version;
	}
	function getItem(){
		if(Variable::istype("RssChannel20",$this->channel)) return $this->channel->getItem();
		return array();
	}
	function setItem($titleOrObject,$description="",$link="",$about=""){
		if(!Variable::istype("RssChannel20",$this->channel)){
			$this->channel = new RssChannel20($title,$description,$link);
		}
		foreach(ArrayUtil::arrays($titleOrObject) as $obj){
			if(Variable::istype("RssItem20",$obj)){
				$this->channel->setItem($obj);
			}else{
				$this->channel->setItem($obj,$description,$link,$about);
			}
		}
	}
}
?>