<?php
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.GenericException");
Rhaco::import("exception.model.NotConnectionException");
Rhaco::import("util.Logger");
Rhaco::import("lang.Validate");
/**
 * Socket
 * 
 * @author SHIGETA Takeshiro
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class Socket {
	
	var $fp;
	var $delimiter='';
	
	function Socket(){
		
	}
	
	function open($host,$port,$timeout=30){
		$this->fp = @fsockopen($host,$port,$errno,$errstr,$timeout);
		if(!$this->fp){
			ExceptionTrigger::raise(new NotConnectionException("socket"));
			return false;
		}
		Logger::deep_debug("Socket is connected");
		return true;
	}
	
	function close(){
		if(fclose($this->fp)){
			Logger::deep_debug("Socket is disconnected");
			return true;
		}else{
			ExceptionTrigger::raise(new GenericException("socket is not disconnected"));
			return false;
		}
	}
	
	function command($cmd){
		if(!fwrite($this->fp,$cmd)){
			ExceptionTrigger::raise(new NotConnectionException("socket"));
			return false;
		}
		Logger::deep_debug("Command \"$cmd\" is sent.");
		return $this->getResponse();
	}
	
	function setResponseDelimiter($str=''){
		$this->delimiter = $str;
	}
	
	function getResponse($num=null){
		if(!is_null($num) && $num > 0){
			$response = '';
			for($i=0;$i<$num;$i++){
				$response .=  fgets($this->fp, 1024);
				$meta = stream_get_meta_data($this->fp);
				if($meta['eof']){
					Logger::deep_debug("Response \"$response\" is given.");
					return $response;
				}
			}
			Logger::deep_debug("Response \"$response\" is given.");
			return $response;
		}else{
			if($response =  fgets($this->fp, 1024)){
				$meta = stream_get_meta_data($this->fp);
				$line = $response;
				if(!empty($this->delimiter)){
					while (!feof($this->fp) && !preg_match('@'.$this->delimiter.'@im',trim($line))) {
						$line = fgets($this->fp, 1024);
						$response .= $line;
					}
				}else{
					while(!$meta['eof']){
						$line = fgets($this->fp, 1024);
						$response .= $line;
						$meta = stream_get_meta_data($this->fp);
					}
				}
				$this->setResponseDelimiter();
				Logger::deep_debug("Response \"$response\" is given.");
				return $response;
			}else{
				ExceptionTrigger::raise(new GenericException("Response not found"));
				return false;
			}
		}
	}
}
?>