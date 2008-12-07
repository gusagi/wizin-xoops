<?php
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.PermissionException");
Rhaco::import("resources.Message");
Rhaco::import("io.FileUtil");
Rhaco::import("lang.StringUtil");
/**
 * ファイルモデル
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class File{
	var $directory = "";
	var $fullname = "";
	var $name = "";
	var $originalName = "";
	var $extension = "";
	var $size = 0;
	var $update = 0;
	var $value = null;

	var $tmp;
	var $error;

	function File($fullname=null,$value=null){
		$this->__init__($fullname,$value);
	}
	function __init__($fullname=null,$value=null){
		$this->fullname	= str_replace("\\","/",$fullname);
		$this->value	= $value;		
		$this->_parseFullname();		
	}
	function _parseFullname(){
		$info				= $this->_pathinfo($this->fullname);
		$this->directory	= (isset($info["dirname"])) ? $info["dirname"] : "";
		$this->name			= (isset($info["basename"])) ? $info["basename"] : "";
		$this->extension	= ".".((isset($info["extension"])) ? $info["extension"] : "");
		$this->originalName	= @basename($this->name,$this->extension);

		if(@is_file($this->fullname)){
			$this->update	= @filemtime($this->fullname);
			$this->size		= sprintf("%u",@filesize($this->fullname));
		}else{
			$this->size = strlen($this->value);
		}
	}
	function _pathinfo($fullname){
		$fullname = str_replace("\\","/",StringUtil::encode($fullname));
		$dirname = "";
		$basename = $filename = $fullname;
		$extension = "";

		if(preg_match("/^(.+[\/]){0,1}([^\/]+)$/",$fullname,$match)){
			$dirname = empty($match[1]) ? "." : $match[1];
			$basename = $match[2];
		}
		$p = strrpos($basename,".");
		if($p !== false){
			$extension = substr($basename,$p+1);
			$filename = substr($basename,0,$p);
		}
		return array("fullname"=>$fullname,"dirname"=>$dirname,"basename"=>$basename,"filename"=>$filename,"extension"=>$extension);
	}
	/**
	 * 一時ファイルから移動する
	 * HTMLでのファイル添付の場合に使用
	 *
	 * @param string $filename
	 * @return unknown
	 */
	function generate($filename){
		if(FileUtil::cp($this->tmp,$filename)){
			if(@unlink($this->tmp)){
				$this->fullname = $filename;
				$this->_parseFullname();
				return true;
			}
		}
		ExceptionTrigger::raise(new PermissionException($filename));		
		return false;
	}
	
	/**
	 * ディレクトリパス
	 *
	 * @return unknown
	 */
	function getDirectory(){
		return $this->directory;
	}
	
	/**
	 * フルパス
	 *
	 * @return unknown
	 */
	function getFullname(){
		return $this->fullname;
	}
	
	/**
	 * ファイル名
	 *
	 * @return unknown
	 */
	function getName(){
		return $this->name;
	}
	
	/**
	 * ファイル名（拡張子無し)
	 *
	 * @return unknown
	 */
	function getOriginalName(){
		return $this->originalName;
	}
	
	/**
	 * ファイル拡張子
	 *
	 * @return unknown
	 */
	function getExtension(){
		return $this->extension;
	}
	function getSize(){
		return (empty($this->size)) ? 0 : $this->size;
	}
	function getUpdate($dateformat=""){
		if(!empty($dateformat)) return date($dateformat,$this->update);
		return $this->update;
	}
	function getTmp(){
		return $this->tmp;
	}
	function isError(){
		return (intval($this->error) > 0);
	}
	function setValue($value){
		$this->value = $value;
		$this->size = sizeof($value);
	}
	function getValue(){
		return $this->value;
	}
	function getPath(){
		return $this->getFullname();
	}
	/**
	 * 標準出力に出力する
	 *
	 */
	function output(){
		if(empty($this->value) && @is_file($this->fullname)){
			readfile($this->fullname);
		}else{
			print($this->getValue());
		}
	}
	
	/**
	 * 取得する
	 *
	 * @param string $filename
	 * @return string
	 */
	function read(){
		if($this->value !== null) return $this->value;
		return FileUtil::read($this->getFullname());
	}
}
?>