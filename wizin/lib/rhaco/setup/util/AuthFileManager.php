<?php
Rhaco::import("resources.Message");
Rhaco::import("generic.Flow");
Rhaco::import("io.FileUtil");
Rhaco::import("exception.model.RequireException");
Rhaco::import("exception.model.DuplicateException");
Rhaco::import("exception.model.IllegalStateException");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("setup.util.SetupUtil");
/**
 * 認証ファイルを生成するライブラリ
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class AuthFileManager extends Flow{
	var $memberfile;
	
	/**
	 * コンストラクタ
	 *
	 * @param string $memberfile
	 * @return AuthFileManager
	 */
	function AuthFileManager($memberfile=""){
		$this->__init__();
		$this->memberfile = (empty($memberfile)) ? Rhaco::resource("member_xml.php") : $memberfile;
	}

	/**
	 *　認証データの生成／削除／表示を行う
	 *
	 * @return tag.HtmlParser
	 */
	function template(){
		/*** #pass */
		if(!FileUtil::exist($this->memberfile)){
			$tag = new SimpleTag("auth");
			FileUtil::write($this->memberfile,$this->phpize($tag->get()));
		}else if(!SimpleTag::setof($xml,FileUtil::read($this->memberfile),"auth")){
			$src = trim(FileUtil::read($this->memberfile));
			$tag = new SimpleTag("auth");
			FileUtil::append($this->memberfile,"\n".$this->phpize($tag->get()));
		}
		if($this->isPost() && SimpleTag::setof($xml,FileUtil::read($this->memberfile),"auth")){
			$users = $xml->getIn("user");

			if($this->isVariable("delete") && !empty($users)){
				$xml->setValue("");
				foreach($users as $user){
					if($user->getParameter("login") !== $this->getVariable("_login")) $xml->addValue($user);
				}
			}else if($this->isVariable("addmember")){
				$login		= $this->getVariable("_login");
				$password	= $this->getVariable("_password");

				if(empty($login)) ExceptionTrigger::raise(new RequireException(Message::_("Login")));
				if(empty($password)) ExceptionTrigger::raise(new RequireException(Message::_("Password")));

				if(!ExceptionTrigger::isException()){
					foreach($users as $user){
						if($user->getParameter("login") === $login){
							ExceptionTrigger::raise(new DuplicateException(Message::_("Login")));
							break;
						}
					}
					if(!ExceptionTrigger::isException()){
						$xml->addValue(new SimpleTag("user","",array("login"=>$login,"password"=>md5($password))));
					}
				}
			}
			FileUtil::write($this->memberfile,str_replace($xml->getPlain(),$xml->get(),FileUtil::read($this->memberfile)));
		}		
		$users = array();
		if(SimpleTag::setof($xml,FileUtil::read($this->memberfile),"auth")) $users = $xml->getIn("user");
		$this->setVariable("userList",$users);
		$this->setTemplate(SetupUtil::template("setup/member.html"));
		return $this;
	}
	function phpize($src){
		/*** #pass */
		return "<?php\n/*\n".$src."\n*/\n?>";
	}
}
?>