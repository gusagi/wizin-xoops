<?php
Rhaco::import("resources.Message");
Rhaco::import("io.FileUtil");
Rhaco::import("util.Logger");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Env");
Rhaco::import("tag.model.TemplateFormatter");
/**
 * スナップショットを操作するクラス
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Snapshot{
	var $start = false;
	var $url = "";
	var $variables = array();
	var $buffer = "";
	var $id = 0;

	/**
	 * スナップショットの取得を開始する
	 *
	 * @param string $url
	 * @param array $variables
	 * @return Snapshot
	 */
	function Snapshot($url="",$variables=array()){
		/***
		 * $snap = new Snapshot();
		 * print("A");
		 * eq("A",$snap->get());
		 * 
		 * $snap1 = new Snapshot();
		 * print("A");
		 * $snap2 = new Snapshot();
		 * print("a");
		 * $snap3 = new Snapshot();
		 * print("1");
		 * eq("1",$snap3->get());
		 * eq("a",$snap2->get());
		 * print("B");
		 * eq("AB",$snap1->get());
		 */
		$id = sizeof(Rhaco::getVariable("RHACO_CORE_SNAPSHOT_COUNT"));
		Rhaco::addVariable("RHACO_CORE_SNAPSHOT_COUNT",$id,$id);
		$this->id = $id;
		$this->start = true;
		$this->url = $url;
		$this->variables = ArrayUtil::arrays($variables);
		Rhaco::register_shutdown(array($this,"close"));
		Logger::deep_debug(Message::_("start snapshot({1})",$this->id));
		ob_start();
	}
	
	/**
	 * スナップショットの取得を終了する
	 */
	function close(){
		/*** unit("io.SnapshotTest"); */
		if($this->start){
			$this->start = false;
			$status = ob_get_status(true);

			if(isset($status[$this->id]) && $status[$this->id]["status"] != PHP_OUTPUT_HANDLER_END){
				Rhaco::clearVariable("RHACO_CORE_SNAPSHOT_COUNT",$this->id);
				Logger::deep_debug(Message::_("end snapshot({1})",$this->id));
	
				if($this->buffer === ""){
					$this->buffer = ob_get_contents();
					if(preg_match("/Fatal error.+on line.+/",$this->buffer,$match)){
						Logger::error(str_replace(array("<b>","</b>","<br />"),array("","",""),$match[0]));
					}
				}
				ob_end_clean();
			}
		}
	}
	/**
	 * スナップショットバッファを取得する
	 *
	 * @return string
	 */
	function get(){
		/*** unit("io.SnapshotTest"); */
		if($this->start){
			$this->buffer = ob_get_contents();
			$this->close();
		}
		return $this->buffer;
	}

	
	/**
	 * 保存されたスナップショットが存在するか
	 * 
	 * @static 
	 * @param string $url
	 * @param array $variables
	 * @param int $expiryTime
	 * @return boolean
	 */
	function exist($url="",$variables=array(),$expiryTime=0){
		/*** unit("io.SnapshotTest"); */
		$path = Snapshot::path($url,$variables);
		return (FileUtil::exist($path) && ($expiryTime == 0 || (FileUtil::time($path) + $expiryTime) > time()));
	}
	
	
	/**
	 * スナップショットを保存する
	 * 
	 * @static 
	 * @param string $url
	 * @param array $variables
	 * @return string
	 */
	function save($url="",$variables=array()){
		/*** unit("io.SnapshotTest"); */
		$path = (!empty($url) || !empty($variables)) ? Snapshot::path($url,$variables) : Snapshot::path($this->url,$this->variables);

		if(FileUtil::write($path,$this->get())){
			Logger::deep_debug(Message::_("made snapshot file [{1}]",$path));
		}else{
			Logger::warning(Message::_("fails in made snapshot file [{1}]",$path));
		}
		return $this->buffer;
	}
	/**
	 * 保存されたスナップショットを取得
	 * 
	 * @static 
	 * @param string $url
	 * @param array $variables
	 * @return string
	 */
	function load($url,$variables=array()){
		/*** unit("io.SnapshotTest"); */
		$path = Snapshot::path($url,$variables);
		if(FileUtil::exist($path)){
			Logger::deep_debug(Message::_("read snapshot file [{1}]",$path));
			return FileUtil::read($path);
		}
		Logger::warning(Message::_("fails in read snapshot file [{1}]",$path));
		return null;
	}
	
	/**
	 * 保存されたスナップショットをPHPとして出力する
	 * 
	 * @static 
	 * @param string $url
	 * @param array $variables
	 */
	function write($url="",$variables=array()){
		/*** unit("io.SnapshotTest"); */
		$path = Snapshot::path($url,$variables);
		if(FileUtil::exist($path)){
			include($path);
			Logger::deep_debug(Message::_("write snapshot file [{1}]",$path));
		}
	}
	
	/**
	 * 保存されたスナップショット削除する
	 *
	 * @static
	 * @param string $url
	 * @param array $variables
	 * @return boolean
	 */
	function clear($url="",$variables=array()){
		/*** unit("io.SnapshotTest"); */
		$path = Snapshot::path($url,$variables);
		
		if(FileUtil::exist($path)){
			if(FileUtil::rm($path)){
				Logger::deep_debug(Message::_("snapshot file deleted [{1}]",$path));
				return true;
			}
			Logger::warning(Message::_("fails in deletion [{1}]",$path));
			return false;
		}
		return true;
	}
	
	/**
	 * スナップショットのパスを取得
	 *
	 * @static 
	 * @param string $url
	 * @param array $variables
	 * @return string
	 */
	function path($url,$variables=array()){
		/***
		 * $path = Snapshot::path("http://rhaco.org/hoge/test.php",array("hoge"=>"abc"));
		 * eq(FileUtil::path(Rhaco::path("work/snapshot/"),"aHR0cDovL3JoYWNvLm9yZy9ob2dlL3Rlc3QucGhwaG9nZT1hYmM="),$path);
		 */
		$url = base64_encode($url.TemplateFormatter::httpBuildQuery($variables));
		return FileUtil::path(Rhaco::constant("SNAPSHOT_PATH",Rhaco::path("work/snapshot/")),$url);
	}
}
?>