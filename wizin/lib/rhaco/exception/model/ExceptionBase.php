<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
/**
 * 疑似Exceptionのベース
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ExceptionBase{
	var $message		= "";
	var $target			= "";
	var $properties		= array();
	var $name			= "";

	function ExceptionBase($properties=array()){
		$this->properties = ArrayUtil::arrays($properties);
	}
	
	/**
	 * メッセージを取得する
	 *
	 * @return string
	 */
	function getMessage(){
		$propertiesize	= sizeof($this->properties);
		$message		= $this->message;

		if(preg_match_all("/\{([\d]+)\}/",$message,$match)){
			$paramsize = sizeof($match);

			foreach($match[1] as $value){
				if($value <= $propertiesize){
					$paramList[] = $this->properties[$value-1];
				}
			}
			if($paramsize > $propertiesize){
				for($i=0;$i<$paramsize-$propertiesize;$i++){
					$paramList[] = "";
				}
			}
			$message	= preg_replace("/\{[\d]+\}/","%s",$message);
			$message	= vsprintf($message,ArrayUtil::arrays($paramList,0,substr_count($message,"%s"),true));
		}
		return $message;
	}
	
	/**
	 * 詳細メッセージを取得する
	 *
	 * @return string
	 */
	function getDetail(){
		return sprintf("%s (%s:%d [%s])",$this->getMessage(),$this->target["file"],$this->target["line"],date("Y/m/d H:i:s"));
	}
}
?>