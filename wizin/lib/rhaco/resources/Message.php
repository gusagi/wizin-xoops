<?php
Rhaco::import("lang.ArrayUtil");
/**
 * #ignore
 * 国際化文字列処理
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Message{
	/*
	 * 国際化文字列を定義する
	 * @return string
	 */
	function _n($message){
		return Rhaco::getVariable("RHACO_CORE_MESSAGES",$message,$message);
	}
	/**
	 * 国際化対象文字列を取得する
	 *
	 * @return string
	 */
	function _(){
		$argList = func_get_args();
		$argsize = sizeof($argList);

		if($argsize > 0){
			$message = $argList[0];
			$paramList = array();
			$propertiesize = $argsize - 1;

			if(!empty($message)){
				$message = Rhaco::getVariable("RHACO_CORE_MESSAGES",$message,$message);
				if(preg_match_all("/\{([\d]+)\}/",$message,$match)){
					$paramsize = sizeof($match);
	
					foreach($match[1] as $value){
						if($value <= $propertiesize){
							$paramList[] = $argList[$value];
						}
					}
					if($paramsize > $propertiesize){
						for($i=0;$i<$paramsize-$propertiesize;$i++){
							$paramList[] = "";
						}
					}
					$message = preg_replace("/\{[\d]+\}/","%s",$message);
					$message = vsprintf($message,$paramList);
				}
			}
			return $message;
		}
		return "";
	}
	function add($hash){
		$messages = Rhaco::getVariable("RHACO_CORE_MESSAGES");
		if(!empty($messages)) $hash = array_merge($messages,$hash);
		Rhaco::setVariable("RHACO_CORE_MESSAGES",$hash);
	}
	/**
	 * 国際化文字列を設定する
	 */
	function loadRhacoMessages($lang=""){
		if(empty($lang) && !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
			list($lang)	= explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			list($lang)	= explode("-",$lang);	
		}
		$filename = Rhaco::rhacopath()."resources/locale/messages/message-".$lang.".php";
		if(is_file($filename)) require($filename);
	}
	function loadMessages($lang=""){
		if(empty($lang) && !empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
			list($lang)	= explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			list($lang)	= explode("-",$lang);
		}
		$filename = Rhaco::resource(sprintf("locale/messages/message-%s.php",$lang));
		if(is_file($filename)) require($filename);
		Rhaco::setVariable("RHACO_CORE_MESSAGE_LANG",$lang);
	}
	function includeMessages($directory,$lang=""){
		if(empty($lang)){
			if(Rhaco::isVariable("RHACO_CORE_MESSAGE_LANG")){
				$lang = Rhaco::getVariable("RHACO_CORE_MESSAGE_LANG");
			}else if(!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
				list($lang)	= explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
				list($lang)	= explode("-",$lang);
			}
		}
		$directory = str_replace("\\","/",$directory);
		$filename = ((substr($directory,-1) == "/") ? $directory : $directory."/").sprintf("locale/messages/message-%s.php",$lang);
		if(is_file($filename)) require($filename);
	}
	
	/**
	 * Langを変更する
	 */
	function setLang($lang){
		Rhaco::clearVariable("RHACO_CORE_MESSAGES");
		Message::loadRhacoMessages($lang);
		Message::loadMessages($lang);
		Rhaco::setVariable("RHACO_CORE_MESSAGE_LANG",$lang);
	}
	/**
	 * Langを取得する
	 * @return string
	 */
	function getLang(){
		return Rhaco::getVariable("RHACO_CORE_MESSAGE_LANG");;
	}
	
	/**
	 * 複数形判断で取得する
	 * @return string
	 */
	function _p(){
		$args = func_get_args();		
		if(sizeof($args) >= 2){
			$var = array();
			for($i=0;$i<3;$i++)	$var[] = array_shift($args);
			$args = array_merge(ArrayUtil::arrays((isset($var[2]) ? $var[2] : 0)),ArrayUtil::arrays($args));
			if(intval($args[0]) > 1) return call_user_func_array(array("Message","_"),array_merge(ArrayUtil::arrays($var[1]),$args));
			return call_user_func_array(array("Message","_"),array_merge(ArrayUtil::arrays($var[0]),$args));
		}
		return "";
	}
	function get($words,$word){
		if(isset($words[$word][Message::getLang()])) return $words[$word][Message::getLang()];
		return $word;
	}
}
?>
<?php
Message::loadMessages();
?>