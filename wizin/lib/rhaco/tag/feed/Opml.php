<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.DateUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.feed.model.OpmlOutline");
Rhaco::import("tag.model.SimpleTag");
/**
 * Opml Model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Opml{
	var $version = "1.0";
	
	var $title = "";
	var $dateCreated = "";
	var $dateModified = "";
	var $ownerName = "";
	var $ownerEmail = "";
	var $expansionState = "";
	var $vertScrollState = "";
	var $windowTop = "";
	var $windowLeft = "";
	var $windowBottomv;
	var $windowRight = "";

	var $outlineList = array();

	/**
	 * 文字列からOpmlをセットする
	 *
	 * @param string $src
	 */	
	function set($src){
		/*** unit("tag.feed.OpmlTest"); */
		if(SimpleTag::setof($tag,$src,"opml")){
			$this->setTitle($tag->getInValue("title"));
			$this->setDateCreated($tag->getInValue("dateCreated"));
			$this->setDateModified($tag->getInValue("dateModified"));
			$this->setOwnerName($tag->getInValue("ownerName"));
			$this->setOwnerEmail($tag->getInValue("ownerEmail"));
			$this->setExpansionState($tag->getInValue("expansionState"));
			$this->setVertScrollState($tag->getInValue("vertScrollState"));
			$this->setWindowTop($tag->getInValue("windowTop"));
			$this->setWindowLeft($tag->getInValue("windowLeft"));
			$this->setWindowBottom($tag->getInValue("windowBottom"));
			$this->setWindowRight($tag->getInValue("windowRight"));
			
			foreach($tag->getIn("outline") as $intag) $this->setOutline($intag->get());
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
		/*** unit("tag.feed.OpmlTest"); */
		$outTag	= new SimpleTag("opml");
		$outTag->setParameter("version",$this->getVersion());

		$headTag = new SimpleTag("head");
		if($this->getTitle() != "") $headTag->addValue(new SimpleTag("title",$this->getTitle()));		
		if($this->getDateCreated() != "") $headTag->addValue(new SimpleTag("dateCreated",$this->getDateCreated()));
		if($this->getDateModified() != "") $headTag->addValue(new SimpleTag("dateModified",$this->getDateModified()));
		if($this->getOwnerName() != "") $headTag->addValue(new SimpleTag("ownerName",$this->getOwnerName()));
		if($this->getOwnerEmail() != "") $headTag->addValue(new SimpleTag("ownerEmail",$this->getOwnerEmail()));	
		if($this->getExpansionState() != "") $headTag->addValue(new SimpleTag("expansionState",$this->getExpansionState()));
		if($this->getVertScrollState() != "") $headTag->addValue(new SimpleTag("vertScrollState",$this->getVertScrollState()));
		if($this->getWindowTop() != "") $headTag->addValue(new SimpleTag("windowTop",$this->getWindowTop()));
		if($this->getWindowLeft() != "") $headTag->addValue(new SimpleTag("windowLeft",$this->getWindowLeft()));
		if($this->getWindowBottom() != "") $headTag->addValue(new SimpleTag("windowBottom",$this->getWindowBottom()));
		if($this->getWindowRight() != "") $headTag->addValue(new SimpleTag("windowRight",$this->getWindowRight()));				

		$outTag->addValue($headTag->get());

		$bodyTag	= new SimpleTag("body");
		foreach($this->getOutline() as $outline){
			if(Variable::istype("OpmlOutline",$outline)){
				$bodyTag->addValue($outline->get());
			}
		}
		$outTag->addValue($bodyTag->get());
		
		return $outTag->get();
	}
	
	/**
	 * 出力する
	 *
	 * @param string $name
	 */
	function output($name=""){
		/*** unit("tag.feed.OpmlTest"); */
		header(sprintf("Content-Type: application/xml; name=%s",(empty($name)) ? uniqid("") : $name));
		print("<?xml version=\"1.0\" encoding=\"utf-8\"?>\n".StringUtil::encode($this->get()));
		Rhaco::end();
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
	function setDateCreated($value){
		$this->dateCreated = DateUtil::parseString($value);
	}
	function getDateCreated($format=""){
		return (empty($format)) ? DateUtil::formatRss($this->dateCreated) : DateUtil::format($this->dateCreated,$format);
	}
	function setDateModified($value){
		$this->dateModified = DateUtil::parseString($value);
	}
	function getDateModified($format=""){
		return (empty($format)) ? DateUtil::formatRss($this->dateModified) : DateUtil::format($this->dateModified,$format);
	}
	function setOwnerName($value){
		$this->ownerName = $value;
	}
	function getOwnerName(){
		return $this->ownerName;
	}
	function setOwnerEmail($value){
		$this->ownerEmail = $value;
	}
	function getOwnerEmail(){
		return $this->ownerEmail;
	}
	function setExpansionState($value){
		$this->expansionState = $value;
	}
	function getExpansionState(){
		return $this->expansionState;
	}
	function setVertScrollState($value){
		$this->vertScrollState = $value;
	}
	function getVertScrollState(){
		return $this->vertScrollState;
	}
	function setWindowTop($value){
		$this->windowTop = intval($value);
	}
	function getWindowTop(){
		return $this->windowTop;
	}
	function setWindowLeft($value){
		$this->windowLeft = intval($value);
	}
	function getWindowLeft(){
		return $this->windowLeft;
	}
	function setWindowBottom($value){
		$this->windowBottom = intval($value);
	}
	function getWindowBottom(){
		return $this->windowBottom;
	}
	function setWindowRight($value){
		$this->windowRight = intval($value);
	}
	function getWindowRight(){
		return $this->windowRight;
	}
	function setOutline($value){
		$this->outlineList	= ArrayUtil::arrays($this->outlineList);	
		$value				= ArrayUtil::arrays($value);			
		
		foreach($value as $outline){
			if(Variable::istype("OpmlOutline",$outline)){
				$this->outlineList[] = $outline;
			}else{
				$opmloutline = new OpmlOutline();
				if($opmloutline->set($outline)){
					$this->outlineList[] = $opmloutline;
				}
			}
		}
	}
	function getOutline(){
		return ArrayUtil::arrays($this->outlineList);
	}
	function getHtmlOutlines(){
		$list = array();
		foreach($this->outlineList as $outline) $list = array_merge($list,$outline->getHtmlOutlines());
		return $list;
	}
	function getXmlOutlines(){
		$list = array();

		foreach($this->outlineList as $outline){
			$list = array_merge($list,$outline->getXmlOutlines());
		}
		return $list;
	}
}
?>