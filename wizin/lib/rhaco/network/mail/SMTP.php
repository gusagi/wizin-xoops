<?php
Rhaco::import("lang.Variable");
Rhaco::import("network.Socket");
Rhaco::import("network.mail.POP3");
Rhaco::import("util.Logger");
/**
 * SMTP
 * 
 * @author SHIGETA Takeshiro
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class SMTP extends Socket{
	
	var $responseText;
	var $host;
	var $port;
	var $id;
	var $pass;
	var $authtype=array();
	
	function SMTP($host='',$port=25,$timeout=30){
		if(empty($host) || empty($port)) return;
		$this->open($host,$port,$timeout);
		$this->host = $host;
		$this->port = $port;
		$this->getResponse(1);
	}
	
	function login($id="",$pass=""){
		if($this->cmd('EHLO '.$this->host)){
			if(preg_match('@starttls@im',$this->getText())){
				if(!$this->cmd('STARTTLS')){
					return false;
				}
			}
			if(preg_match('@auth\s(.*?)$@im',$this->getText(),$match)){
				$this->authtype = array_map('trim',explode(' ',$match[1]));
				if(empty($id) || empty($pass)) return false;
				$this->auth($id,$pass);
			}
			return true;	
		}
		return false;
	}
	
	function auth($id,$pass,$type="plain"){
		if(in_array(strtoupper($type),$this->authtype)){
			switch (strtoupper($type)){
				case 'PLAIN':
					$key = base64_encode($id."\0".$id."\0".$pass."\0");
					if($this->cmd("AUTH PLAIN $key")){
						return true;
					}
					break;
				case 'LOGIN':
					if($this->cmd("AUTH LOGIN $id $pass")){
						return true;
					}
					break;
				case 'CRAM-MD5':
					if($this->cmd("AUTH CRAM-MD5")){
						$tmp = explode(' ',$this->getText());
						$skey = $tmp[1];
						$key=base64_encode($id." ".md5(base64_decode($skey).$pass));
						if($this->cmd($key)){
								return true;
						}
					}
					break;
				case 'DIGEST-MD5'://incomplete
					if($this->cmd("AUTH DIGEST-MD5")){
						if($this->cmd($key)){
							if($this->getCode()=='503'){
								return true;
							}else{
								$challenge = base64_decode($this->getText());
								$md5digest = preg_replace('/=+$/','',base64_encode(pack('H*',md5($data)))); 
								return true;
							}
						}
					}
					break;
				default:
					break;
			}
			return false;
		}
	}
	
	function pop($id,$pass,$phost,$pport,$timeout=30){
		$p = new POP3($phost,$pport,$timeout);
		if($p->login($id,$pass)){
			$p->logout();
			if($this->login($id,$pass)){
				Logger::deep_debug("smtp login successful");
				return true;
			}
		}
		return false;
	}
	
	function logout(){
		if($this->cmd('QUIT')){
				if($this->close()){
					Logger::deep_debug("connection closed");
					return true;
				}
		}
	}
	
	function mail($mail){
		if(Variable::istype("Mail",$mail)){
			if($this->cmd(sprintf("MAIL FROM: <%s>",$mail->from))){
				$fail = false;
				foreach (array_keys($mail->to) as $to){
					$fail = $this->cmd(sprintf("RCPT TO: <%s>",$to)) ? $fail : true;
				}
				if(!$fail){
					if($this->cmd("DATA")){
						if($this->cmd($mail->manuscript().".")){//from to subject x-mailer
							return $this->getText();
						}
					}
				}
			}
		}
	}
	
	function reset(){
		return $this->cmd('RSET');
	}
	
	function ping(){
		return $this->cmd('NOOP');
	}
	
	function cmd($cmd){
		$this->responseText = $response = trim($this->command($cmd."\r\n"));
		return $this->isOK();
	}
	
	function isOk(){
		$code = $this->getCode();
		switch ($code[0]){
			case 4:
			case 5:
				Logger::warning($this->getText());
				return false;
			case 2:
			case 3:
				return true;
			default:
				return false;
		}
	}
	
	function getCode(){
		if(preg_match('@^([0-9]+?)\s@m',$this->getText(),$match)){
			return $match[1];
		}
		return false;
	}
	
	function getText(){
		return $this->responseText;
	}
}
?>