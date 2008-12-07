<?php
Rhaco::import("io.Snapshot");
Rhaco::import("network.Socket");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotSupportedException");
Rhaco::import("lang.StringUtil");
Rhaco::import("util.Logger");
/**
 * POP3
 * 
 * @author SHIGETA Takeshiro
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class POP3 extends Socket{
	
	var $responseText;
	var $host;
	var $port;
	var $id;
	var $pass;
	
	function POP3($host='',$port=110,$timeout=30){
		if(empty($host) || empty($port)) return;
		$this->open($host,$port,$timeout);
		$this->host = $host;
		$this->port = $port;
		$this->getResponse(1);
	}
	
	function login($id="",$pass=""){
		if((empty($id) || empty($pass)) && (empty($this->id) || empty($this->pass))) return false;
		if($this->cmd('USER '.$id)){
				if($this->cmd('PASS '.$pass)){
						$this->id = $id;
						$this->pass = $pass;
						return true;	
				}
		}
		return false;
	}
	
	function loginapop($id,$pass){
		if(!preg_match('@<.+?>@',$this->getResponse(),$match)){
			ExceptionTrigger::raise(new NotSupportedException("APOP"));
			return false;
		}
		$timestamp = $match[0];
		$md5pass = md5($timestamp.$pass);
		if($resp = $this->cmd('APOP '.$md5pass)){
				return true;
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
	
	function getStatus(){
		if($this->cmd('STAT')){
			return split(' ',$this->getText(),2);
		}
	}
	
	function delete($no=0){
		return $this->cmd("DELE $no");
	}
	
	function getList($no=0){
		if($no > 0){
			if($this->cmd("LIST $no")){
				return $this->toList($this->getText());
			}
		}else{
			$this->setResponseDelimiter('^\.\s*$');
			if($this->cmd("LIST")){
				return $this->toList($this->getText());
			}
		}
	}
	
	function getUIDList($no=0){
		if($no > 0){
			if($this->cmd("UIDL $no")){
				return $this->toList($this->getText());
			}
		}else{
			$this->setResponseDelimiter('^\.\s*$');
			if($this->cmd("UIDL")){
				return $this->toList($this->getText());
			}
		}
	}
	
	function fetch($num,$offset=0){
		$status = $this->getStatus();
		if($offset > $status[0]) return false;
		$end = min($num+$offset,$status[0]);
		for($i=$offset;$i<$end;$i++){
			$result[] = $this->read($i);
		}
		return $result;
	}
			
	function read($no){
		$this->setResponseDelimiter('^\.\s*$');
		if($this->cmd("RETR $no")){
			return $this->getText();
		}
		return false;
	}

	function content($no){
		$head = $this->header($no);
		$whole = $this->read($no);
       	$body = substr($whole,strlen($head));
       	return $body;
	}
	
	function top($no,$line=0){
		$this->setResponseDelimiter('^\.\s*$');
		if($this->cmd("TOP $no $line")){
			return $this->getText();
		}
	}
	
	function header($no){
		return $this->top($no,0);
	}

	function news(){
		$name=$this->host.$this->id;
        $uids = $this->getUIDList();
        if(Snapshot::exist($name)){
			$store_uids = unserialize(Snapshot::read($name));
        }else{
        	$store_uids = array();
        }
        $new_mails = array_diff($uids,$store_uids);
        $store = new Snapshot($name);
        echo serialize($uids);
        $store->set($name);
		$store->close();
		return $new_mails;
	}
	
	function ping(){
		return $this->cmd('NOOP');
	}
	
	function reset(){
		return $this->cmd('RSET');
	}
	
	function cmd($cmd){
		$this->responseText = $response = trim($this->command($cmd."\n"));
		if(preg_match('@^\+OK@',$response)){
			$this->responseText = trim(preg_replace('@^\+OK@','',$response));
			return true;
		}elseif(preg_match('@^\-ERR@',$response)){
			if(strstr($response,'timeout')){
				Logger::deep_debug("timeout $cmd. response is $response");
				$this->login();
				$this->cmd($cmd);
			}
		}
		return false;
	}
	
	function getText(){
		return $this->responseText;
	}
	
	function toList($response){
		if(empty($response)) return false;
			$lines = explode("\n",StringUtil::toULD($response));
			foreach($lines as $line){
			list($key,$var) = explode(' ',$line);
				$result[$key] = $var;
			}
			array_pop($result);
			return $result;
	}
}
?>