<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class NetscapeBookmarkItem{
	var $title = "";
	var $link = "";
	var $description = "";
	var $modified = null;
	var $issued = null;
	var $created = null;
	var $tags = "";

	function NetscapeBookmarkItem($title="",$description="",$link=""){
		$this->setTitle($title);
		$this->setLink($link);
		$this->setDescription($description);
	}
	function set($dtvalue,$ddvalue=""){
		/***
		 * $src = <<< __XML__
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/help/">ヘルプとチュートリアル</A>
		 * __XML__;
		 * 
		 * $xml = new NetscapeBookmarkItem();
		 * assert($xml->set($src));
		 * eq("http://ja.www.mozilla.com/ja/firefox/help/",$xml->getLink());
		 * eq("ヘルプとチュートリアル",$xml->getTitle());
		 * 
		 */
		if(SimpleTag::setof($intag,$dtvalue,"a")){
			$this->setDescription($ddvalue);

			$this->setTitle($intag->getValue());
			$this->setLink($intag->getParameter("href"));
			$this->setTags($intag->getParameter("tags"));
			$this->setCreated($intag->getParameter("add_date"));
			$this->setIssued($intag->getParameter("last_visit"));
			$this->setModified($intag->getParameter("last_modified"));
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $src = <<< __XML__
		 *        <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/help/" ICON="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAHWSURBVHjaYvz//z8DJQAggJiQOe/fv2fv7Oz8rays/N+VkfG/iYnJfyD/1+rVq7ffu3dPFpsBAAHEAHIBCJ85c8bN2Nj4vwsDw/8zQLwKiO8CcRoQu0DxqlWrdsHUwzBAAIGJmTNnPgYa9j8UqhFElwPxf2MIDeIrKSn9FwSJoRkAEEAM0DD4DzMAyPi/G+QKY4hh5WAXGf8PDQ0FGwJ22d27CjADAAIIrLmjo+MXA9R2kAHvGBA2wwx6B8W7od6CeQcggKCmCEL8bgwxYCbUIGTDVkHDBia+CuotgACCueD3TDQN75D4xmAvCoK9ARMHBzAw0AECiBHkAlC0Mdy7x9ABNA3obAZXIAa6iKEcGlMVQHwWyjYuL2d4v2cPg8vZswx7gHyAAAK7AOif7SAbOqCmn4Ha3AHFsIDtgPq/vLz8P4MSkJ2W9h8ggBjevXvHDo4FQUQg/kdypqCg4H8lUIACnQ/SOBMYI8bAsAJFPcj1AAEEjwVQqLpAbXmH5BJjqI0gi9DTAAgDBBCcAVLkgmQ7yKCZxpCQxqUZhAECCJ4XgMl493ug21ZD+aDAXH0WLM4A9MZPXJkJIIAwTAR5pQMalaCABQUULttBGCCAGCnNzgABBgAMJ5THwGvJLAAAAABJRU5ErkJggg==" ID="rdf:#\$22iCK1">ヘルプとチュートリアル</A>
		 * __XML__;
		 * 
		 * $xml = new NetscapeBookmarkItem();
		 * assert($xml->set($src));
		 * 
		 * $result = '<dt><a href="http://ja.www.mozilla.com/ja/firefox/help/">ヘルプとチュートリアル</a>'."\n";
		 * eq($result,$xml->get());
		 */		
		$src = "<dt>";
		$params = array();
		if($this->link != "") $params["href"] = $this->getLink();
		if($this->created !== null) $params["add_date"] = $this->getCreated();
		if($this->issued !== null) $params["last_visit"] = $this->getIssued();
		if($this->modified !== null) $params["last_modified"] = $this->getModified();
		if($this->tags != "") $params["last_modified"] = $this->getTags();				
		
		$tag = new SimpleTag("a",$this->getTitle(),$params);
		$src .= $tag->get()."\n";

		if($this->getDescription() != "") $src .= "<dd>".$this->getDescription()."\n";
		return $src;
	}

	function setTitle($value){
		$this->title = SimpleTag::xmltext($value);
	}
	function getTitle(){
		return $this->title;
	}
	function setLink($value){
		$this->link = $value;
	}
	function getLink(){
		return $this->link;
	}
	function setDescription($value){
		$this->description = $value;
	}
	function getDescription(){
		return $this->description;
	}
	function setModified($value){
		if(!empty($value)) $this->modified = intval($value);
	}
	function getModified(){
		return intval($this->modified);
	}
	function setIssued($value){
		if(!empty($value)) $this->issued = intval($value);
	}
	function getIssued(){
		return intval($this->issued);
	}
	function setCreated($value){
		if(!empty($value)) $this->created = intval($value);
	}
	function getCreated(){
		return intval($this->created);
	}
	function setTags($value){
		if($this->tags != "") $this->tags = trim($this->tags)." ";
		$this->tags .= trim($value);
	}
	function getTags(){
		return $this->tags;
	}
}