<?php
Rhaco::import("network.http.model.RequestLoginCondition");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.FileUtil");
Rhaco::import("network.http.RequestLogin");
/**
 * #ignore
 * DBを利用してログインを行う認証条件
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008 rhaco project. All rights reserved.
 */
class RequestLoginConditionDb extends RequestLoginCondition{
	var $db;
	var $object;

	function RequestLoginConditionDb(&$dbUtil,$tableObject){
		$this->__init__($dbUtil,$tableObject);
	}
	function __init__(&$dbUtil,$tableObject){
		$this->db = $dbUtil;
		$this->object = Variable::copy($tableObject);
	}
	
	/**
	 * 指定のTableObjectのloginCondition(&$db,&$var,$request)を実行する
	 * クッキー情報が存在する場合、指定のTableObjectのloginConditionCookie(&$db,&$var,$cookiekey)を実行する
	 * 
	 * &$db database.DbUtilインスタンス
	 * &$var ユーザ情報を格納する変数
	 * $request network.http.Requestのインスタンス
	 * $cookiekey クッキーに格納されたログインキー
	 *
	 * @param unknown_type $request
	 * @return unknown
	 */
	function condition($request){
		if(RequestLogin::isLoginCookie() 
			&& method_exists($this->object,"loginConditionCookie")
			&& $this->object->loginConditionCookie($this->db,$object,RequestLogin::getLoginCookie())
		){
			$this->object = $object;
			RequestLogin::setLoginSession($this->object);
			return true;
		}
		if(method_exists($this->object,"loginCondition") 
					&& $this->object->loginCondition($this->db,$object,$request)
					&& $object !== null
		){
			$this->object = $object;
			RequestLogin::setLoginSession($this->object);
			return true;
		}
		return false;
	}
}
?>