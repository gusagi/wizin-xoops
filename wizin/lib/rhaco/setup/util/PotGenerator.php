<?php
Rhaco::import("tag.HtmlParser");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.FileUtil");
Rhaco::import("generic.Flow");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.RequireException");
Rhaco::import("exception.model.DuplicateException");
Rhaco::import("setup.util.SetupUtil");
Rhaco::import("tag.TemplateParser");
Rhaco::import("lang.StringUtil");
/**
 * 疑似po生成ライブラリ
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class PotGenerator extends Flow{
	var $memberfile;

	/**
	 * コンストラクタ
	 */
	function PotGenerator($path="",$outputpath=""){
		$this->__init__();

		if($this->isPost()){
			if($this->isVariable("create")){
				$this->_createPot($path,$this->getVariable("outputpot"));
			}else if($this->isVariable("generate")){
				$this->_generatePhp($outputpath);
			}else if($this->isVariable("marge")){
				$this->_marge($path);
			}
		}
		$this->setVariable("searchpath",Rhaco::path());
		$this->setVariable("outputpot",Rhaco::setuppath("po/message-xx.pot"));
		$this->setVariable("outputphp",Rhaco::resource("locale/messages"));
		$this->setVariable("outputdir",Rhaco::setuppath("po"));
		$this->setTemplate(SetupUtil::template("setup/intltool/po2php.html"));
	}
	function _escape($value){
		return str_replace(array("\r","\n"),array("\\r","\\n"),trim($value));
	}	
	function _createPot($path="",$outputpath=""){
		$messageList = $this->_massageList($this->getVariable("searchpath",Rhaco::path()));
		$outoutsrc = $this->_header();
				
		foreach($messageList as $message => $fileList){
			if(strpos($message,'{$') === false){
				foreach($fileList as $fileline) $outoutsrc .= sprintf("%s\n",$fileline);
				$outoutsrc .= "\n";
				$outoutsrc .= sprintf("msgid \"%s\"\n",$message);
				$outoutsrc .= sprintf("msgstr \"\"\n");
				$outoutsrc .= "\n";
			}
		}
		if(empty($outputpath)) $outputpath = Rhaco::setuppath("po/message-xx.pot");
		FileUtil::write($outputpath,$outoutsrc,"UTF-8");
	}
	function _massageList($path=""){
		$path = empty($path) ? Rhaco::path() : $path;
		$messageList = array();
		$pathstrlen = strlen($path);

		foreach(FileUtil::ls($path,true) as $file){
			if(!preg_match("/(\.svn)|(\.pot)|(\.po)|(\.log)$/",$file->fullname)){
				if(Rhaco::constant("TEMPLATE_CACHE_PATH") == "" || 
					!preg_match(sprintf("/^%s/",preg_quote(Rhaco::constant("TEMPLATE_CACHE_PATH"),"/")),$file->fullname)
				){
					$src = FileUtil::read($file->fullname);
					$lineList = explode("\n",$src);

					foreach($lineList as $lineNo => $line){
						while(preg_match("/_p\(([\"\'])(.+?)\\1,([\"\'])(.+?)\\3/",$line,$matche)){
							$this->_margeMassageList($messageList,$matche[2],$file->fullname,$pathstrlen,$lineNo);
							$this->_margeMassageList($messageList,$matche[4],$file->fullname,$pathstrlen,$lineNo);
							$line = str_replace($matche[0],"",$line);
						}
						while(preg_match("/_\(([\"\'])(.+?)\\1/",$line,$matche)){
							$this->_margeMassageList($messageList,$matche[2],$file->fullname,$pathstrlen,$lineNo);
							$line = str_replace($matche[0],"",$line);
						}
						while(preg_match("/_n\(([\"\'])(.+?)\\1/",$line,$matche)){
							$this->_margeMassageList($messageList,$matche[2],$file->fullname,$pathstrlen,$lineNo);
							$line = str_replace($matche[0],"",$line);
						}
					}
					$src = str_replace(array("\r\n","\r"),"\n",$src);
					$base = $src;
					$index = 0;
					$tag = new SimpleTag();
					while($tag->set($src,TemplateParser::withNamespace("plural"))){
						$single = $tag->getParameter("single");
						$plural = trim(str_replace(array("\r\n","\r","\n"),"",$tag->getValue()));
						$index = strpos($base,$tag->getPlain(),$index);
						$lineNo = substr_count(substr($src,0,$index),"\n");
						$this->_margeMassageList($messageList,$single,$file->fullname,$pathstrlen,$lineNo);
						$this->_margeMassageList($messageList,$plural,$file->fullname,$pathstrlen,$lineNo);
						$src = str_replace($tag->getPlain(),"",$src);
					}
					$index = 0;
					while($tag->set($src,TemplateParser::withNamespace("trans"))){
						$trans = trim(str_replace(array("\r\n","\r","\n"),"",$tag->getValue()));
						$index = strpos($base,$tag->getPlain(),$index);
						$lineNo = substr_count(substr($src,0,$index),"\n");
						$this->_margeMassageList($messageList,$trans,$file->fullname,$pathstrlen,$lineNo);
						$src = str_replace($tag->getPlain(),"",$src);
					}
				}
			}
		}
		if($path == Rhaco::path() && FileUtil::exist(Rhaco::setuppath("project.xml"))){
			$tag		= new SimpleTag();
			$src		= FileUtil::read(Rhaco::setuppath("project.xml"));
			
			if($tag->set($src,"project")){
				foreach($tag->getIn("database") as $databaseTag){
					foreach($databaseTag->getIn("description") as $valueTag){
						$messageList[$this->_escape($valueTag->getValue())][] = sprintf("#: setup/project.xml:database");
					}
				}
				foreach($tag->getIn("input") as $inputTag){
					foreach($inputTag->getIn("description") as $valueTag){
						$messageList[$this->_escape($valueTag->getValue())][] = sprintf("#: setup/project.xml:input");
					}
					foreach($inputTag->getIn("title") as $valueTag){
						$messageList[$this->_escape($valueTag->getValue())][] = sprintf("#: setup/project.xml:input");
					}
				}
				foreach($tag->getIn("select") as $inputTag){
					foreach($inputTag->getIn("description") as $valueTag){
						$messageList[$this->_escape($valueTag->getValue())][] = sprintf("#: setup/project.xml:input");
					}
					foreach($inputTag->getIn("title") as $valueTag){
						$messageList[$this->_escape($valueTag->getValue())][] = sprintf("#: setup/project.xml:input");
					}
					foreach($inputTag->getIn("data") as $valueTag){
						$messageList[$this->_escape($valueTag->getValue())][] = sprintf("#: setup/project.xml:input");
					}
				}
			}
		}
		return $messageList;
	}
	function _header(){
		$outoutsrc	= "";
		$outoutsrc	.= "# SOME DESCRIPTIVE TITLE.\n";
		$outoutsrc	.= "# Copyright (C) YEAR THE PACKAGE'S COPYRIGHT HOLDER\n";
		$outoutsrc	.= "# This file is distributed under the same license as the PACKAGE package.\n";
		$outoutsrc	.= "# FIRST AUTHOR <EMAIL@ADDRESS>, YEAR.\n";
		$outoutsrc	.= "#\n";
		$outoutsrc	.= "#, fuzzy\n";
		
		$outoutsrc	.= "msgid \"\"\n";
		$outoutsrc	.= "msgstr \"\"\n";
		$outoutsrc	.= "\"Project-Id-Version: PACKAGE VERSION\\n\"\n";
		$outoutsrc	.= "\"Report-Msgid-Bugs-To: \\n\"\n";
		$outoutsrc	.= sprintf("\"POT-Creation-Date: %s\\n\"\n",date("Y-m-d H:iO"));
		$outoutsrc	.= "\"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\\n\"\n";
		$outoutsrc	.= "\"Last-Translator: FULL NAME <EMAIL@ADDRESS>\\n\"\n";
		$outoutsrc	.= "\"Language-Team: LANGUAGE <team@exsample.com>\\n\"\n";
		$outoutsrc	.= "\n";
		
		return $outoutsrc;
	}
	function _marge($path=""){
		$file = $this->getFile("pofile");
		$dir = $this->getVariable("outputdir",Rhaco::setuppath("po"));

		if($file->isError()){
			ExceptionTrigger::raise(new RequireException(Message::_("Attached file")));
		}else{
			$filename = sprintf("%s.pot",substr($file->name,0,(strlen($file->extension) * -1)));		
			$pomessageList = $this->_pofileMessageList();
			$messageList = $this->_massageList($path);
			$outoutsrc = $this->_header();
					
			foreach($messageList as $message => $fileList){
				if(strpos($message,'{$') === false){
					foreach($fileList as $fileline){
						$outoutsrc .= sprintf("%s\n",$fileline);
					}
					$outoutsrc .= "\n";
					$outoutsrc .= sprintf("msgid \"%s\"\n",$message);
					$outoutsrc .= sprintf("msgstr \"%s\"\n",isset($pomessageList[$message]) ? $pomessageList[$message] : "");
					$outoutsrc .= "\n";
				}
			}
			FileUtil::write(FileUtil::path($dir,$filename),$outoutsrc,"UTF-8");
		}
	}
	function _pofileMessageList(){
		$file		= $this->getFile("pofile");
		$src		= FileUtil::read($file->tmp,StringUtil::UTF8());
		$lineList	= explode("\n",$src);
		$messageList = array();
		$msgId		= "";
		$isId		= false;

		foreach($lineList as $line){
			if(!preg_match("/^[\s]*#/",$line)){
				if(preg_match("/msgid[\s]+([\"\'])(.+)\\1/",$line,$match)){
					$msgId	= $match[2];
					$isId	= true;
				}else if(preg_match("/msgstr[\s]+([\"\'])(.+)\\1/",$line,$match)){
					$messageList[$msgId] = $match[2];
					$isId = false;
				}else if(preg_match("/([\"\'])(.+)\\1/",$line,$match)){
					if(!empty($msgId)){
						if($isId){
							$msgId .= $match[2];
						}else{
							if(!isset($messageList[$msgId])) $messageList[$msgId] = "";
							$messageList[$msgId] .= $match[2];
						}
					}
				}
			}
		}
		return $messageList;	
	}
	function _generatePhp($outputpath=""){
		$file		= $this->getFile("pofile");
		$dir		= $this->getVariable("outputphp",Rhaco::resource("locale/messages"));

		if($file->isError()){
			ExceptionTrigger::raise(new RequireException(Message::_("Attached file")));
		}else{
			$filename = sprintf("%s.php",substr($file->name,0,(strlen($file->extension) * -1)));
			$outputpath = empty($outputpath) ? FileUtil::path($dir,$filename) : $outputpath;

			$outoutsrc = "Message::add(array(\n";
			foreach($this->_pofileMessageList() as $key => $value){
				if(!empty($key)){
					$outoutsrc .= sprintf("\"%s\"=>\"%s\",\n",$key,str_replace("\"","\\\"",$value));
				}
			}
			$outoutsrc .= "));\n";
			FileUtil::write($outputpath,sprintf("<?php\n%s?>",$outoutsrc),StringUtil::UTF8());
		}
	}
	function _margeMassageList(&$messageList,$msg,$filename,$pathstrlen,$lineNo){
		if(!isset($messageList[$msg])) $messageList[$msg] = array();
		$messageList[$msg][] = sprintf("#: %s:%d",substr($filename,$pathstrlen),$lineNo+1);		
	}
}
?>