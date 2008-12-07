<?php
Rhaco::import("resources.Message");
Rhaco::import("io.FileUtil");
Rhaco::import("io.Snapshot");
Rhaco::import("util.Logger");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("network.Url");
/**
 * キャッシュを操作するクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Cache{
	/**
	 * $urlsが生存期間を超えているか
	 *
	 * @param array / string $urls
	 * @param 生存時間 $expiryTime
	 * @return boolean
	 */
	function isExpiry($urls,$expiryTime=86400){
		return ((Cache::time($urls) + $expiryTime) < time());
	}
	/**
	 * キャッシュファイルを作成する
	 *
	 * @param array / string $urls
	 * @param string $source
	 */
	function set($urls,$source){
		$path = Cache::getFilepath($urls);
		
		if(!is_string($source)){
			$source = serialize($source);
			$path = $path."_s";
		}
		if(FileUtil::write($path,$source)){
			Logger::debug(Message::_("made cache file [{1}]",$path));			
			return true;
		}
		Logger::warning(Message::_("fails in made cache [{1}]",$path));
		return false;
	}	
	/**
	 * キャッシュを取得する
	 *
	 * @param array / string $urls
	 * @return string / false
	 */
	function get($urls){
		$path = Cache::getFilepath($urls);
		$state = 0;

		if(is_file($path)) $state = 1;
		if(is_file($path."_s")){
			$path = $path."_s";
			$state = 2;
		}
		if($state !== 0){
			Logger::debug(Message::_("read cache [{1}]",$path));
			return ($state === 2) ? unserialize(FileUtil::read($path)) : FileUtil::read($path);
		}
		Logger::warning(Message::_("fails in read cache [{1}]",Cache::path($urls)));
		return null;
	}	
	/**
	 * キャッシュの内容をPHPとして実行する
	 *
	 * @param array / string $urls
	 * @param array $variables
	 * @return string / false
	 */
	function execute($urls,$variables=array()){
		$rhaco_cache_path = Cache::getFilepath($urls);

		if(FileUtil::isFile($rhaco_cache_path)){
			Logger::debug(Message::_("execute cache [{1}]",$rhaco_cache_path));
			$rhaco_snapshot = new Snapshot();
			Rhaco::execute($rhaco_cache_path,$variables,true);
			return $rhaco_snapshot->get();
		}
		Logger::warning(Message::_("fails in execute cache [{1}]",$rhaco_cache_path));
		return false;
	}

	/**
	 * キャッシュを削除する
	 *
	 * @param array / string $urls
	 * @return boolean
	 */
	function clear($urls=array()){
		$path = Cache::getFilepath($urls);
		$path = (is_file($path)) ? $path : (is_file($path."_s") ? $path."_s" : null);
		$bool = true;
		
		if($path !== null){
			foreach(FileUtil::ls($path) as $file){
				if(!FileUtil::rm($file)){
					Logger::debug(Message::_("fails in deletion [{1}]",$file->getFullname()));
					$bool = false;
				}
			}
			if($bool) Logger::debug(Message::_("cache deleted [{1}]",$path));
		}
		return $bool;
	}	
	/**
	 * キャッシュの更新時間を取得する
	 *
	 * @param array / string $urls
	 * @return int
	 */
	function time($urls){
		$path = Cache::getFilepath($urls);
		if(is_file($path)) return FileUtil::time($path);
		if(is_file($path."_s")) return FileUtil::time($path."_s");
		return 0;
	}
	/**
	 * キャッシュファイルが存在するか
	 *
	 * @param array / string $urls
	 * @return boolean
	 */
	function isExists($urls){
		$path = Cache::getFilepath($urls);
		return (is_file($path) || is_file($path."_s"));
	}
	
	/**
	 * キャッシュのパスを取得する
	 *
	 * @param array / string $urls
	 * @return string
	 */
	function path($urls){
		/***
		 * eq("2ccfea6a1c6fb5cfa92ab9dd763e86f1",Cache::path("/var/rhaco/templates/hoge.html"));
		 * eq("682493e7ae5aa9f55a4d4b2017724f1a",Cache::path("/var/rhaco/templates/hoge.html?hoge=abc&def=geko"));
		 * eq("e6f85c0f789c9758c051b011db6fa91a",Cache::path(array("/var/rhaco/templates/hoge.html?hoge=abc&def=geko","http://rhaco.org/hoge/abc")));
		 */
		return md5(ArrayUtil::implode($urls));
	}
	function getFilepath($urls){
		return Url::parseAbsolute(((Rhaco::constant("CACHE_PATH") == null) ? 
					Rhaco::constant("CONTEXT_PATH") : 
					Rhaco::constant("CACHE_PATH")),Cache::path($urls));
	}
}
?>