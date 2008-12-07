<?php
Rhaco::import("network.http.model.RequestLoginCondition");
Rhaco::import("network.http.Browser");
/**
 * ブラウザでログインを行う認証条件
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RequestLoginConditionBrowser extends RequestLoginCondition{
	var $browser = null;
	var $success_page = null;
	var $login_page = null;
	var $login_var = "login";
	var $password_var = "password";
	var $login_form = 0;

	function RequestLoginConditionBrowser($login_url,$success_url,$login_form=0,$login_var="login",$password_var="password"){
		$this->__init__($login_url,$success_url,$login_form,$login_var,$password_var);
	}
	function __init__($login_url,$success_url,$login_form=0,$login_var="login",$password_var="password"){
		$this->browser = new Browser();
		$this->success_page = $success_url;
		$this->login_page = $login_url;
		$this->login_var = $login_var;
		$this->password_var = $password_var;
		$this->login_form = $login_form;
	}
	function condition($request){
		$this->browser->get($this->login_page);
		$this->browser->setVariable($this->login_var,$request->getVariable("login"));
		$this->browser->setVariable($this->password_var,$request->getVariable("password"));
		$this->browser->submit($this->login_form);

		if($this->browser->url(0) == $this->success_page){
			return $this->_success_condition();
		}
		return false;
	}
	function _success_condition(){
		return true;
	}
	
}
?>