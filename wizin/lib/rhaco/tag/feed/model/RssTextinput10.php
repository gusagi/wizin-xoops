<?php
Rhaco::import("tag.feed.model.RssTextinput");
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssTextinput10 extends RssTextinput{
	var $about = "";

	function RssTextinput10($title="",$name="",$description="",$link="",$about=""){
		$this->setAbout($about);
		parent::RssTextinput($title,$name,$description,$link);
	}

	function _set($tag){
		/***
		 * $src = '<textinput rdf:about="hoge"><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>';
		 * $xml = new RssTextinput10();
		 * assert($xml->set($src));
		 * eq("rhaco",$xml->getTitle());
		 * eq("tokushima",$xml->getName());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("rhaco desc",$xml->getDescription());
		 * eq("hoge",$xml->getAbout());
		 * 
		 */
		if(parent::_set($tag)){
			$this->setAbout($tag->getParameter("rdf:about"));
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $xml = new RssTextinput10();
		 * eq('<textinput />',$xml->get());
		 * 
		 * $xml->setTitle("rhaco");
		 * eq('<textinput><title>rhaco</title></textinput>',$xml->get());
		 * 
		 * $xml->setName("tokushima");
		 * eq('<textinput><title>rhaco</title><name>tokushima</name></textinput>',$xml->get());
		 * 
		 * $xml->setLink("http://rhaco.org");
		 * eq('<textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link></textinput>',$xml->get());
		 * 
		 * $xml->setDescription("rhaco desc");
		 * eq('<textinput><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>',$xml->get());
		 * 
		 * $xml->setAbout("hoge");
		 * eq('<textinput rdf:about="hoge"><title>rhaco</title><name>tokushima</name><link>http://rhaco.org</link><description>rhaco desc</description></textinput>',$xml->get());
		 */
		if($this->about != "") $outTag->setParameter("rdf:about",$this->getAbout());
		$outTag = parent::_get($outTag);
		return $outTag;
	}

	function setAbout($value){
		$this->about = $value;
	}
	function getAbout(){
		return $this->about;
	}
}