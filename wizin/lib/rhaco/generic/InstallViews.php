<?php
Rhaco::import("io.FileUtil");
Rhaco::import("tag.feed.model.RssItem20");
Rhaco::import("tag.feed.Rss20");
Rhaco::import("util.DocUtil");
Rhaco::import("generic.Flow");
Rhaco::import("network.Url");
Rhaco::import("setup.model.ProjectModel");
/**
 * インストーラーのViews
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008 rhaco project. All rights reserved.
 */
class InstallViews extends Flow{
	var $params = array();	

	/**
	 * 標準のURLパターン
	 * 
	 * @static
	 * @param unknown_type $path
	 * @param unknown_type $url
	 * @return unknown
	 */
	function pattern($path,$url){
		$pattern = array();

		$pattern["^$"] = array(
							"class"=>"generic.InstallViews",
							"method"=>"dir",
							"args"=>array(
								$path,
								$url,
								),
							);
		foreach(FileUtil::dirs($path) as $dir){
			$name = basename($dir);
			if(FileUtil::isPublic($dir)){
				$pattern["^".$name."/up[\/]*$"] = array(
												"class"=>"generic.InstallViews",
												"method"=>"upload",
												"args"=>$dir
												);
				$pattern["^".$name."[\/]*$"] = array(
												"class"=>"generic.InstallViews",
												"method"=>"show",
												"args"=>array(
														$name,
														$dir,
														Url::parseAbsolute($url,$name."/up"),
													)
												);
			}
		}
		return $pattern;
	}

	/**
	 * インストールパス
	 * @static 
	 *
	 * @param unknown_type $category
	 * @param unknown_type $name
	 * @return unknown
	 */
	function installPath($url,$name){
		$info = parse_url($url);
		$query = (isset($info["query"])) ? explode("&",$info["query"]) : array();
		$category = "";
		$path = "";

		foreach($query as $q){
			if(preg_match("/^(.+)=(.+)$/",$q,$match)){
				switch($match[1]){
					case "category":
						$category = $match[2];
						break;
					case "url":
						$path = $match[2];
						break;
				}
			}
		}
		switch($category){
			case "application":
				return array("application",Rhaco::path());
			case "file":
				return array("file",Rhaco::constant(strtoupper($name."_PATH"),Rhaco::lib($name)));
			case "package":
				return array("package",Rhaco::constant(strtoupper($name."_PATH"),Rhaco::lib($name)));
		}
		return array("",Rhaco::path($name));
	}
	
	/**
	 * ディレクトリ一覧
	 *
	 * @param unknown_type $path
	 * @param unknown_type $linkpath
	 */
	function dir($path,$linkpath){
		$rss = new Rss20();

		foreach(FileUtil::dirs($path) as $dir){
			if(FileUtil::isPublic($dir)){
				$name = basename($dir);
				$item = new RssItem20();
				$item->setLink(Url::parseAbsolute($linkpath,$name));
				$item->setTitle($name);
				$item->setDescription($path);

				$rss->setItem($item);
			}
		}
		Logger::disableDisplay();
		$rss->output();
	}
	
	/**
	 * アップロード用
	 *
	 * @param unknown_type $basepath
	 */
	function upload($basepath){
		$result = "";
		$path = FileUtil::path($basepath,$this->getVariable("url"));
		$category = $this->getVariable("category");

		Logger::disableDisplay();
		if($category == "file"){
			print(FileUtil::pack(array($this->getVariable("url")=>$path)));
		}else{
			print(FileUtil::pack($path));
		}
		Rhaco::end();
	}

	function _toShowItem($name,$file,$basepath,$linkpath,$category){
		$path = str_replace($basepath,"",(basename($file->getDirectory()) == $file->getOriginalname()) ? $file->getDirectory() : $file->getFullname());
		if(substr($path,0,1) == "/") $path = substr($path,1);
		$doc = new DocUtil($file->read(),$file->getFullname());
		$item = new RssItem20();
		$item->setLink($linkpath."?url=".$path."&category=".$category);
		$item->setPubDate($file->getUpdate());
		$item->setTitle($path);
		$item->setCategory($category);
		$item->setDescription($doc->description);
		$item->setComments($doc->version);

		return $item;
	}
	function _queryCheck($query,$file){
		foreach($query as $q){
			if(strpos(strtolower($file->getFullname()),$q) === false){
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 閲覧用
	 *
	 * @param unknown_type $name
	 * @param unknown_type $basepath
	 * @param unknown_type $linkpath
	 * @param unknown_type $find
	 */
	function show($name,$basepath,$linkpath,$find=".php"){
		$rss = new Rss20();

		if(is_file($basepath)){
			$rss->setItem($this->_toShowItem($name,new File($basepath),$basepath));
		}else{
			$result = array();
			$package = array();
			$query = array();
			$files = FileUtil::ls($basepath,true);
	
			foreach(explode(" ",str_replace("　"," ",$this->getVariable("q"))) as $q){
				if(!empty($q)) $query[] = strtolower($q);
			}
			foreach($files as $file){
				if(!isset($package[$file->getDirectory()])){
					if(
						$file->getName() == "Rhaco.php" ||
						$file->getName() == "setup.php" || 
						($file->getExtension() == $find && basename($file->getDirectory()) == $file->getOriginalname())){
						$package[$file->getDirectory()] = $file;
					}
				}
			}
			foreach($files as $file){
				if(FileUtil::isPublic($file->getFullname())){
					$bool = true;
	
					if($find === null || $file->getExtension() == $find){
						foreach($package as $p => $f){
							if(strpos($file->getDirectory(),$p) === 0){
								$bool = false;
								break;
							}
						}
						if($bool && $this->_queryCheck($query,$file)){
							$rss->setItem($this->_toShowItem($name,$file,$basepath,$linkpath,"file"));
						}
					}
				}
			}
			foreach($package as $file){
				if($this->_queryCheck($query,$file)){
					if($file->getName() == "Rhaco.php"){
							$ver = basename($file->getDirectory());
						
							$item = new RssItem20();
							$item->setLink($linkpath."?url=".str_replace($basepath,"",$file->getDirectory())."&category=package");
							$item->setPubDate($file->getUpdate());
							$item->setTitle($ver);
							$item->setDescription($ver);
							$item->setComments($ver);
							$item->setCategory("package");
							$rss->setItem($item);
					}else if($file->getName() == "setup.php"){
						$xml = FileUtil::path($file->getDirectory(),"setup/project.xml");

						if(is_file($xml)){
							$model = ProjectModel::toSimpleProjectModel(FileUtil::read($xml));
							
							$item = new RssItem20();
							$item->setLink($linkpath."?url=".str_replace($basepath,"",$file->getDirectory())."&category=application");
							$item->setPubDate($file->getUpdate());
							$item->setTitle($model->name);
							$item->setDescription($model->description);
							$item->setComments($model->version);
							$item->setCategory("application");
							$rss->setItem($item);
						}
					}else{
						$rss->setItem($this->_toShowItem($name,$file,$basepath,$linkpath,"package"));
					}
				}
			}
		}
		Logger::disableDisplay();
		$rss->output();
	}

	function setParam($params){
		$this->params = $params;
	}
	
//Rhaco::import("generic.Urls");
//Rhaco::import("generic.InstallViews");
//
//$parser = Urls::parser(InstallViews::pattern(Rhaco::constant(VAR_RESOURCE_PATH),Rhaco::url()));

}
?>