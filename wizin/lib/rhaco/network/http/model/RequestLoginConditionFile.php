<?php
Rhaco::import("network.http.model.RequestLoginCondition");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.FileUtil");
Rhaco::import("network.http.RequestLogin");
/**
 * XMLファイルによる認証条件
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RequestLoginConditionFile extends RequestLoginCondition{
	var $filename = "";
	var $through = false;

	function RequestLoginConditionFile($filename=""){
		$this->__init__($filename);
	}
	function __init__($filename=""){
		$this->filename = empty($filename) ? Rhaco::resource("member_xml.php") : $filename;
	}
	function condition($request){
		if(!FileUtil::isFile($this->filename)) return $this->_through();
		$xmlsrc = FileUtil::read($this->filename);
		if(!SimpleTag::setof($xml,FileUtil::read($this->filename),"auth")) return $this->_through();
		$users = $xml->getIn("user");

		if(empty($users)) return $this->_through();
		if($request->isVariable("login") && $request->isVariable("password")){
			foreach($users as $user){
				if($user->getParameter("login") === $request->getVariable("login") &&
					$user->getParameter("password") === md5($request->getVariable("password"))
				){
					RequestLogin::setLoginSession($user);					
					return true;
				}
			}
		}
		return false;
	}
	function _through(){
		$this->through = true;
		return true;
	}
	function after($request=null){
		if($this->through){
			unset($_SESSION[RequestLogin::getSessionLoginIdName()]);
		}
	}
}
?>