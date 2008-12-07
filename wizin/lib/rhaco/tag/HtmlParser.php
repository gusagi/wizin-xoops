<?php
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("tag.TemplateParser");
Rhaco::import("lang.Variable");
Rhaco::import("lang.DateUtil");
Rhaco::import("io.FileUtil");
Rhaco::import("network.Url");
Rhaco::import("lang.ArrayUtil");
/**
 * HTMLをフォーマットする
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class HtmlParser extends TemplateParser{
	function HtmlParser($template=null,$path=null,$url=null){
		parent::TemplateParser($template,$path,$url);
	}
	

	function _parsePrintVariable($src){
		foreach($this->_matchVariable($src) as $variable){
			$name	= $this->_parsePlainVariable($variable);
			$check	= $this->_variableCheckValue($name);

			if(Variable::bool(Rhaco::constant("HTML_TEMPLATE_ARG_ESCAPE")) && 
				strpos($variable,"{\$f.noop(") === false && 
				strpos($variable,"{\$f.htmlencode(") === false){
				$name = "TemplateFormatter::htmlencode(".$name.")";
			}
			$value = $check[0].$this->_pts()."print(".$name.");".$this->_pte().$check[1];
			$src = str_replace(array($variable."\n",$variable),array($value."\n\n",$value),$src);
		}
		return $src;
	}
	function _doRead1100_Meta($src){
		/*** unit("tag.HtmlParserTest"); */
		if($this->encodeType != ""){
			if(preg_match("/<meta /i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
				foreach($tag->getIn("meta") as $obj){
					$equiv = $obj->getParameter("http-equiv");

					if(!empty($equiv)){
						if(preg_match("/Content-Type/i",$equiv)){
							$obj->setParameter("content",sprintf("text/html; charset=%s",$this->encodeType));
							$src = str_replace($obj->getPlain(),$obj->get(),$src);
						}
					}
				}
				unset($obj,$equiv);
			}
		}
		return $src;
	}
	function _exec2100_Iframe($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<iframe .+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			while(true){
				$list = $tag->getIn("iframe");
				if(sizeof($list) <= 0) break;
				$href = 0;
				foreach($list as $obj){
					if($this->_isReference($obj)){
						$url = Url::parseAbsolute(dirname(Url::parseAbsolute($this->path,$this->filename)),$obj->getParameter("href"));
						$src = str_replace($obj->getPlain(),$this->read($url),$src);
						$href++;	
					}
				}
				if($href == 0) break;
				$tag->set($src,"body");
			}
		}
		return $src;
	}
	function _exec2101_Form($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<form .+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("form") as $obj){
				$ispre = false;
				if(!$obj->isParameter("enctype")){
					foreach($obj->getIn("input") as $input){
						if(strtolower($input->getParameter("type")) == "file"){
							$obj->setParameter("enctype","multipart/form-data");
							$obj->setParameter("method","post");
							$ispre = true;
							break;
						}
					}
				}
				if($this->_isReference($obj)){
					foreach($obj->getIn("input") as $tag){
						if(!$tag->isParameter(TemplateParser::withNamespace("reference"))){
							switch(strtolower($tag->getParameter("type","text"))){
								case "button":
								case "submit":
									break;
								default:
									$tag->setParameter(TemplateParser::withNamespace("reference"),"true");
									$obj->setValue(str_replace($tag->getPlain(),$tag->get(),$obj->getValue()));
									$ispre = true;
							}
						}
					}
					foreach($obj->getIn("select") as $tag){
						if(!$tag->isParameter(TemplateParser::withNamespace("reference"))){
							$tag->setParameter(TemplateParser::withNamespace("reference"),"true");
							$obj->setValue(str_replace($tag->getPlain(),$tag->get(),$obj->getValue()));
							$ispre = true;
						}
					}
					foreach($obj->getIn("textarea") as $tag){
						if(!$tag->isParameter(TemplateParser::withNamespace("reference"))){
							$tag->setParameter(TemplateParser::withNamespace("reference"),"true");
							$obj->setValue(str_replace($tag->getPlain(),$tag->get(),$obj->getValue()));
							$ispre = true;
						}
					}
				}
				$src = str_replace($obj->getPlain(),$obj->get(),$src);
			}
		}
		return $src;
	}
	function _exec2101_Input($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<input .+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("input") as $obj){
				$originalName = $obj->getParameter("name",$obj->getParameter("id"));
				$ispre = false;

				if(!empty($originalName)){
					$type = strtolower($obj->getParameter("type","text"));
					if($type == "checkbox" && substr($originalName,-2) !== "[]"){
						$old = $obj->get();
						$obj->setParameter("name",$this->_toFormElementName($originalName)."[]");
						$ispre = true;
					}else{
						$obj->setParameter("name",$this->_toFormElementName($originalName));						
					}
					if($this->_isReference($obj)){
						$value = $this->_parsePlainVariable($obj->getParameter("value"));
						switch($type){
							case "checkbox":
							case "radio":
								$name = $this->_parsePlainVariable($this->_getFormVariableName($originalName));
								$value = $this->_parsePlainVariable($obj->getParameter("value","true"));
								$value = (substr($value,0,1) != "\$") ? sprintf("'%s'",$value) : $value;
								$selected = sprintf("if(isset(%s) && ((preg_match(\"/(true)|(false)/i\",%s)".
													" ? Variable::bool(%s,true) : %s) == %s || in_array(%s,ArrayUtil::arrays(%s),true))){print('checked');}",
													$name,$value,$name,$name,$value,$value,$name);
								$obj->removeAttribute("checked");
								$obj->setAttribute(sprintf("%s%s%s",$this->_pts(),$selected,$this->_pte()));								
								break;
							case "text":
							case "hidden":
								$dateFormat = null;
								if($obj->isParameter(TemplateParser::withNamespace("dateFormat"))){
									$dateFormat = $obj->getParameter(TemplateParser::withNamespace("dateFormat"));
									$obj->removeParameter(TemplateParser::withNamespace("dateFormat"));
								}
							case "password":
								$format = !empty($dateFormat) ? "{\$f.dateformat(\$f.htmlencode(%s),'".$dateFormat."')}" : "{\$f.htmlencode(%s)}";
								$obj->setParameter("value",sprintf($format,
															((preg_match("/^\{\$(.+)\}$/",$originalName,$match)) ? 
																"{\$\$".$match[1]."}" : 
																"{\$".$originalName."}")));
						}
						$ispre = true;
					}else if($obj->isParameter(TemplateParser::withNamespace("param"))){
						switch($type){
							case "checkbox":
							case "radio":
								if($this->_selectedParam($obj,"checked")) $ispre = true;
						}
					}
					if($ispre) $src = str_replace($obj->getPlain(),$obj->get(),$src);
				}
			}
		}
		return $src;
	}
	function _exec2102_Textarea($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<textarea .+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("textarea") as $obj){
				if($this->_isReference($obj)){
					$originalName	= $obj->getParameter("name",$obj->getParameter("id"));
					$name			= $this->_parsePlainVariable($this->_getFormVariableName($originalName));

					if(!empty($originalName)){
						$obj->setParameter("name",$this->_toFormElementName($originalName));
						$obj->setValue(sprintf("{\$f.htmlencode(%s)}",((preg_match("/^{\$(.+)}$/",$originalName,$match)) ? "{\$\$".$match[1]."}" : "{\$".$originalName."}")));
						$src = str_replace($obj->getPlain(),$obj->get(),$src);
					}
				}
			}
		}
		return $src;
	}
	function _exec2103_Select_Option_Param($src){
		if(preg_match("/<option .*".TemplateParser::withNamespace("").".+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){		
			foreach($tag->getIn("select") as $obj){
				foreach($obj->getIn("option") as $option){
					if($this->_selectedParam($option)) $src = str_replace($option->getPlain(),$option->get(),$src);
				}						
			}
		}
		return $src;
	}
	function _exec2104_Select_Options($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<select .*".TemplateParser::withNamespace("").".+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("select") as $obj){
				if($obj->isParameter(TemplateParser::withNamespace("options"))){
					Logger::deprecated();
					/**
					 * @deprecated 
					 */						
					$varname = uniqid("var_");
					$keyname = uniqid("key_");
					$options = sprintf("<%s param=\"%s\" var=\"%s\" key=\"%s\">\n",TemplateParser::withNamespace("loop"),$obj->getParameter(TemplateParser::withNamespace("options")),$varname,$keyname);
					$options .= sprintf("<option value=\"{\$%s}\">{\$%s}</option>\n",$keyname,$varname);
					$options .= sprintf("</%s>\n",TemplateParser::withNamespace("loop"));
					$obj->setValue($this->_exec2002_Loop($options));
					$obj->removeParameter(TemplateParser::withNamespace("options"));
					
					if($obj->isParameter(TemplateParser::withNamespace("null"))){
						$label = $obj->getParameter(TemplateParser::withNamespace("null"));
						$obj->setValue("<option value=\"\">".$label."</option>".$obj->getValue());
						$obj->removeParameter(TemplateParser::withNamespace("null"));
					}
					$src = str_replace($obj->getPlain(),$obj->get(),$src);
				}else if($obj->isParameter(TemplateParser::withNamespace("param"))){
					$varname = uniqid("var_");
					$keyname = uniqid("key_");
					$options = sprintf("<%s param=\"%s\" var=\"%s\" key=\"%s\">\n",TemplateParser::withNamespace("loop"),$obj->getParameter(TemplateParser::withNamespace("param")),$varname,$keyname);
					$options .= sprintf("<option value=\"{\$%s}\">{\$%s}</option>\n",$keyname,$varname);
					$options .= sprintf("</%s>\n",TemplateParser::withNamespace("loop"));
					$obj->setValue($this->_exec2002_Loop($options));
					$obj->removeParameter(TemplateParser::withNamespace("param"));
					
					if($obj->isParameter(TemplateParser::withNamespace("null"))){
						$label = $obj->getParameter(TemplateParser::withNamespace("null"));
						$obj->setValue("<option value=\"\">".$label."</option>".$obj->getValue());
						$obj->removeParameter(TemplateParser::withNamespace("null"));
					}
					$src = str_replace($obj->getPlain(),$obj->get(),$src);
				}
			}
		}
		return $src;
	}
	
	function _exec2105_Select($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<select .+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("select") as $obj){
				if($this->_isReference($obj)){
					$originalName	= $obj->getParameter("name",$obj->getParameter("id"));
					$name			= $this->_parsePlainVariable($this->_getFormVariableName($originalName));

					if(!empty($originalName)){
						if(($obj->isAttribute("multiple") || Variable::bool($obj->getParameter("multiple"))) && substr($originalName,-2) !== "[]"){
							$obj->setParameter("name",$this->_toFormElementName($originalName)."[]");
						}else{
							$obj->setParameter("name",$this->_toFormElementName($originalName));
						}
						$select = $obj->get();

						foreach($obj->getIn("option") as $option){
							$value = $this->_parsePlainVariable($option->getParameter("value"));

							if(substr($value,0,1) != "\$"){
								$boolvalue = strtolower($value);

								if($boolvalue == "true" || $boolvalue == "false"){
									$value	= sprintf("%s",$boolvalue);
								}else{
									$value = sprintf("'%s'",$value);
								}
							}
							$selected = sprintf("if(isset(%s) && (%s === %s || in_array(%s,ArrayUtil::arrays(%s),true) || (%s == %s && preg_match(\"/^[\d]+\$/\",%s) && strlen(%s) == strlen(%s) ))){print(' selected');}",$name,$value,$name,$value,$name,$name,$value,$name,$value,$name);
							$option->removeAttribute("selected");
							$option->setAttribute(sprintf("%s%s%s",$this->_pts(),$selected,$this->_pte()),false);
							$select = str_replace($option->getPlain(),$option->get(),$select);							
						}
						$src = str_replace($obj->getPlain(),$select,$src);
					}
				}
			}
		}
		return $src;
	}
	function _exec2106_Ul($src){
		/*** unit("tag.HtmlParserTest"); */
		if(preg_match("/<ul .*".TemplateParser::withNamespace("").".+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("ul") as $obj){
				if($obj->isParameter(TemplateParser::withNamespace("param"))){
					$param = $this->_getVariableString($this->_parsePlainVariable($obj->getParameter(TemplateParser::withNamespace("param"))));
					$paramvar = "\$".uniqid("ul");
					$sbr = (strpos($obj->getPlain(),$obj->getStart()."\n") !== false) ? "\n" : "";
					$ebr = (strpos($src,$obj->getPlain()."\n") !== false) ? "\n" : "";

					$value = sprintf("<%s param=\"%s\" var=\"%s\" counter=\"%s\" key=\"%s\" offset=\"%s\" limit=\"%s\" first=\"%s\" last=\"%s\">",
						TemplateParser::withNamespace("loop"),
						$paramvar,
						$obj->getParameter(TemplateParser::withNamespace("var"),"var"),
						$obj->getParameter(TemplateParser::withNamespace("counter"),"counter"),
						$obj->getParameter(TemplateParser::withNamespace("key"),"key"),
						$obj->getParameter(TemplateParser::withNamespace("offset"),"1"),
						$obj->getParameter(TemplateParser::withNamespace("limit"),"0"),
						$obj->getParameter(TemplateParser::withNamespace("first"),"first"),
						$obj->getParameter(TemplateParser::withNamespace("last"),"last")
					);
					$value .= $obj->getRawValue();
					$value .= sprintf("</%s>",TemplateParser::withNamespace("loop"));
					$obj->setValue($this->_exec2002_Loop($value));

					$obj->removeParameter(TemplateParser::withNamespace("param"));
					$obj->removeParameter(TemplateParser::withNamespace("var"));
					$obj->removeParameter(TemplateParser::withNamespace("counter"));
					$obj->removeParameter(TemplateParser::withNamespace("key"));
					$obj->removeParameter(TemplateParser::withNamespace("offset"));
					$obj->removeParameter(TemplateParser::withNamespace("limit"));
					$obj->removeParameter(TemplateParser::withNamespace("first"));
					$obj->removeParameter(TemplateParser::withNamespace("last"));
					$function = "";
					$function .= sprintf("%s%s = %s;%s",$this->_pts(),$paramvar,$param,$this->_pte());
					$function .= sprintf("%sif(!empty(%s) && is_array(%s)):%s",$this->_pts(),$paramvar,$paramvar,$this->_pte());
					$function .= $obj->getStart().$sbr;
					$function .= $obj->getValue();
					$function .= $obj->getEnd().$ebr;
					$function .= sprintf("%sendif;%s",$this->_pts(),$this->_pte());
					$src = str_replace($obj->getPlain().$ebr,$function.$ebr,$src);
					unset($function);
				}
			}
		}
		return $src;
	}
	function _exec2107_Table($src){
		/*** unit("tag.HtmlParserTest"); */
			if(preg_match("/<table .*".TemplateParser::withNamespace("").".+/i",$src) && SimpleTag::setof($tag,"<html_parser>".$src."</html_parser>","html_parser")){
			foreach($tag->getIn("table") as $obj){
				if($obj->isParameter(TemplateParser::withNamespace("param"))){
					$param = $this->_getVariableString($this->_parsePlainVariable($obj->getParameter(TemplateParser::withNamespace("param"))));
					$paramvar = "\$".uniqid("table");
					$null = $obj->getParameter(TemplateParser::withNamespace("null"));
					$sbr = (strpos($obj->getPlain(),$obj->getStart()."\n") !== false) ? "\n" : "";
					$ebr = (strpos($src,$obj->getPlain()."\n") !== false) ? "\n" : "";
					$counter = $obj->getParameter(TemplateParser::withNamespace("counter"),"counter");

					$rawvalue = $obj->getRawValue();
					$value = sprintf("<%s param=\"%s\" var=\"%s\" counter=\"%s\" key=\"%s\" offset=\"%s\" limit=\"%s\" first=\"%s\" last=\"%s\">",
						TemplateParser::withNamespace("loop"),
						$paramvar,
						$obj->getParameter(TemplateParser::withNamespace("var"),"var"),
						$counter,
						$obj->getParameter(TemplateParser::withNamespace("key"),"key"),
						$obj->getParameter(TemplateParser::withNamespace("offset"),"1"),
						$obj->getParameter(TemplateParser::withNamespace("limit"),"0"),
						$obj->getParameter(TemplateParser::withNamespace("first"),"first"),
						$obj->getParameter(TemplateParser::withNamespace("last"),"last")
					);
					if(SimpleTag::setof($t,$rawvalue,"tbody")){
						$value .= $this->_tableTrEvenodd($t->getRawValue(),$counter);
						$value .= sprintf("</%s>",TemplateParser::withNamespace("loop"));
						$t->setValue($value);
						$obj->setValue($this->_exec2002_Loop(str_replace($t->getPlain(),$t->get(),$rawvalue)));
					}else{
						$value .= $this->_tableTrEvenodd($rawvalue,$counter);
						$value .= sprintf("</%s>",TemplateParser::withNamespace("loop"));
						$obj->setValue($this->_exec2002_Loop($value));
					}
					$obj->removeParameter(TemplateParser::withNamespace("param"));
					$obj->removeParameter(TemplateParser::withNamespace("var"));
					$obj->removeParameter(TemplateParser::withNamespace("counter"));
					$obj->removeParameter(TemplateParser::withNamespace("key"));
					$obj->removeParameter(TemplateParser::withNamespace("offset"));
					$obj->removeParameter(TemplateParser::withNamespace("limit"));
					$obj->removeParameter(TemplateParser::withNamespace("first"));
					$obj->removeParameter(TemplateParser::withNamespace("last"));
					$obj->removeParameter(TemplateParser::withNamespace("null"));
					$function = "";
					$function .= sprintf("%s%s = %s;%s",$this->_pts(),$paramvar,$param,$this->_pte());
					$function .= sprintf("%sif(!empty(%s) && is_array(%s)):%s",$this->_pts(),$paramvar,$paramvar,$this->_pte());
					$function .= $obj->getStart().$sbr;
					$function .= $obj->getValue();
					$function .= $obj->getEnd().$ebr;
					$function .= sprintf("%selse:%s",$this->_pts(),$this->_pte());
					$function .= $null;					
					$function .= sprintf("%sendif;%s",$this->_pts(),$this->_pte());
					$src = str_replace($obj->getPlain().$ebr,$function.$ebr,$src);
					unset($function);
				}
			}
		}
		return $src;
	}
	function _tableTrEvenodd($src,$counter){
		$tag = SimpleTag::anyhow($src);
		foreach($tag->getIn("tr") as $tr){
			$class = $tr->getParameter("class");
			if($class == "even" || $class == "odd"){
				$tr->setParameter("class","{\$f.evenodd(\$".$counter.")}");
				$src = str_replace($tr->getPlain(),$tr->get(),$src);
			}
		}
		return $src;
	}
	function _exec1009_pager($src){
		while(SimpleTag::setof($tag,$src,TemplateParser::withNamespace("pager"))){
			$param = $this->_parsePlainVariable($tag->getParameter("param","pager"));
			$function = "";

			if(!empty($param)){
				$param = $this->_getVariableString($param);
				$body = $tag->getValue();
				$counter = uniqid("pager_");

				$function .= sprintf("%sif(is_object(%s) && (\"%s\" == strtolower(get_class(%s)) || is_subclass_of(%s,%s)) && %s->isPage()):%s",$this->_pts(),$param,"paginator",$param,$param,"paginator",$param,$this->_pte());
				if(empty($body)){
					$tagtype = $tag->getParameter("type","span");
					$stag = (empty($tagtype)) ? "" : "<".$tagtype.">";
					$etag = (empty($tagtype)) ? "" : "</".$tagtype.">";

					$isap = Variable::bool($tag->getParameter("ap",true));
					$iscounter = Variable::bool($tag->getParameter("counter",true));
					$navi = ArrayUtil::lowerflip(explode(",",$tag->getParameter("navi","prev,next,first,last,counter")));

					if($isap && isset($navi["prev"])) $function .= sprintf("<rt:if param=\"{%s.isPrev()}\">%s<a href=\"{\$rhaco.uri()}?{%s.query(%s.prevPage)}\">%s</a>%s</rt:if>",$param,$stag,$param,$param,Message::_("Prev"),$etag);

					if($iscounter){
						if(isset($navi["first"])){
							$function .= sprintf("<rt:ifnot param=\"{%s.isPageFirst()}\">",$param);
							$function .= sprintf("%s<a href=\"{\$rhaco.uri()}?{%s.query(1)}\">1</a>%s",$stag,$param,$etag);
							$function .= $stag."...".$etag;
							$function .= "</rt:ifnot>";
						}
						if(isset($navi["counter"])){
							$function .= sprintf("<rt:for start=\"{%s.pageFirst}\" end=\"{%s.pageLast}\" counter=\"%s\">",$param,$param,$counter);
							$function .= sprintf("%s<rt:if param=\"{\$%s}\" value=\"{%s.page}\"><strong>{\$%s}</strong><rt:else /><a href=\"{\$rhaco.uri()}?{%s.query(\$%s)}\">{\$%s}</a></rt:if>%s",$stag,$counter,$param,$counter,$param,$counter,$counter,$etag);
							$function .= "</rt:for>";
						}
						if(isset($navi["last"])){
							$function .= sprintf("<rt:ifnot param=\"{%s.isPageFinish()}\">",$param);
							$function .= $stag."...".$etag;
							$function .= sprintf("%s<a href=\"{\$rhaco.uri()}?{%s.query(%s.pageFinish)}\">{%s.pageFinish}</a>%s",$stag,$param,$param,$param,$etag);
							$function .= "</rt:ifnot>";
						}
					}
					if($isap && isset($navi["next"])) $function .= sprintf("<rt:if param=\"{%s.isNext()}\">%s<a href=\"{\$rhaco.uri()}?{%s.query(%s.nextPage)}\">%s</a>%s</rt:if>",$param,$stag,$param,$param,Message::_("Next"),$etag);
				}else{
					$function .= $body;
				}
				$function .= sprintf("%sendif;%s",$this->_pts(),$this->_pte());
			}
			$src = str_replace($tag->getPlain(),$function,$src);
			unset($function);
		}
		return $src;
	}
	function _selectedParam(&$obj,$attrvalue="selected"){
		if($obj->isParameter(TemplateParser::withNamespace("param"))){
			$param = "param=\"".$obj->getParameter(TemplateParser::withNamespace("param"))."\"";
			$value = ($obj->isParameter(TemplateParser::withNamespace("value"))) ? " value=\"".$obj->getParameter(TemplateParser::withNamespace("value"))."\"" : "";
			$obj->removeParameter(TemplateParser::withNamespace("param"));
			$obj->removeParameter(TemplateParser::withNamespace("value"));
			$obj->removeAttribute("selected");

			$selected = $this->_exec2004_If(sprintf("<%s %s%s> %s</%s>",TemplateParser::withNamespace("if"),$param,$value,$attrvalue,TemplateParser::withNamespace("if")));
			$obj->setAttribute($selected,false);
			return true;
		}
		return false;
	}
	function _toFormElementName($name){
		return (preg_match("/(.+)\.(.+)?$/",$name,$variableName)) ? sprintf("%s[%s]",$variableName[1],$variableName[2]) : $name;
	}
	function _getFormVariableName($name){
		if(strpos($name,"[") && preg_match("/^(.+)\[([^\"\']+)\]$/",$name,$match)){
			return "{\$".$match[1]."[\"".$match[2]."\"]"."}";
		}
		return "{\$".$name."}";
	}
	function _isReference(&$tagobj){
		if(Variable::bool($tagobj->getParameter(TemplateParser::withNamespace("reference"),false))){
			$tagobj->removeParameter(TemplateParser::withNamespace("reference"));
			return true;
		}
		return false;
	}
}
?>