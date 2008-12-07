<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.feed.model.NetscapeBookmarkItem");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class NetscapeBookmarkBlock{
	var $category = "";
	var $itemList = array();
	var $blockList = array();
	
	var $plain = "";

	function set($src,$category=""){
		/***
		 * $src = <<< __XML__
		 * <DL><p>
		 *     <DT><A HREF="http://ja.add-ons.mozilla.com/ja/firefox/bookmarks/">ブックマークのアドオンを入手</A>
		 *     <HR>
		 * 
		 *     <DT><H3 LAST_MODIFIED="1204351961">ブックマークツールバーフォルダ</H3>
		 * <DD>このフォルダの中身がブックマークツールバーに表示されます。
		 *     <DL><p>
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/central/">Firefox を使ってみよう</A>
		 *         <DT><A HREF="http://ja.fxfeeds.mozilla.com/ja/firefox/livebookmarks/" LAST_MODIFIED="1204809368">最新ニュース</A>
		 *         <DT><A HREF="https://direct.smbc.co.jp/aib/aibgsjsw5001.jsp" ADD_DATE="1204351961" LAST_VISIT="1204352154" LAST_CHARSET="Shift_JIS" ID="rdf:#\$gRzO72">One&#39;sダイレクト</A>
		 *     </DL><p>
		 * 
		 *     <HR>
		 *     <DT><H3 ID="rdf:#\$ZvPhC3">Mozilla Firefox</H3>
		 *     <DL><p>
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/help/">ヘルプとチュートリアル</A>
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/customize/">Firefox をカスタマイズしてみよう</A>
		 *     </DL><p>
		 * 
		 *     <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/about/" ADD_DATE="1204809362" LAST_CHARSET="UTF-8" ID="rdf:#\$3XgON">Mozilla について</A>
		 * </DL><p>
		 * 
		 * __XML__;
		 * 
		 * $xml = new NetscapeBookmarkBlock();
		 * assert($xml->set($src));
		 * eq(2,sizeof($xml->getItem()));
		 * list($item1,$item2) = $xml->getItem();
		 * eq("ブックマークのアドオンを入手",$item1->getTitle());
		 * eq("http://ja.add-ons.mozilla.com/ja/firefox/bookmarks/",$item1->getLink());
		 * eq("Mozilla について",$item2->getTitle());
		 * eq("http://ja.www.mozilla.com/ja/firefox/about/",$item2->getLink());
		 * 
		 * eq(2,sizeof($xml->getBlock()));
		 * 
		 * list($block1,$block2) = $xml->getBlock();
		 * eq("ブックマークツールバーフォルダ",$block1->getCategory());
		 * eq("Mozilla Firefox",$block2->getCategory());
		 * eq(3,sizeof($block1->getItem()));
		 * eq(2,sizeof($block2->getItem()));
		 */
		if(SimpleTag::setof($tag,$src,"DL")){
			$this->plain = $tag->getPlain();
			$source = $tag->getValue();
			$category = "";

			foreach($tag->getIn(array("H3","DL"),true) as $intag){
				if(strtoupper($intag->getName()) == "H3"){
					$category = $intag->getValue();
				}else{
					$block = new NetscapeBookmarkBlock();
					if($block->set($intag->getPlain())){
						$obj = Variable::copy($block);
						$obj->setCategory($category);
						$this->blockList[] = $obj;
						$block = new NetscapeBookmarkBlock();
					}
					$category = "";
				}
			}
			if(preg_match_all("/<DT>[\s\n\t]*(<A[\s\n\t].+?>.+?<\/A>)/i",$tag->getValue(),$match)){
				foreach($match[1] as $key => $dtvalue){
					$item = new NetscapeBookmarkItem();
					if($item->set($dtvalue)) $this->setItem($item);
				}
			}
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $src = <<< __XML__
		 * <DL><p>
		 *     <DT><A HREF="http://ja.add-ons.mozilla.com/ja/firefox/bookmarks/">ブックマークのアドオンを入手</A>
		 *     <HR>
		 * 
		 *     <DT><H3 LAST_MODIFIED="1204351961">ブックマークツールバーフォルダ</H3>
		 * <DD>このフォルダの中身がブックマークツールバーに表示されます。
		 *     <DL><p>
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/central/">Firefox を使ってみよう</A>
		 *         <DT><A HREF="http://ja.fxfeeds.mozilla.com/ja/firefox/livebookmarks/" LAST_MODIFIED="1204809368">最新ニュース</A>
		 *         <DT><A HREF="https://direct.smbc.co.jp/aib/aibgsjsw5001.jsp" ADD_DATE="1204351961" LAST_VISIT="1204352154" LAST_CHARSET="Shift_JIS" ID="rdf:#\$gRzO72">One&#39;sダイレクト</A>
		 *     </DL><p>
		 * 
		 *     <HR>
		 *     <DT><H3 ID="rdf:#\$ZvPhC3">Mozilla Firefox</H3>
		 *     <DL><p>
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/help/">ヘルプとチュートリアル</A>
		 *         <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/customize/">Firefox をカスタマイズしてみよう</A>
		 *     </DL><p>
		 * 
		 *     <DT><A HREF="http://ja.www.mozilla.com/ja/firefox/about/" ADD_DATE="1204809362" LAST_CHARSET="UTF-8" ID="rdf:#\$3XgON">Mozilla について</A>
		 * </DL><p>
		 * __XML__;
		 * 
		 * $xml = new NetscapeBookmarkBlock();
		 * assert($xml->set($src));
		 * 
		 * $result = <<< __XML__
		 * <dl><p>
		 * <h3 folded>ブックマークツールバーフォルダ</h3>
		 * <dl><p>
		 * <dt><a href="http://ja.www.mozilla.com/ja/firefox/central/">Firefox を使ってみよう</a>
		 * <dt><a href="http://ja.fxfeeds.mozilla.com/ja/firefox/livebookmarks/" last_modified="1204809368">最新ニュース</a>
		 * <dt><a href="https://direct.smbc.co.jp/aib/aibgsjsw5001.jsp" add_date="1204351961" last_visit="1204352154">One&#39;sダイレクト</a>
		 * </dl><p>
		 * <h3 folded>Mozilla Firefox</h3>
		 * <dl><p>
		 * <dt><a href="http://ja.www.mozilla.com/ja/firefox/help/">ヘルプとチュートリアル</a>
		 * <dt><a href="http://ja.www.mozilla.com/ja/firefox/customize/">Firefox をカスタマイズしてみよう</a>
		 * </dl><p>
		 * <dt><a href="http://ja.add-ons.mozilla.com/ja/firefox/bookmarks/">ブックマークのアドオンを入手</a>
		 * <dt><a href="http://ja.www.mozilla.com/ja/firefox/about/" add_date="1204809362">Mozilla について</a>
		 * </dl><p>
		 * 
		 * __XML__;
		 * 
		 * eq($result,$xml->get());
		 * 
		 */
		$src = "";
		if($this->category != ""){
			$tag = new SimpleTag("h3",$this->getCategory());
			$tag->setAttribute("folded");
			$src .= $tag->get()."\n";
		}
		$src .= "<dl><p>\n";
		foreach($this->getBlock() as $block){
			if(Variable::istype("NetscapeBookmarkBlock",$block)) $src .= $block->get();
		}
		foreach($this->getItem() as $item){
			if(Variable::istype("NetscapeBookmarkItem",$item)) $src .= $item->get();
		}
		$src .= "</dl><p>\n";
		return $src;
	}
	function setCategory($value){
		$this->category = $value;
	}
	function getCategory(){
		return $this->category;
	}
	function getBlock(){
		return ArrayUtil::arrays($this->blockList);
	}
	function getItem(){
		return ArrayUtil::arrays($this->itemList);
	}
	function getItems(){
		$items = $this->getItem();
		
		foreach($this->getBlock() as $block){
			$items = array_merge($items,$block->getItems());
		}
		return $items;
	}
	function setItem($titleOrObject,$description="",$link=""){
		if(Variable::istype("NetscapeBookmarkItem",$titleOrObject)){
			$this->itemList[] = $titleOrObject;
		}else{
			$this->itemList[] = new NetscapeBookmarkItem(SimpleTag::xmltext($titleOrObject),SimpleTag::xmltext($description),$link);
		}		
	}
	function getPlain(){
		return $this->plain;
	}
}