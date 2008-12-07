<?php
Rhaco::import("tag.model.SimpleTag");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class RssCloud{
	var $domain = "";
	var $port = null;
	var $path = "";
	var $protocol = "";	

	function RssCloud($domain="",$path="",$port="",$protocol=""){
		$this->setDomain($domain);
		$this->setPort($port);
		$this->setPath($path);
		$this->setProtocol($protocol);		
	}
	function set($src){
		/***
		 * $src = '<cloud domain="http://rhaco.org" port="80" protocol="http" path="/" />';
		 * $xml = new RssCloud();
		 * assert($xml->set($src));
		 * 
		 * eq("http://rhaco.org",$xml->getDomain());
		 * eq(80,$xml->getPort());
		 * eq("http",$xml->getProtocol());
		 * eq("/",$xml->getPath());
		 */
		if(SimpleTag::setof($tag,$src,"cloud")){
			$this->setDomain($tag->getParameter("domain"));
			$this->setPort($tag->getParameter("port"));
			$this->setPath($tag->getParameter("path"));
			$this->setProtocol($tag->getParameter("protocol"));
			return true;
		}
		return false;
	}
	function get(){
		/***
		 * $src = '<cloud domain="http://rhaco.org" port="80" protocol="http" path="/" />';
		 * $xml = new RssCloud();
		 * assert($xml->set($src));
		 * 
		 * eq($src,$xml->get());
		 */
		$params = array();
		if($this->domain != "") $params["domain"] = $this->getDomain();
		if($this->port !== null) $params["port"] = $this->getPort();
		if($this->protocol != "") $params["protocol"] = $this->getProtocol();
		if($this->path != "") $params["path"] = $this->getPath();

		$outTag	= new SimpleTag("cloud","",$params);
		return $outTag->get();
	}
	
	function setDomain($value){
		$this->domain = $value;
	}
	function getDomain(){
		return $this->domain;
	}
	function setPort($value){
		if(!empty($value)) $this->port = intval($value);
	}
	function getPort(){
		return $this->port;
	}
	function setPath($value){
		$this->path = $value;
	}
	function getPath(){
		return $this->path;
	}
	function setProtocol($value){
		$this->protocol = $value;
	}
	function getProtocol(){
		return $this->protocol;
	}
}