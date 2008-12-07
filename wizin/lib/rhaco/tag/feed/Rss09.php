<?php
Rhaco::import("tag.feed.Rss");
Rhaco::import("tag.feed.model.RssChannel09");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.Variable");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Rss09 extends Rss{
	var $version;

	function Rss09(){
		$this->channel = new RssChannel09();
	}
	
	/**
	 * 文字列からRss09をセットする
	 *
	 * @param string $src
	 */	
	function set($src){
		/***
		 * $src = <<< __XML__
		 * <rss version="0.91">
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
		 * </rss>
		 * __XML__;
		 * 
		 * 
		 * $feed = new Rss09();
		 * assert($feed->set($src));
		 * 
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
		$tag = new SimpleTag();

		if(!$tag->set($src,"rss")){
			return false;
		}
		$this->setVersion($tag->getParameter("version"));

		foreach($tag->getIn("channel") as $intag){
			$data = new RssChannel09();
			$data->set($intag->get());
			$this->channel = $data;
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
		 * <rss version="0.91">
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
		 * </rss>
		 * __XML__;
		 * 
		 * 
		 * $xml = new Rss09();
		 * assert($xml->set($src));
		 * 
		 * $doc = "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\" \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">";
		 * eq($doc."\n".str_replace("\n","",$src),$xml->get());
		 * 
		 */		
		$outTag	= new SimpleTag("rss");
		$outTag->setParameter("version","0.91");
		
		if(Variable::istype("RssChannel09",$this->getChannel())){
			$channel = $this->getChannel();
			$outTag->addValue($channel->get());
		}
		return sprintf("%s\n%s",
				"<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\" \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">",
				$outTag->get()
				);
	}
	function setVersion($value){
		$this->version = $value;
	}
	function getVersion(){
		return $this->version;
	}
	function setChannel($titleOrChannel,$description="",$link="",$language=""){
		$this->channel = (Variable::istype("RssChannel09",$titleOrChannel)) ? Variable::copy($titleOrChannel) : new RssChannel09($titleOrChannel,$description,$link,$language);
	}
	function getChannel(){
		return (Variable::istype("RssChannel09",$this->channel)) ? $this->channel : new RssChannel09();
	}
	function getItem(){
		if(Variable::istype("RssChannel09",$this->channel)) return $this->channel->getItem();
		return array();
	}
	function setItem($title,$description="",$link="",$about=""){
		if(!Variable::istype("RssChannel09",$this->channel)){
			$this->channel = new RssChannel09($title,$description,$link);
		}
		if(Variable::istype("RssItem09",$title)){
			$this->channel->setItem($title);
		}else{
			$this->channel->setItem($title,$description,$link,$about);
		}		
	}
}
?>