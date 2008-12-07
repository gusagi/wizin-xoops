<?php
Rhaco::import("resources.Message");
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
/**
 * ページを管理するモデル
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class Paginator{
	var $offset = 0;
	var $limit = 20;
	var $total = 0;
	var $nextOffset = 0;
	var $prevOffset = 0;
	var $start = 0;	
	var $last = 0;

	var $page = 1;
	var $pages = 50;
	var $nextPage = 0;
	var $prevPage = 0;
	var $pageFirst = 0;
	var $pageLast = 0;
	var $pageFinish = 0;
	var $variables = array();

	function setOffset($value){
		$this->offset = intval($value);
	}
	function setLimit($value){
		if(empty($value)) $value = 20;
		$this->limit = intval($value);
	}
	function setPage($page,$pages=20){
		if(empty($page)) $page = 1;
		$this->page = intval($page);
		$this->pages = intval($pages);
		$this->nextPage = $this->page + 1;
		$this->prevPage = $this->page - 1;
		$this->setOffset($this->limit * round(abs($this->page - 1)));
	}
	function setTotal($total){
		$this->total = intval($total);

		if($this->total <= 0){
			$this->offset = 0;
			$this->nextOffset = 0;
			$this->prevOffset = 0;
			$this->start = 0;
			$this->last = 0;
		}else{
			$this->nextOffset = $this->offset + $this->limit;
			$this->prevOffset = $this->offset - $this->limit;
			if($this->prevOffset < 0) $this->prevOffset = 0;
			$this->last = (($this->offset + $this->limit) > $this->total) ? $this->total : $this->nextOffset;
			$this->start = $this->offset + 1;

			$pages = intval($this->pages) - 1;
			$this->pageFinish = intval(@ceil($this->total / $this->limit));
			$this->pageLast = $this->pageFinish;
			$this->pageFirst = ($this->page > ($pages/2)) ? @ceil($this->page - ($pages/2)) : 1;
			$this->pageLast = ($this->pageLast > ($this->pageFirst + $pages)) ? ($this->pageFirst + $pages) : $this->pageLast;
			$this->pageFirst = (($this->pageLast  - $pages) > 0) ? ($this->pageLast  - $pages) : $this->pageFirst;
		}
	}
	
	/**
	 * 最後のページを含むか
	 *
	 * @return boolean
	 */
	function isPageFinish(){
		return ($this->pageLast >= $this->pageFinish);
	}
	
	/**
	 * 最初のページを含むか
	 *
	 * @return boolean
	 */
	function isPageFirst(){
		return ($this->pageFirst == 1);
	}
	
	/**
	 * ページ情報メッセージを返す
	 *
	 * @return string
	 */
	function output(){
		/***
		 * $page = new Paginator();
		 * $page->setLimit(2);
		 * $page->setTotal(10);
		 * eq(Message::_("Results {1} - {2} of about {3}",1,2,10),$page->output());
		 * 
		 */
		return Message::_("Results {1} - {2} of about {3}",$this->start,$this->last,$this->total);
	}
	function isNext(){
		/***
		 * $page = new Paginator();
		 * $page->setLimit(2);
		 * $page->setTotal(10);
		 * assert($page->isNext());
		 * $page = new Paginator();
		 * $page->setLimit(2);
		 * $page->setOffset(8);
		 * $page->setTotal(10);
		 * assert(!$page->isNext());
		 */
		if($this->nextOffset > 0 && $this->nextOffset < $this->total) return true;
		return false;
	}
	function isPrev(){
		/***
		 * $page = new Paginator();
		 * $page->setTotal(10);
		 * $page->setLimit(2);
		 * assert(!$page->isPrev());
		 * $page = new Paginator();
		 * $page->setTotal(10);
		 * $page->setLimit(2);
		 * $page->setOffset(3);
		 * assert($page->isPrev());
		 */		
		if($this->offset > 0 && $this->total > $this->offset) return true;
		return false;
	}
	
	/**
	 * ページがあるか
	 *
	 * @return unknown
	 */
	function isPage(){
		return ($this->pageFirst < $this->pageLast);
	}
	
	/**
	 * query用の変数をセットする
	 * @param array/string arrayOrKey
	 * @param unknown_type $value
	 */
	function setVariable($name,$value=null){
		if(!is_array($name)) $name = array($name=>$value);
		$this->variables = array_merge(ArrayUtil::arrays($this->variables),$name);
	}
	
	/**
	 * pagingQueryを返す
	 *
	 * @param unknown_type $page
	 * @return unknown
	 */
	function query($page){
		/***
		 * $paginator = new Paginator();
		 * $paginator->setVariable("hoge",1);
		 * $paginator->setVariable("page",1);
		 * $paginator->setVariable("abc","abc");
		 * 
		 * eq("hoge=1&abc=abc&page=2",$paginator->query(2));
		 */
		return TemplateFormatter::pagingQuery($page,TemplateFormatter::httpBuildQuery($this->variables));
	}
}