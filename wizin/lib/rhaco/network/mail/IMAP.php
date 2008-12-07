<?php
Rhaco::import("network.Socket");
Rhaco::import("lang.StringUtil");
Rhaco::import("util.Logger");
/**
 * Socket
 * 
 * サポート範囲外
 * 拡張機能,md5認証など
 * IDLE, CREATE,DELETE,RENAME,SUBSCRIBE,UNSUBSCRIBE,RLIST
 * CHECK, SEARCHなどのコマンド(UIDコマンドで対応)
 * 
 * @author SHIGETA Takeshiro
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class IMAP extends Socket{
	
	var $responseText;
	var $tag = 0;
	var $mbox = 'INBOX';
	var $options = array(
	'status'=>array("MESSAGES","RECENT","UIDNEXT","UIDVALIDITY","UNSEEN"),
	'flags'=>array('\Deleted','\Seen','\Answered','\Flagged','\Draft','\Recent')
	);
	
	function IMAP($host='',$port=110,$timeout=30){
		if(empty($host) || empty($port)) return;
		$this->open($host,$port,$timeout);
		$this->getResponse(1);
	}
	
	function login($id,$pass){
		if(empty($id) || empty($pass)) return false;
		if($this->isCommand('AUTHENTICATE'))
			$this->cmd('AUTHENTICATE LOGIN');
		if($this->cmd('LOGIN "'.$id.'" "'.$pass.'"')){
			if($this->cmd('NAMESPACE')){
				return true;
			}
		}
		return false;
	}
	
	function logout(){
		if($this->cmd('LOGOUT')){
				if($this->close()){
					Logger::deep_debug("connection closed");
					return true;
				}
		}
	}
	
	function getStatus($type,$name=""){
		if(in_array($type,$this->options['status'])){
			if(empty($name)) $name = $this->mbox;
			if($this->cmd("STATUS $name $type")){
				return $this->getText();//split(' ',$this->getText(),2);
			}
		}
		return false;
	}
	
	function getList($base="",$name="*"){
		
		if($this->cmd("LIST \"$base\" $name")){
			return $this->toList($this->getText());
		}
	}
	
	function getLsub($base="",$name="*"){
		if($this->cmd("LSUB \"$base\" $name")){
			return $this->toList($this->getText());
		}
			}
	
	function getUIDList($type="unseen"){
		if($this->cmd("UID SEARCH $type")){
			$data = explode(' ',$this->getText());//$this->toList($this->getText());
			return V::arrays($data,2);
		}
		return false;
	}
			
	function read($no){
		if($this->cmd("UID FETCH $no (BODY)")){
			return preg_replace('@^.*?[\r\n]+@','',$this->getText());
		}
		return false;
	}
	
	function body($no){
		if($this->cmd("UID FETCH $no (BODY[1])")){
			return preg_replace('@^.*?[\r\n]+@','',$this->getText());
		}
		return false;
	}
	
	function header($no){
		if($this->cmd("UID FETCH $no (RFC822.HEADER)")){
			return preg_replace('@^.*?[\r\n]+@','',$this->getText());
		}
	}
	
	function append($msg,$flag="",$name=""){
		$size=strlen($msg);
		if(empty($name)) $name = $this->mbox;
		if($this->isCommand("LITERAL+")){
			if($this->cmd("APPEND $name $flag$time{$size}\r\n$msg")){
				return $this->getText();
			}
		}
		return false;
	}
	
	function delete($no){
		if($no = $this->getRegion($no)){
			if($this->setFlag($no,'\Deleted')){
				return $this->cmd('EXPUNGE');
			}
		}
		return false;
	}
	
	function ping(){
		return $this->cmd('NOOP');
	}
	
	function getCommandList(){
		if($this->cmd("CAPABILITY")){
			return explode(' ',$this->getText());
		}
	}
	
	function isCommand($cmd){
		$commands = $this->getCommandList();
		return in_array(strtoupper($cmd),$commands);
	}
	
	function select($name="",$readonly=false){
		if(empty($name)) $name = $this->mbox;
		if($readonly){
			$cmd = "EXAMINE \"$name\"";
		}else{
			$cmd = "SELECT \"$name\"";
		}
		if($this->cmd($cmd)){
			$this->mbox = $name;
			return true;
		}
		return false;
	}
	
	function copy($no, $name=""){
		if($no = $this->getRange($no)){
			if(empty($name)) $name = $this->mbox;
			if($this->cmd("UID COPY $no $name")){
				return $this->getText();
			}
		}
		return false;
	}
	
	function getMaillist($status="unseen",$name=""){
		if($this->select($name)){
			return $this->getUIDList(strtoupper($status));
		}
	}
	
	function cmd($cmd){
		$tag = $this->getTag();
		$this->setResponseDelimiter($tag);
		$this->responseText = $response = trim($this->command($tag." ".$cmd."\r\n"));
		if(preg_match('@^'.$tag.'\s*ok@im',$response)){
			$this->responseText = trim(preg_replace('@[\r\n]+(.*?)$@','',$response));
			return true;
		}
		return false;
	}
	
	function getText(){
		return $this->responseText;
	}
	
	function setFlag($no, $cmd, $mode="set"){
		if(in_array($cmd,$this->options['flags'])){
			if($no = $this->getRange($no)){
				if($this->cmd("UID STORE $no FLAGS ($cmd)")){
					return $this->getText();
				}
			}
		}
		return false;
	}
	
	function toList($response){
		if(empty($response)) return false;
			$lines = explode("\n",StringUtil::toULD($response));
			foreach($lines as $line){
				$data = explode(' ',$line);
				$result[] = str_replace('"','',$data[4]);
			}
			return $result;
	}
	
	function getTag(){
		$this->tag++;
		return sprintf('%04s',$this->tag);
	}
	
	function getRange($no){
		if(is_object($no)){
			$no = V::oh($no);
		}
		if(is_array($no)){
			if(isset($no['from']) && isset($no['to'])){
				return $no['from'].':'.$no['to'];
			}else{
				return false;
			}
		}
		return $no;
	}
}
?>