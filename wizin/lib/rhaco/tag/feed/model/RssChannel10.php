<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.feed.model.RssChannel");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssChannel10 extends RssChannel{
	var $about = "";
	var $imageList = array();
	var $itemsList = array();
	var $textinputList = array();

	function RssChannel10($title="",$description="",$link="",$about=""){
		parent::RssChannel($title,$description,$link);		
		$this->setAbout($about);
	}
	function _set($tag){
		/***
		 * $src = <<< __XML__
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
		 * <rdf:li rdf:resource="http://everesnet" />
		 * </rdf:Seq>
		 * </items>
		 * </channel>
		 * __XML__;
		 * 
		 * $xml = new RssChannel10();
		 * assert($xml->set($src));
		 * 
		 * eq("rhaco",$xml->getTitle());
		 * eq("http://rhaco.org",$xml->getLink());
		 * eq("php",$xml->getDescription());
		 * 
		 * eq(2,sizeof($xml->getImage()));
		 * eq(2,sizeof($xml->getTextInput()));
		 * eq(2,sizeof($xml->getItem()));
		 */
		if(parent::_set($tag)){
			$this->setAbout($tag->getParameter("rdf:about"));
	
			foreach($tag->getIn("image") as $intag){
				$this->setImage($intag->getParameter("rdf:resource"));
			}
			foreach($tag->getIn("textinput") as $intag){
				$this->setTextinput($intag->getParameter("rdf:resource"));
			}
			foreach($tag->getIn("items") as $intag){
				foreach($intag->getIn("rdf:Seq") as $inintag){
					foreach($inintag->getIn("rdf:li") as $ininintag){
						$this->setItem($ininintag->getParameter("rdf:resource"));
					}
				}
			}
			return true;
		}
		return false;
	}
	function _get($outTag){
		/***
		 * $src = <<< __XML__
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
		 * <rdf:li rdf:resource="http://everesnet" />
		 * </rdf:Seq>
		 * </items>
		 * </channel>
		 * __XML__;
		 * 
		 * $xml = new RssChannel10();
		 * assert($xml->set($src));
		 * 
		 * eq(str_replace("\n","",$src),$xml->get());
		 */
		$outTag = parent::_get($outTag);
		$outTag->setParameter("rdf:about",$this->getAbout());

		foreach($this->imageList as $value){
			$outTag->addValue(new SimpleTag("image","",array("rdf:resource"=>$value)));
		}
		foreach($this->textinputList as $value){
			$outTag->addValue(new SimpleTag("textinput","",array("rdf:resource"=>$value)));
		}
		if(!empty($this->itemsList)){
			$items = "";
			foreach($this->itemsList as $value){
				$tag = new SimpleTag("rdf:li","",array("rdf:resource"=>$value));
				$items .= $tag->get();
			}
			$seq = new SimpleTag("rdf:Seq",$items);
			$outTag->addValue(new SimpleTag("items",$seq->get()));
			unset($seq,$items);
		}
		return $outTag;
	}	
	function setAbout($value){
		$this->about = $value;
	}
	function getAbout(){
		return $this->about;
	}
	function setImage($value){
		if(!empty($value)) $this->imageList[] = $value;
	}
	function getImage(){
		return $this->imageList;
	}
	function setItem($value){
		if(!empty($value)) $this->itemsList[] = $value;
	}
	function getItem(){
		return $this->itemsList;
	}
	function setTextinput($value){
		if(!empty($value)) $this->textinputList[] = $value;
	}
	function getTextinput(){
		return $this->textinputList;
	}
}