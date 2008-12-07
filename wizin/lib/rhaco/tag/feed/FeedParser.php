<?php
Rhaco::import("tag.feed.Rss20");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("network.Url");
Rhaco::import("network.http.Http");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.model.File");
Rhaco::import("io.Cache");
Rhaco::import("util.Logger");
Rhaco::import("io.FileUtil");
/**
 * Feed解析クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class FeedParser{
	/**
	 * FeedParser用のキャッシュ定義を行う
	 *
	 * @static
	 * @param string $cachePath
	 * @param int $cacheTime
	 */
	function setCache($cachePath="",$cacheTime=86400){
		Rhaco::constant("FEED_CACHE",true);
		Rhaco::constant("FEED_CACHE_TIME",$cacheTime);
		Rhaco::constant("CACHE_PATH",(empty($cachePath) ? Rhaco::path("work") : $cachePath));
	}

	/**
	 * 一時的に無効にしたキャッシュを有効にする
	 */
	function enableCache(){
		Logger::deep_debug("FeedParser cache on");
		Rhaco::setVariable("FEED_CACHE_OFF",false);
	}
	
	/**
	 * 一時的にキャッシュを無効にする
	 */
	function disableCache(){
		Logger::deep_debug("FeedParser cache off");
		Rhaco::setVariable("FEED_CACHE_OFF",true);
	}
	
	/**
	 * URLからFeedを取得しRSS20として取得
	 *
	 * @static
	 * @param string $url
	 * @param int $time 現在時間-$time秒以内の更新を取得
	 * @return tag.feed.Rss20
	 */
	function read($url,$time=null,$headers=array()){
		/*** unit("tag.feed.FeedParserTest"); */
		Rhaco::getVariable("FEED_CACHE_OFF",false);
		if(
			Rhaco::getVariable("FEED_CACHE_OFF") 
			|| !Rhaco::constant("FEED_CACHE") 
			|| (
					Rhaco::constant("FEED_CACHE_TIME") > 0 
					&& Cache::isExpiry($url,Rhaco::constant("FEED_CACHE_TIME"))
				)
		){
			$src = (preg_match("/[\w]+:\/\/[\w]+/",$url)) ? (($time > 0) ? Http::modified($url,$time) : Http::body($url,"GET",$headers)) : FileUtil::read($url);
			if(!Rhaco::getVariable("FEED_CACHE_OFF") && Rhaco::constant("FEED_CACHE")) Cache::set($url,$src);
		}else{
			$src = Cache::get($url);
		}
		Logger::deep_debug(Message::_("read feed [{1}]",$url));
		return FeedParser::parse(StringUtil::encode($src),$url,$time);
	}
	
	/**
	 * URLからFeedを取得しRssItemをまとめたものを取得
	 *
	 * @static
	 * @param string/array $urls
	 * @param int $time 現在時間-$time秒以内の更新を取得
	 * @return array(tag.feed.model.RssItem20)
	 */
	function getItem($urls,$time=null,$headers=array()){
		/*** unit("tag.feed.FeedParserTest"); */
		$list = array();
		if(is_string($urls)) $urls = explode("\n",StringUtil::toULD($urls));
		
		foreach(ArrayUtil::arrays($urls) as $url){
			$url = trim($url);
			if(!empty($url)){
				$feed = FeedParser::read($url,$time,$headers);
				foreach($feed->getItem() as $item){
					$list[substr(str_pad(DateUtil::parseString($item->getPubDate()).$item->getTitle(),50,"_"),0,50).uniqid("")] = $item;
				}
			}
		}
		krsort($list);
		return $list;
	}
	
	/**
	 * 文字列からtag.feed.Rss20にセットする
	 *
	 * @static
	 * @param string $src
	 * @return tag.feed.Rss20
	 */
	function parse($src,$url=null,$time=null,$headers=array()){
		/*** unit("tag.feed.FeedParserTest"); */
		$toFeed = new Rss20();

		if(empty($src)) return $toFeed;
		$url = FeedParser::alternateUrl($url,$src,$headers);
		if(!empty($url)) return FeedParser::read($url,$time,$headers);

		$fromFeed = new Rss20();
		if($fromFeed->set($src)) return $fromFeed;

		Rhaco::import("tag.feed.Rss10");
		$fromFeed = new Rss10();
		if($fromFeed->set($src)){
			$channel	= $fromFeed->getChannel();
			$toFeed->setChannel($channel->getTitle(),$channel->getDescription(),$channel->getLink());
			foreach($fromFeed->getItem() as $item){
				$toItem	= new RssItem20($item->getTitle(),$item->getDescription(),$item->getLink());				
				$toItem->setPubDate($item->getDate());
				$toItem->setAuthor($item->getCreator());
				$toFeed->setItem($toItem);
			}
			return $toFeed;
		}
		
		Rhaco::import("tag.feed.Rss09");
		$fromFeed = new Rss09();
		if($fromFeed->set($src)){
			$channel = $fromFeed->getChannel();
			$this->feed->setChannel($channel->getTitle(),$channel->getDescription(),$channel->getLink());
			foreach($fromFeed->getItem() as $item){
				$toFeed->setItem($item->getTitle(),$item->getDescription(),$item->getLink());
			}
			return $toFeed;
		}

		Rhaco::import("tag.feed.Atom10");
		$fromFeed = new Atom10();
		if($fromFeed->set($src)){
			$toFeed->setChannel($fromFeed->getTitle(),$fromFeed->getSubTitle(),$fromFeed->getLinkHref());
			foreach($fromFeed->getEntry() as $item){
				$author = $item->getAuthor();				
				$content = $item->getContentValue();
				if(empty($content)) $content = $item->getSummaryValue();
				$toItem	= new RssItem20($item->getTitle(),$content,$item->getLinkHref());
				$date = $item->getPublished();
				$toItem->setPubDate(empty($date) ? $item->getUpdated() : $date);
				$toItem->setAuthor($author->getName());

				$toFeed->setItem($toItem);
			}
			return $toFeed;
		}
		
		Rhaco::import("tag.feed.Atom03");
		$fromFeed = new Atom03();
		if($fromFeed->set($src)){
			$toFeed->setChannel($fromFeed->getTitle(),$fromFeed->getTitle(),$fromFeed->getLink());
			foreach($fromFeed->getEntry() as $item){
				$author = $item->getAuthor();				
				$content = $item->getContentValue();
				if(empty($content)) $content = $item->getSummaryValue();
				$toItem	= new RssItem20($item->getTitle(),$content,$item->getLinkHref());
				$date = $item->getCreated();
				$toItem->setPubDate(empty($date) ? $item->getModified() : $date);
				$toItem->setAuthor($author->getName());

				$toFeed->setItem($toItem);
			}
			return $toFeed;
		}

		Rhaco::import("tag.feed.Opml");
		$fromFeed = new Opml();
		if($fromFeed->set($src)){
			$toFeed->setChannel($fromFeed->getTitle(),$fromFeed->getTitle());

			foreach($fromFeed->getHtmlOutlines() as $outline){
				$item = new RssItem20($outline->getTitle(),$outline->getDescription(),$outline->getHtmlUrl());
				$item->setPubDate(time());
				$item->setComments($outline->getValue());
				$item->setCategory($outline->getTags());
				$toFeed->setItem($item);
			}
			return $toFeed;
		}

		Rhaco::import("tag.feed.NetscapeBookmark");
		$fromFeed = new NetscapeBookmark();
		if($fromFeed->set($src)){
			foreach($fromFeed->getItems() as $item){
				$toItem	= new RssItem20($item->getTitle(),$item->getTags(),$item->getLink());				
				$toItem->setPubDate($item->getCreated());
				$toItem->setComments($item->getDescription());				
				$toItem->setCategory($item->getTags());				
				$toFeed->setItem($toItem);
			}
			return $toFeed;
		}
		return new Rss20();
	}
	
	/**
	 * URLまたは文字列からFeed URLを取得する
	 *
	 * @static
	 * @param string $baseurl
	 * @param string $src
	 * @return string
	 */
	function alternateUrl($baseurl,$src="",$headers=array()){
		/***
		 * $html = <<< __HTML__
		 * <html>
		 * <head>
		 * 	<link rel="alternate" type="application/rss+xml" href="rss" />
		 * 	<link rel="alternate" type="application/rss+xml" href="http://rhaco.org/rss.rdf" />
		 * 	<link rel="alternate" type="application/atom+xml" href="http://rhaco.org/atom" />
		 * </head>
		 * <body>
		 * </body>
		 * </html>
		 * __HTML__;
		 * 
		 * eq("http://rhaco.org/rss",FeedParser::alternateUrl("http://rhaco.org/",$html));
		 * 
		 */
		$src = empty($src) ? Http::body($baseurl,"GET",$headers) : $src;
		if(SimpleTag::setof($tag,$src,"head")){
			foreach($tag->getIn("link") as $link){
				if("alternate" == strtolower($link->getParameter("rel")) &&
					strpos(strtolower($link->getParameter("type")),"application") === 0
				){
					$url = Url::parseAbsolute($baseurl,$link->getParameter("href"));
					if($baseurl != $url) return $url;
				}
			}
		}
		return "";
	}
}
?>