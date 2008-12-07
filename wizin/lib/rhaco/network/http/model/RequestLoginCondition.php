<?php
Rhaco::import("network.http.RequestLogin");
Rhaco::import("tag.HtmlParser");
Rhaco::import("lang.Variable");
Rhaco::import("io.FileUtil");
/**
 * #ignore
 * 認証条件モデルの基底クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RequestLoginCondition{	
	/**
	 * 条件式
	 *
	 * @param network.http.Request $request
	 * @return unknown
	 */
	function condition($request){
		return false;
	}

	/**
	 * 成功した場合の処理
	 *
	 * @param network.http.Request $request
	 */
	function after($request=null){
	}
	
	/**
	 * 失敗した場合の処理
	 *
	 * @param network.http.Request $request
	 */
	function invalid($request){
		if(!Variable::istype("Request",$request)){
			$request = new RequestLogin();
		}
		$this->_invalidForword($request);
	}
	function _invalidForword($request){
		$htmlParser = new HtmlParser();
		$login = $request->getVariable("login");

		$request->clearVariable("password","login","args");
		$htmlParser->setVariable("requestvar",$request->getVariable());
		$template = Rhaco::rhacoresource("templates/network/login.html");
		
		if(FileUtil::exist(Rhaco::templatepath("network/login.html"))) $template = Rhaco::templatepath("network/login.html");
		$htmlParser->write($template);
		Rhaco::end();
	}
}
?>