<?php
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.feed.model.NetscapeBookmarkBlock");
Rhaco::import("tag.feed.model.NetscapeBookmarkItem");
Rhaco::import("tag.model.SimpleTag");
/**
 * NETSCAPE-Bookmark-file-1　MODEL
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class NetscapeBookmark{
	var $title;
	var $header;
	var $block;

	/**
	 * 文字列からNetscapeBookmarkをセットする
	 *
	 * @param string $src
	 */
	function set($src){
		/*** unit("tag.feed.NetscapeBookmarkTest"); */
		$tag = new SimpleTag("dummy",$src);
		$this->setTitle($tag->getInValue("title"));
		$this->setHeader($tag->getInValue("h1"));
		$this->block = new NetscapeBookmarkBlock();
		$this->block->set($src);
	}
	
	/**
	 * フォーマットされた文字列を取得
	 *
	 * @return string
	 */
	function get(){
		/*** unit("tag.feed.NetscapeBookmarkTest"); */
		$src	= "<!DOCTYPE NETSCAPE-Bookmark-file-1>\n";
		$src 	.= "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">\n";
		$tag	= new SimpleTag("title",$this->getTitle());
		$src	.= $tag->get()."\n";
		$tag	= new SimpleTag("h1",$this->getHeader());
		$src	.= $tag->get()."\n";
		$block	= $this->getBlock();
		$src	.= $block->get();
		return $src;
	}
	
	/**
	 * 出力
	 *
	 * @param string $name
	 */
	function output($name=""){
		/*** unit("tag.feed.NetscapeBookmarkTest"); */
		$name = (empty($name)) ? uniqid("") : $name;
		$src = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n".StringUtil::encode($this->get());

		header(sprintf("Content-Type: application/xml; name=%s",$name));
		print($src);
		Rhaco::end();
	}
	function setTitle($value){
		$this->title = $value;
	}
	function getTitle(){
		return $this->title;
	}
	function setHeader($value){
		$this->header = $value;
	}
	function getHeader(){
		return $this->header;
	}
	function getBlock(){
		if(Variable::istype("NetscapeBookmarkBlock",$this->block)) return $this->block;
		return new NetscapeBookmarkBlock();
	}
	function setItem($titleOrObject,$description="",$link=""){
		$block = $this->getBlock();
		$block->set($titleOrObject,$description,$link);
		$this->block = $block;
	}
	function getItems(){
		$block	= $this->getBlock();
		return $block->getItems();
	}
}
?>