<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.TagParser");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("network.Url");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("util.Logger");
Rhaco::import("tag.model.TemplateFormatter");
/**
 * テンプレートをフォーマットする
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class TemplateParser extends TagParser{
	var $replaceList = array();
	var $blockList = array();
	var $blocRead = array();
	var $blocParts = array();

	function TemplateParser($template=null,$path=null,$url=null){
		parent::TagParser($template,$path,$url);
	}
	
	/**
	 * 文字列置換用の値をセットする
	 *
	 * @param string/array $arrayOrSource
	 * @param string $dest
	 */
	function setReplace($arrayOrSource,$dest=""){
		if(!is_array($arrayOrSource)) $arrayOrSource = array($arrayOrSource=>$dest);
		foreach($arrayOrSource as $key => $value) $this->replaceList[$key]	= $value;
	}
	
	/**
	 * ブロックテンプレートをセットする
	 *
	 */
	function setBlock(){
		foreach(func_get_args() as $templateFileName){
			if(is_array($templateFileName)){
				foreach($templateFileName as $value) $this->blockList[] = $value;
			}else if(is_string($templateFileName)){
				$this->blockList[] = $templateFileName;
			}
		}
	}
	function _syntaxCheck($src){
		if(preg_match_all("/(Notice|Fatal).+/",$src,$match)){
			foreach($match[0] as $key => $m) Logger::error($m);
		}
		if(preg_match_all("/({\\$|".preg_quote(TemplateParser::withNamespace("")).").+/m",$src,$match)){
			foreach($match[0] as $key => $m) Logger::warning($m);
		}
		return $src;
	}	
	function _cs1001_Comment($src){
		/*** unit("tag.TemplateParserTest"); */
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("comment"))){
			$src = str_replace($tag->getPlain(),"",$src);
		}
		return $src;
	}
	function _cs1002_Template($src){
		/*** unit("tag.TemplateParserTest"); */
		$value = "";
		$bool = false;

		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("template"))){
			$value	.= $tag->getRawValue();
			$bool	= true;
			$src	= str_replace($tag->getPlain(),"",$src);
		}
		return ($bool) ? $value : $src;
	}
	function _cs1003_Include($src,$filename=""){
		/*** unit("tag.TemplateParserTest"); */
		$base = (empty($filename)) ? Url::parseAbsolute($this->path,$this->filename) : $filename;
		$bool = false;

		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("include"))){
			$path = Url::parseAbsolute(dirname($base),$tag->getParameter("href"));
			$includeTemplateSrc = $this->_getTemplateSource($path);
			$read = "";

			if(empty($includeTemplateSrc)){
				Logger::warning(Message::_("fails in read include template file [{1}]",$path));
				$src = str_replace($tag->getPlain(),"",$src);
			}else{
				Logger::deep_debug(Message::_("read include template file [{1}]",$path));
				$read = $this->_callFilter("init",$includeTemplateSrc);
				$src = str_replace($tag->getPlain(),$this->_cs1003_Include($read,$path),$src);
			}
			$bool = true;
		}
		if($bool) $src = $this->_callFilter("before",$src);
		return $src;
	}
	function _cs1003_Extends($src){
		/*** unit("tag.TemplateParserTest"); */
		$blockList	= array($this->filename);
		$path		= Url::parseAbsolute($this->path,$this->filename);
		$bool		= false;

		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("extends"))){
			$path			= Url::parseAbsolute(dirname($path),$tag->getParameter("href"));
			$blockList[]	= $path;
			$src			= $this->_getTemplateSource($path);
			$bool			= true;
		}
		if($bool){
			$this->setTemplate($path);			
			array_pop($blockList);
			$this->blockList = array_reverse(ArrayUtil::arrays($blockList));
		}
		return $src;
	}
	function _cs1005_Block($src){
		/*** unit("tag.TemplateParserTest"); */
		$tmpsrc = $src;
		$this->blockList = ArrayUtil::arrays($this->blockList);
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("setblock"))){
			$this->setBlock(Url::parseAbsolute(dirname(Url::parseAbsolute($this->path,$this->filename)),$tag->getParameter("href")));
			$src = str_replace($tag->getPlain(),"",$src);
		}
		foreach($this->blockList as $key => $blockTemplateFile){
			$blockTemplateFile	= Url::parseAbsolute($this->path,$blockTemplateFile);
			$tmpurl = Url::parseAbsolute($this->url,Url::parseRelative($this->path,$blockTemplateFile));

			if(!isset($this->blocRead[$blockTemplateFile])){
				$blockTemplateSrc = $this->_getTemplateSource($blockTemplateFile);
				$this->blocRead[$blockTemplateFile] = true;

				if(empty($blockTemplateSrc)){
					Logger::warning(Message::_("fails in read block template file [{1}]",$blockTemplateFile));
				}else{
					$incvalue = $this->_cs1003_Include($blockTemplateSrc,$blockTemplateFile);
					$newtag = new SimpleTag("template",$incvalue);
	
					foreach($newtag->getIn(TemplateParser::withNamespace("block")) as $blocktag){
						$this->blocParts[strtolower($blocktag->getParameter("name","name"))] = Url::parse($blocktag->getRawValue(),$tmpurl);
					}
					Logger::deep_debug(Message::_("read block template file [{1}]",$blockTemplateFile));
				}
			}
		}
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("block"))){
			$id		= strtolower($tag->getParameter("name","name"));
			$value	= $tag->getRawValue();
			if(array_key_exists($id,$this->blocParts)) $value = $this->blocParts[$id];
			$src = str_replace($tag->getPlain(),$this->_call($value,"_cs"),$src);
		}
		if(sizeof($this->blockList) > 0) $src = $this->_callFilter("before",$src);
		return $src;
	}
	function _cs1006_Replace($src){
		/*** unit("tag.TemplateParserTest"); */
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("replace"))){
			if($tag->getParameter("src") != "") $this->replaceList[$tag->getParameter("src")] = $tag->getParameter("dest");
			$src = str_replace($tag->getPlain(),"",$src);
		}
		krsort($this->replaceList);
		foreach($this->replaceList as $source => $dest){
			$src = str_replace($source,$dest,$src);
		}
		return $src;
	}
	/**
	 * 変数タグから変数へ変換
	 *
	 * @param unknown_type $src
	 * @return unknown
	 */
	function _exec1006_ReplaceContent($src){
		if(preg_match_all("/<([\w:_-]+)[\s][^>]*?content[s]{0,1}=([\"\'])[^\\2]*?\\2[^>]*?>/i",$src,$tagList)){
			foreach($tagList[0] as $id => $plain){
				$tag = new SimpleTag();
				$tag->set(substr($src,strpos($src,$plain)),$tagList[1][$id]);

				if($tag->isParameter(TemplateParser::withNamespace("content"))){
					$plain	= $tag->getPlain();
					$var	= $tag->getParameter(TemplateParser::withNamespace("content"));
					$tag->removeParameter(TemplateParser::withNamespace("content"));
					$tag->setValue($var);
					$src = str_replace($plain,$tag->get(),$src);
				}
			}
		}
		return $src;
	}
	/**
	 * メッセージタグからメッセージ記法へ変換
	 *
	 * @param unknown_type $src
	 * @return unknown
	 */
	function _exec1007_Message($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("plural"))){
			$param	= $tag->getParameter("param","param");
			$single = $tag->getParameter("single");
			$plural = trim(str_replace(array("\r\n","\r","\n"),"",$tag->getValue()));
			if(!is_int($param) && $param[0] != "{" && !preg_match("/^[\d]+$/",$param)) $param = "{\$".$param."}";
			$src = str_replace($tag->getPlain(),"_p(\"".$single."\",\"".$plural."\",".$param.")",$src);
		}
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("trans"))){
			$trans = trim(str_replace(array("\r\n","\r","\n"),"",$tag->getValue()));
			$src = str_replace($tag->getPlain(),"_(\"".$trans."\")",$src);
		}
		return $src;
	}
	function _exec1008_invalid($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("invalid"))){
			$name = $this->_parsePlainVariable($tag->getParameter("name","exceptions"));
			if(substr($name,0,1) != "$") $name = "\"".$name."\"";
			$var = $this->_parsePlainVariable($tag->getParameter("var","errors"));
			$type = $this->_parsePlainVariable($tag->getParameter("type","ul"));
			$value = $tag->getRawValue();

			$function = sprintf("%sif(ExceptionTrigger::invalid(%s)):%s",$this->_pts(),$name,$this->_pte());
			$function .= sprintf("%s\$_INVALID_ = array();%s",$this->_pts(),$this->_pte());
			$function .= sprintf("%sforeach(ExceptionTrigger::get(%s) as \$_INVALID_EXCEPTION):%s",$this->_pts(),$name,$this->_pte());
			$function .= sprintf("%s\$_INVALID_[] = \$_INVALID_EXCEPTION->getMessage();%s",$this->_pts(),$this->_pte());
			$function .= sprintf("%sendforeach;%s",$this->_pts(),$this->_pte());
			$function .= sprintf("%s%s = \$_INVALID_; %s",$this->_pts(),$this->_getVariableString($var),$this->_pte());
			if(!empty($value)){
				$function .= $value;
			}else{
				switch(strtolower($type)){
					case "ul":
						$function .= sprintf("<ul class=\"exceptions\">\n").
										sprintf("<%s param=\"%s\" var=\"msg\">\n",TemplateParser::withNamespace("loop"),$this->_getVariableString($var)).
										"<li>{\$msg}</li>\n".
										sprintf("</%s>\n",TemplateParser::withNamespace("loop")).
										"</ul>\n";
						break;
					case "plain":
						$function .= sprintf("<%s param=\"%s\" var=\"msg\">\n",TemplateParser::withNamespace("loop"),$this->_getVariableString($var)).
										"{\$msg}\n".
										sprintf("</%s>\n",TemplateParser::withNamespace("loop"));
						break;
				}
			}
			$function .= sprintf("%sendif;%s",$this->_pts(),$this->_pte());
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	function _exec2001_include($src){
		return $this->_cs1003_Include($src);
	}
	function _exec2002_Loop($src){
		/*** unit("tag.TemplateParserTest"); */
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("loop"))){
			$class	= $this->_parsePlainVariable($tag->getParameter("param","param"));
			$id		= $tag->getParameter("var","var");
			$classnm	= $this->_variableQuote($class);
			$counter	= $this->_getVariableString($tag->getParameter("counter","counter"));
			$first		= $this->_getVariableString($tag->getParameter("first","first"));
			$last		= $this->_getVariableString($tag->getParameter("last","last"));					
			$key		= $tag->getParameter("key","key");

			$count	= $this->_getVariableString("counter_".$classnm);
			$offset	= $tag->getParameter("offset","1");
			$limit	= $tag->getParameter("limit","0");

			$offsetName	= sprintf("\$_OFFSET_%s",$classnm);
			$limitName	= sprintf("\$_LIMIT_%s",$classnm);
			$varName	= sprintf("\$_".uniqid("RHACO_LOOP"));

			$function	= "";
			$function	.= sprintf("%s%s = 1;%s",$this->_pts(),$count,$this->_pte());
			$function	.= sprintf("%s %s = %d;%s",$this->_pts(),$offsetName,$offset,$this->_pte());
			$function	.= sprintf("%s %s = %d;%s",$this->_pts(),$limitName,(($limit>0)?($offset + $limit):0),$this->_pte());
			$function	.= sprintf("%s%s=%s%s",$this->_pts(),$varName,$this->_getVariableString($class),$this->_pte());
			$function	.= sprintf("%sif(is_array(%s)):%s",$this->_pts(),$varName,$this->_pte());
			$function	.= sprintf("%sforeach(%s as %s => %s):%s",$this->_pts(),$varName,$this->_getVariableString($key),$this->_getVariableString($id),$this->_pte());
			$function	.= sprintf("%sif(%s <= %s):%s",$this->_pts(),$offsetName,$count,$this->_pte());
			$function	.= sprintf("%s%s = %s;%s",$this->_pts(),$counter,$count,$this->_pte());
			$function	.= sprintf("%s%s = (1 == %s);%s",$this->_pts(),$first,$count,$this->_pte());
			$function	.= sprintf("%s%s = (sizeof(%s) == %s);%s",$this->_pts(),$last,$varName,$count,$this->_pte());
			$function	.= sprintf("%s",$tag->getRawValue());
			$function	.= sprintf("%sendif;%s",$this->_pts(),$this->_pte());
			$function	.= sprintf("%s%s++;%s",$this->_pts(),$count,$this->_pte());
			$function	.= sprintf("%sif(%s > 0 && %s >= %s){break;}%s",$this->_pts(),$limitName,$count,$limitName,$this->_pte());
			$function	.= sprintf("%sendforeach;%s",$this->_pts(),$this->_pte());
			$function	.= sprintf("%sendif;%s",$this->_pts(),$this->_pte());
			$function	.= sprintf("%sunset(%s);%s",$this->_pts(),$varName,$this->_pte());
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	function _exec2003_For($src){
		/*** unit("tag.TemplateParserTest"); */
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("for"))){
			$counterName = $this->_getVariableString($tag->getParameter("counter","counter"));
			$start = $this->_parsePlainVariable($tag->getParameter("start",0));
			$end   = $this->_parsePlainVariable($tag->getParameter("end",10));
			$step  = $this->_parsePlainVariable($tag->getParameter("step",1));
			
			$function = sprintf("%s for(%s=%s;%s<=%s;%s+=%s): %s",
								$this->_pts(),$counterName,$start,$counterName,$end,$counterName,$step,$this->_pte());
			$function .= $tag->getRawValue();
			$function .= sprintf("%s endfor; %s",$this->_pts(),$this->_pte());
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}		
		return $src;
	}
	function _exec2004_If($src){
		/*** unit("tag.TemplateParserTest"); */
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("if"))){
			$pattern = "";
			$parse = array();
			$value = $tag->getRawValue();
			$arg1 = $tag->getParameter("param",false);			
			$arg1 = (preg_match("/^[a-zA-Z_][\w]*$/",$arg1)) ? "$".$arg1 : $this->_parsePlainVariable($arg1);
			$isarg1 = (preg_match("/^[\$\'\"]/",$arg1)) ? true : false;
			$arg1		= (!is_bool($arg1) && !$isarg1) ? sprintf("'%s'",$arg1) : $arg1;
			$pattern	= ($isarg1 &&  $arg1[0] == "\$" && strpos($arg1,"->") === false) ? sprintf("isset(%s) && ",$arg1) : "";
	
			if($tag->isParameter("value")){
				$arg2		= $this->_parsePlainVariable($tag->getParameter("value"));
				$isarg2		= (preg_match("/^[\$\'\"]/",$arg2)) ? true : false;
				$arg2		= (!is_bool($arg2) && !$isarg2) ? sprintf("'%s'",$arg2) : $arg2;

				$pattern	.= ($isarg2 && $arg2[0] == "\$" && strpos($arg2,"->") === false) ? sprintf("isset(%s) && ",$arg2) : "";
				$pattern	.= sprintf("%s == %s",$arg1,$arg2);
			}else{
				$pattern	.= sprintf("%s",$arg1);
			}
			$function	= sprintf("%s if(%s): %s",$this->_pts(),$pattern,$this->_pte());
			
			if(preg_match("/(<[\s]*".TemplateParser::withNamespace("else")."[^>]*[\s]*\/>)/i",$tag->getRawValue(),$parse)){
				$value	= str_replace($parse[1],sprintf("%s else: %s",$this->_pts(),$this->_pte()),$value);
			}
			$function	.= sprintf("%s",$value);
			$function	.= sprintf("%s endif; %s",$this->_pts(),$this->_pte());
			
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);			
		}
		return $src;
	}
	function _exec2005_IfNot($src){
		/*** unit("tag.TemplateParserTest"); */
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("ifnot"))){
			$pattern	= "";
			$parse		= array();
			$value		= $tag->getRawValue();
			$arg1 = $tag->getParameter("param",false);			
			$arg1 = (preg_match("/^[a-zA-Z_][\w]*$/",$arg1)) ? "$".$arg1 : $this->_parsePlainVariable($arg1);
			$isarg1		= (preg_match("/^[\$\'\"]/",$arg1)) ? true : false;
			$arg1		= (!is_bool($arg1) && !$isarg1) ? sprintf("'%s'",$arg1) : $arg1;
			$pattern	= ($isarg1 && $arg1[0] == "\$" && strpos($arg1,"->") === false) ? sprintf("isset(%s) && ",$arg1) : "";
	
			if($tag->isParameter("value")){
				$arg2		= $this->_parsePlainVariable($tag->getParameter("value"));
				$isarg2		= (preg_match("/^[\$\'\"]/",$arg2)) ? true : false;
				$arg2		= (!is_bool($arg2) && !$isarg2) ? sprintf("'%s'",$arg2) : $arg2;
				$pattern	.= ($isarg2 && $arg2[0] == "\$" && strpos($arg2,"->") === false) ? sprintf("isset(%s) && ",$arg2) : "";
				$pattern	.= sprintf("%s != %s",$arg1,$arg2);
			}else{
				$pattern	.= sprintf("!%s",$arg1);
			}
			$function	= sprintf("%s if(%s): %s",$this->_pts(),$pattern,$this->_pte());

			if(preg_match("/(<[\s]*".TemplateParser::withNamespace("else")."[^>]*[\s]*\/>)/i",$tag->getRawValue(),$parse)){
				$value	= str_replace($parse[1],sprintf("%s else: %s",$this->_pts(),$this->_pte()),$value);
			}
			$function	.= sprintf("%s",$value);
			$function	.= sprintf("%s endif; %s",$this->_pts(),$this->_pte());

			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	
	/**
	 * array(),null,""はhasnot
	 *
	 * @param unknown_type $src
	 */
	function _exec2006_hasnot($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("hasnot"))){
			$param = $this->_parsePlainVariable($tag->getParameter("param","param"));
			$varName = sprintf("\$_".uniqid("RHACO_LOOP"));

			$function = "";
			$function .= sprintf("%s%s=%s%s",$this->_pts(),$varName,$this->_getVariableString($param),$this->_pte());
			$function .= sprintf("%sif(%s === null || (is_array(%s) && empty(%s)) || %s === \"\" ):%s",$this->_pts(),$varName,$varName,$varName,$varName,$this->_pte());
			$function .= $tag->getRawValue();
			$function	.= sprintf("%s endif; %s",$this->_pts(),$this->_pte());
			
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	function _exec2006_has($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("has"))){
			$param = $this->_parsePlainVariable($tag->getParameter("param","param"));
			$varName = sprintf("\$_".uniqid("RHACO_LOOP"));

			$function = "";
			$function .= sprintf("%s%s=%s%s",$this->_pts(),$varName,$this->_getVariableString($param),$this->_pte());
			$function .= sprintf("%sif(%s !== null && ( (!is_string(%s) && !is_array(%s)) || (is_string(%s) && %s !== \"\") || (is_array(%s) && !empty(%s)) ) ):%s",$this->_pts(),$varName,$varName,$varName,$varName,$varName,$varName,$varName,$this->_pte());
			$function .= $tag->getRawValue();
			$function	.= sprintf("%s endif; %s",$this->_pts(),$this->_pte());
			
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	function withNamespace($value){
		/***
		 * $parser = new TemplateParser();
		 * eq("rt:hoge",$parser->withNamespace("hoge"));
		 */
		return "rt:".$value;
	}
	function _getCacheUrl($templateFileName){
		return parent::_getCacheUrl(implode("-",array_merge(ArrayUtil::arrays($templateFileName),ArrayUtil::arrays($this->blockList))));
	}
	function _replaceSpecialVariables($src){
		return $this->_toStaticVariable("TemplateFormatter","f",parent::_replaceSpecialVariables($src));
	}
}
?>