<?php
/**
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2008 rhaco project. All rights reserved.
 */
class RequestLoginFilter extends TagParser{
	function after($src,&$parser){
		$list = array();
		foreach(get_class_methods($this) as $methodName){
			if(preg_match("/^_exec.+/",$methodName)) $list[] = $methodName;
		}
		sort($list);
		foreach($list as $methodName) $src = $this->$methodName($src);
		return $src;
	}
	function _exec2006_Login($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("login"))){
			$var = $tag->getParameter("var","var");
			$function = sprintf("%s if(RequestLogin::isLogin()): %s",$this->_pts(),$this->_pte());
			$function .= sprintf("%s%s = RequestLogin::getLoginSession();%s",$this->_pts(),$this->_getVariableString($tag->getParameter("var","var")),$this->_pte());
			$function .= $tag->getRawValue();
			$function .= sprintf("%s endif; %s",$this->_pts(),$this->_pte());

			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	function _exec2007_Guest($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("guest"))){
			$function = sprintf("%s if(!RequestLogin::isLogin()): %s",$this->_pts(),$this->_pte());
			$function .= $tag->getRawValue();
			$function .= sprintf("%s endif; %s",$this->_pts(),$this->_pte());

			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
}
?>