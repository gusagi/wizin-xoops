<?php
Rhaco::import("tag.model.SimpleTagParameter");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.StringUtil");
/**
 * タグ Model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class SimpleTag{
	var $name			= "";
	var $value			= "";
	var $plain			= "";
	var $parameterList	= array();
	var $attributeList	= array();
	var $pos			= 0;
	
	var $start = "";
	var $end = "";
	var $normalization = false;

	/**
	 * コンストラクタ
	 *
	 * @param string $name
	 * @param string $value
	 * @return SimpleTag
	 */
	function SimpleTag($name="",$value="",$param=array()){
		$this->setName($name);
		foreach(ArrayUtil::arrays($value) as $value) $this->addValue($value);
		foreach(ArrayUtil::arrays($param) as $id => $value) $this->setParameter($id,$value);
	}
	
	/**
	 * 文字列からSimpleTagをセットする
	 * $normalizationは対象の構成によっては著しくパフォーマンスがおちます。
	 *
	 * @param string $plain
	 * @param string $name
	 * @param boolean $normalization
	 * @return boolean
	 */
	function set($plain,$name="",$normalization=false){
		/***
		 * $tag = new SimpleTag();
		 * assert($tag->set("<hoge />","hoge"));
		 * assert($tag->set("<hoge /><hoge />","hoge"));
		 * eq("<hoge />",$tag->getPlain());
		 * assert($tag->set("<xyz /><hoge><abc /><bbc>news</bbc></hoge>","hoge"));
		 * eq("<hoge><abc /><bbc>news</bbc></hoge>",$tag->getPlain());
		 * assert($tag->set("<hoge><hoge></hoge></hoge>","hoge"));
		 * assert($tag->set("<hoge><hoge /></hoge>","hoge"));
		 * assert($tag->set("<outline><outline xmlUrl=\"hoge\" /></outline>","outline"));
		 * assert($tag->set("<outline xmlUrl=\"hoge\" /></outline>","outline"));
		 * assert($tag->set("<outline xmlUrl=\"ho/ge\">aa</outline>","outline"));
		 * eq("ho/ge",$tag->getParameter("xmlUrl"));
		 * assert($tag->set("<outline xmlUrl=\"ho/ge\" /></outline>","outline"));
		 * eq("ho/ge",$tag->getParameter("xmlUrl"));
		 * 
		 * // <outline xmlUrl=\"ho/>
		 * assert($tag->set("<outline xmlUrl=\"ho/>ge\">aa</outline>","outline"));
		 * eq("xmlUrl",$tag->getAttribute("xmlUrl"));
		 * eq("ho",$tag->getAttribute("ho"));
		 * 
		 * assert($tag->set("<outline xmlUrl=\"ho/>ge\">aa</outline>","outline",true));
		 * eq("ho/>ge",$tag->getParameter("xmlUrl"),"normalization");
		 * 
		 * assert($tag->set("<outline xmlUrl=\"ho\\'ge\">aa</outline>","outline",true));
		 * eq("ho\\'ge",$tag->getParameter("xmlUrl"));
		 * 
		 * assert($tag->set("<outline xmlUrl=\"ho\\\"ge\">aa</outline>","outline",true));
		 * eq("ho\\\"ge",$tag->getParameter("xmlUrl"));
		 * 
		 * assert($tag->set("<opml hoge=\"<outline \"><outline xmlUrl=\"ho<ge\">aa</outline>","outline",true));
		 * eq("ho<ge",$tag->getParameter("xmlUrl"));
		 */
		$this->setNormalization($normalization);
		$plain = (is_string($plain)) ? $plain : null;
		if(empty($name) && preg_match("/<([\w\:\-]+)[\s][^>]*?>|<([\w\:\-]+)>/is",$plain,$parse)){
			$name = str_replace(array("\r\n","\r","\n"),"",(empty($parse[1]) ? $parse[2] : $parse[1]));
		}
		if(!empty($name)){
			$this->plain			= $plain;
			$this->name				= $name;
			$this->value			= "";
			$this->parameterList	= array();
			$this->attributeList	= array();
			if($this->plain != "")	return $this->_parse();
		}
		return false;
	}	
	/**
	 * staticにSimpleTag::setを行う
	 * $varにsetされたSimpleTagインスタンスが入ります。
	 * $normalizationはparameterがクオートされてない等を補正する
	 * $normalizationは対象の構成によっては著しくパフォーマンスがおちます。
	 *
	 * @static 
	 * @param unknown_type $var
	 * @param string $plain
	 * @param string $name
	 * @param boolea $normalization
	 * @return boolean
	 */
	function setof(&$var,$plain,$name="",$normalization=false){
		/***
		 * $xml = "<tag><xyz /><hoge><abc /><bbc>news</bbc></hoge></tag>";
		 * $result = SimpleTag::setof($tag,$xml,"tag");
		 * eq(true,$result);
		 * assert(Variable::istype("SimpleTag",$tag));
		 * 
		 * $xml = "<tag><xyz /><hoge><abc /><bbc>news</bbc></hoge></tag>";
		 * $result = SimpleTag::setof($tag,$xml,"gen");
		 * eq(false,$result);
		 * eq(null,$tag);
		 */
		$var = new SimpleTag();
		$result = $var->set($plain,$name,$normalization);
		if($result === false) $var = null;
		return $result;
	}
	
	/**
	 * 適当な名前でとにかくSimpleTagにする
	 *
	 * @param unknown_type $var
	 * @param anyhow $plain
	 */
	function anyhow($plain){
		/***
		 * $xml = "<abc>hoge1</abc><abc>hoge2</abc><abc>hoge3</abc>";
		 * $tag = SimpleTag::anyhow($xml);
		 * assert(Variable::istype("SimpleTag",$tag));
		 * eq(3,sizeof($tag->getIn("abc")));
		 */
		$uniq = uniqid("Anyhow_");
		$var = new SimpleTag();
		$var->set("<".$uniq.">".$plain."</".$uniq.">",$uniq);		
		return $var;
	}
	
	/**
	 * フォーマットされた文字列を取得
	 * @param booelan $isDec XML宣言を追加する
	 * @return string
	 */
    function get($isDec=false){
    	/***
    	 * $tag = new SimpleTag("data","hogehoge",array("abc"=>123,"def"=>"xyz"));
    	 * eq('<data abc="123" def="xyz">hogehoge</data>',$tag->get());
    	 * 
    	 * $tag = new SimpleTag("data",null,array("abc"=>123,"def"=>"xyz"));
    	 * eq('<data abc="123" def="xyz" />',$tag->get());
    	 */
		$tag = "";
		if($this->getName() == ""){
			$tag = $this->getRawValue();
		}else{
			$tag = ($this->getRawValue() != "") ? $this->getStart().$this->getRawValue().$this->getEnd() : $this->getStart();
		}
		return ($isDec) ? "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".StringUtil::encode($tag) : $tag;
    }
    
    /**
     * パラメータをセットする
     *
     * @param string $tagParameterOrName
     * @param string $value
     */
	function setParameter($tagParameterOrName,$value=""){
		if(!Variable::istype("SimpleTagParameter",$tagParameterOrName)){
			$tagParameterOrName = new SimpleTagParameter($tagParameterOrName,$value);
		}
		$this->parameterList[$tagParameterOrName->getId()] = $tagParameterOrName;
	}
	
	/**
	 * パラメータを取得する
	 *
	 * @param string $parameterId
	 * @param string $defaultValue
	 * @return string
	 */
	function getParameter($parameterId="",$defaultValue=null){
		if(empty($parameterId)) return ArrayUtil::arrays($this->parameterList);
		$parameterId = strtolower($parameterId);
		if($this->isParameter($parameterId) && Variable::istype("SimpleTagParameter",$this->parameterList[$parameterId])){
			return $this->parameterList[$parameterId]->getValue();
		}
		return $defaultValue;
	}
	function param($parameterId="",$defaultValue=null){
		/***
		 * $xml = "<tag frog='guwa'><abc>123</abc></tag>";
		 * 
		 * SimpleTag::setof($tag,$xml,"tag");
		 * eq("guwa",$tag->param("frog"));
		 * eq(null,$tag->param("python"));
		 * eq("hoge",$tag->param("python","hoge"));
		 * 
		 * $xml = "<tag frog=guwa><abc>123</abc></tag>";
		 * 
		 * 
		 */
		return $this->getParameter($parameterId,$defaultValue);
	}
	
	/**
	 * パラメータを削除する
	 *
	 * @param string $name
	 */
	function removeParameter($name=""){
		/***
		 * $xml = "<tag frog='guwa'><abc>123</abc></tag>";
		 * 
		 * SimpleTag::setof($tag,$xml,"tag");
		 * $tag->removeParameter("frog");
		 * eq("<tag><abc>123</abc></tag>",$tag->get());
		 * 
		 */
		if(empty($name)){
			$this->parameterList = array();
		}else{
			unset($this->parameterList[strtolower($name)]);
		}
	}
	
	/**
	 * 指定のパラメータが存在するか
	 *
	 * @param string $parameterId
	 * @return booleans
	 */
	function isParameter($parameterId){
		return isset($this->parameterList[strtolower($parameterId)]);
	}
	
	/**
	 * アトリビュートをセットする
	 *
	 * @param string $value
	 */
	function setAttribute($value,$space=true){
		$this->attributeList[strtolower($value)] = array($value,$space);
	}
	
	/**
	 * アトリビュートを取得する
	 *
	 * @param string $name
	 * @return string
	 */
	function getAttribute($name=""){
		if(empty($name)) return ArrayUtil::arrays($this->attributeList);
		$name = strtolower($name);
		return (isset($this->attributeList[$name])) ? $this->attributeList[$name][0] : "";
	}
	function attr($name=""){
		/***
		 * $xml = "<tag selected><abc>123</abc></tag>";
		 * 
		 * SimpleTag::setof($tag,$xml,"tag");
		 * eq("selected",$tag->attr("selected"));
		 * 
		 */		
		return $this->getAttribute($name);
	}
	
	/**
	 * アトリビュートを削除する
	 *
	 * @param string $name
	 */
	function removeAttribute($name=""){
		/***
		 * $tag = new SimpleTag("tag");
		 * $tag->setAttribute("default");
		 * eq('<tag default />',$tag->get());
		 * $tag->removeAttribute("default");
		 * eq('<tag />',$tag->get());
		 */
		if(empty($name)){
			$this->attributeList = array();
		}else{
			unset($this->attributeList[strtolower($name)]);
		}
	}
	
	/**
	 * アトリビュートが存在するか
	 *
	 * @param string $name
	 * @return string
	 */
	function isAttribute($name){
		return isset($this->attributeList[strtolower($name)]);
	}
	/**
	 * value内のarray(tag.model.SimpleTag)を取得する
	 *
	 * @param string $tagName
	 * @param boolean $extraction 取得したtag.model.SimpleTagをvalueから削除するか
	 * @return array(tag.model.SimpleTag)
	 */
	function getIn($tagName,$extraction=false,$offset=0,$length=0){
		/***
		 * $src = "<tag><a b='1' /><a>abc</a><b>0</b><a b='1' /><a /></tag>";
		 * $list = array();
		 * 
		 * if(SimpleTag::setof($tag,$src,"tag")){
		 * 		foreach($tag->getIn("a") as $a){
		 * 			eq("a",$a->getName());
		 * 			$list[] = $a;
		 * 		}
		 * }
		 * eq(4,sizeof($list));
		 * 
		 * SimpleTag::setof($tag,"<opml><outline><outline xmlUrl=\"hoge\" /></outline></opml>","opml");
		 * eq(1,sizeof($tag->getIn("outline")),"opml outline");
		 * 
		 * SimpleTag::setof($tag,"<tag><data1 /><data2 /><data1 /><data3 /><data3 /><data2 /><data4 /></tag>","tag");
		 * $result = $tag->getIn("data2",true);
		 * eq("<data1 /><data1 /><data3 /><data3 /><data4 />",$tag->getValue());
		 * eq("<tag><data1 /><data1 /><data3 /><data3 /><data4 /></tag>",$tag->getPlain());
		 * eq(2,sizeof($result));
		 * 
		 * SimpleTag::setof($tag,"<tag><data1 /><data2 /><data1 /><data3 /><data3 /><data2 /><data4 /></tag>","tag");
		 * $result = $tag->getIn(array("data2","data3"));
		 * eq("data2",$result[0]->getName());
		 * eq("data3",$result[1]->getName());
		 * eq("data3",$result[2]->getName());
		 * eq("data2",$result[3]->getName());
		 * 
		 * SimpleTag::setof($tag,"<tag><data1 /><data2 /><data1 /><data3 /><data3 /><data2 /><data4 /></tag>","tag");
		 * $result = $tag->getIn(array("data2","data3"),true);
		 * eq("<tag><data1 /><data1 /><data4 /></tag>",$tag->getPlain());
		 * eq("<data1 /><data1 /><data4 />",$tag->getValue());
		 * 
		 * $src = "<tag><abc><def var='123'><ghi>aaa</ghi><ghi>bbb</ghi><ghi>ccc</ghi></def></abc></tag>";
		 * SimpleTag::setof($tag,$src,"tag");
		 * $tags = $tag->getIn("ghi",true,1,1);
		 * if(assert(isset($tags[0]))){
		 * 	eq("<ghi>bbb</ghi>",$tags[0]->getPlain());
		 *  eq("<tag><abc><def var='123'><ghi>aaa</ghi><ghi>ccc</ghi></def></abc></tag>",$tag->getPlain());
		 * }
		 * 
		 * $html = '<html><input type="hidden" value="test > hogehoge" /><input type="hidden" value="test < hogehoge" /><input type="hidden" value="test > \"hogehoge\"" /></html>';
		 * if(SimpleTag::setof($tag,$html,"html",true)){
		 * 	$inputs = $tag->getIn("input");
		 *  eq(3,sizeof($inputs));
		 *  eq("test > hogehoge",$inputs[0]->getParameter("value"));
		 *  eq("test < hogehoge",$inputs[1]->getParameter("value"));
		 *  eq("test > \\\"hogehoge\\\"",$inputs[2]->getParameter("value"));
		 * }
		 * 
		 * $html = <<< __HDOC__
		 * <div>aaaa</div>
		 * <div style="background: url(http://example.jp/example.png);">bbbb</div>
		 * <div>cccc</div>
		 * __HDOC__;
		 * 
		 * SimpleTag::setof($tag, '<div>'.$html.'</div>', 'div');
		 * $divs = $tag->getIn('div');
		 * eq(3, count($divs),"DIV SLASH");
		 * 
		 * SimpleTag::setof($tag, '<body>'.$html.'</body>', 'body');
		 * $divs = $tag->getIn('div');
		 * eq(3, count($divs));
		 * 
		 * $html = <<< __HDOC__
		 * <div>aaaa</div>
		 * <div>bbbb</div>
		 * <div>cccc</div>
		 * __HDOC__;
		 * 
		 * SimpleTag::setof($tag, '<div>'.$html.'</div>', 'div');
		 * $divs = $tag->getIn('div');
		 * eq(3, count($divs),"DIV simple");
		 */
		$tagList = array();
		$count = 0;
		if(!empty($tagName) && $this->getRawValue() != ""){
			$plain = $this->getRawValue();
			$offset_before = array();

			while(true){
				if(is_array($tagName)){
					$tags = array();
					foreach($tagName as $name){
						if(SimpleTag::setof($gettag,$plain,$name,$this->normalization)) $tags[$gettag->pos] = $gettag;
					}
					if(empty($tags)) break;
					ksort($tags);
					foreach($tags as $tag) break;
				}else if(!SimpleTag::setof($tag,$plain,$tagName,$this->normalization)){
					break;
				}
				$plain = substr($plain,0,$tag->pos).substr($plain,$tag->pos+strlen($tag->plain));

				if($offset <= $count++){
					$tagList[] = $tag;

					if($extraction){
						$this->plain = str_replace($this->value,$plain,$this->plain);
						$this->value = $plain;						
					}
				}else{
					$offset_before[] = $tag;
				}
				if($length > 0 && $length <= ($count - $offset)) break;
			}
			if($extraction && !empty($offset_before)){
				$value = "";
				foreach($offset_before as $tag) $value .= $tag->getPlain();

				$before = substr($this->value,0,$offset_before[0]->pos);
				$after = substr($this->value,$offset_before[0]->pos);
				$this->plain = str_replace($this->value,($before.$value.$after),$this->plain);
				$this->value = ($before.$value.$after);
			}
		}
		return $tagList;
	}

	/**
	 * value内のarray(tag.model.SimpleTag)をフォーマットした文字列を取得
	 *
	 * @param string $tagName
	 * @param string $default
	 * @return string
	 */
	function getInValue($tagName,$default=null){
		/***
		 * $tag = new SimpleTag("hoge","<hogehoge>rhaco</hogehoge><hogehoge>django</hogehoge>");
		 * eq("rhaco",$tag->getInValue("hogehoge"));
		 * eq(null,$tag->getInValue("ruru"));
		 * eq("aid",$tag->getInValue("ruru","aid"));
		 */
		foreach($this->getIn($tagName) as $tag) return $tag->getValue();
		return $default;
	}
	
	/**
	 * 文字列により取得する
	 *
	 * @param string $path
	 * @param unknown_type $default
	 * @return unknown
	 */
	function f($path,$default=""){
		/***
		 * $src = "<tag><abc><def var='123'><ghi selected>hoge</ghi></def></abc></tag>";
		 * SimpleTag::setof($tag,$src,"tag");
		 * eq("hoge",$tag->f("abc.def.ghi.value()"));
		 * eq("123",$tag->f("abc.def.param('var')"));
		 * eq("selected",$tag->f("abc.def.ghi.attr('selected')"));
		 * eq("<def var='123'><ghi selected>hoge</ghi></def>",$tag->f("abc.def.plain()"));
		 * eq("",$tag->f("abc.def.xyz"));
		 * eq("rhaco",$tag->f("abc.def.xyz","rhaco"));
		 * 
		 * $src = "<tag><abc><def var='123'>".
		 * 			"<ghi selected>hoge</ghi>".
		 * 			"<ghi>".
		 * 				"<jkl>rails</jkl>".
		 * 			"</ghi>".
		 * 			"<ghi>django</ghi>".
		 * 			"</def></abc></tag>";
		 * SimpleTag::setof($tag,$src,"tag");
		 * eq("django",$tag->f("abc.def.ghi[2].value()"));
		 * eq("rails",$tag->f("abc.def.ghi[1].jkl.value()"));
		 * 
		 * eq("",$tag->f("abc.def.ghi.jkl.value()"));
		 * $src = "<tag><abc><def var='123'>".
		 * "<def><ghi selected>DRY</ghi></def>".
		 * "<def><def>".
		 * "<ghi>setup framework</ghi>".
		 * "</def></def>".
		 * "<ghi>and library</ghi>".
		 * "</def></abc></tag>";
		 * SimpleTag::setof($tag,$src,"tag");
		 * eq("setup framework",$tag->f("abc.def.def[1].def.ghi.value()"));
		 * eq("setup framework",$tag->f("abc.def.def[1].ghi.value()"));
		 * eq("setup framework",$tag->f("def.def[1].ghi.value()"));
		 * eq("",$tag->f("def[1].ghi.value()"));
		 * eq("",$tag->f("abc.def.def.def.ghi.value()"));
		 * eq(1,count($tag->f("in(def)")));
		 * eq(2,count($tag->f("def.in(def)")));
		 * eq(3,count($tag->f("in(ghi)")));
		 *
		 * 
		 * $src = <<< __XML__
		 * <tag>
		 * <abc>
		 * 	<def type='unknown' selected>hoge</def>
		 * 	<def><jkl>is</jkl></def>
		 * 	<ghi>hentai++</ghi>
		 * </abc>
		 * </tag>
		 * __XML__;
		 * 
		 * SimpleTag::setof($tag,$src,"tag");
		 * 
		 * $def = $tag->f("abc.in(def)");
		 * eq(2,count($def));
		 * eq($tag->f("abc.def"),$def[0]);
		 * 
		 * $ghi = $tag->f("abc.in(ghi)");
		 * assert(V::istype("SimpleTag",$ghi[0]));
		 * eq("hentai++",$ghi[0]->getValue());
		 * eq("",$tag->f("abc.mno"));
		 * neq($tag->f("abc.def[1].jkl"),$tag->f("jkl")); //posだけ違う
		 * eq($tag->f("abc.def[1].jkl.value()"),$tag->f("jkl.value()"));
		 * neq($tag->f("abc.jkl"),$tag->f("jkl")); //posだけ違う
		 * eq($tag->f("abc.jkl.value()"),$tag->f("jkl.value()"));
		 * neq($tag->f("abc.def[1].in(jkl)"),$tag->f("in(jkl)"));//posだけ違う
		 *
		 * $tag->f("def.save()","rhaco");
		 * eq("rhaco",$tag->f("abc.def.value()"));
		 * eq("<jkl>is</jkl>",$tag->f("abc.def[1].value()"));
		 * 
		 * $tag->f("abc.def.saveParam(type)","author");
		 * eq("author",$tag->f("abc.def.param(type)"));
		 * 
		 * $tag->f("abc.saveParam(type)","other");
		 * eq("other",$tag->f("abc.param(type)"));
		 * 
		 * $tag->f("abc.def.saveAttr(human)");
		 * eq("human",$tag->f("abc.def.attr(human)"));
		 * 
		 */
		$paths = explode(".",$path);
		$last = (strpos($path,"(") === false) ? null : array_pop($paths);
		$tag = Variable::copy($this);
		$route = array();

		foreach($paths as $p){
			$pos = 0;
			if(preg_match("/^(.+)\[([\d]+?)\]$/",$p,$matchs)) list($tmp,$p,$pos) = $matchs;
			$tags = $tag->getIn($p,false,$pos,1);
			if(isset($tags[0]) && Variable::istype("SimpleTag",$tags[0])){
				$tag = $tags[0];
				$route[] = $tag;
			}else{
				$tag = null;
				break;
			}
		}
		if(Variable::istype("SimpleTag",$tag)){
			if($last == null) return $tag;
			if($last == "value()") return $tag->getValue();
			if($last == "plain()") return $tag->getPlain();
			if(preg_match("/^param\((.+?)\)$/",$last,$matchs)) return $tag->param(preg_replace("/([\"\'])(.+?)\\1/","\\2",$matchs[1]));
			if(preg_match("/^attr\((.+?)\)$/",$last,$matchs)) return $tag->attr(preg_replace("/([\"\'])(.+?)\\1/","\\2",$matchs[1]));
			if(preg_match("/^in\((.+?)\)$/",$last,$matchs)) return $tag->getIn(preg_replace("/([\"\'])(.+?)\\1/","\\2",$matchs[1]));
			if(preg_match("/^save\(\)$/",$last)) return $this->_save($this,$route,$default);
			if(preg_match("/^saveParam\((.+?)\)$/i",$last,$matchs)) return $this->_saveParameter($this,$route,$matchs[1],$default);
			if(preg_match("/^saveAttr\((.+?)\)$/i",$last,$matchs)) return $this->_saveAttribute($this,$route,$matchs[1],$default);
			if(preg_match("/^add\(\)$/",$last)){
				if(!Variable::istype("SimpleTag",$default)) $default = new SimpleTag($default);
				return $this->_save($this,$route,$tag->getValue().$default->get());			
			}
		}
		return $default;
	}
	function getValue(){
		return (strpos(trim($this->value),"<![CDATA[") === 0 && preg_match("/<!\[CDATA\[(.+)\]\]>/is",$this->value,$match)) ? $match[1] : $this->value;
	}
	function value(){
		/***
		 * $tag = new SimpleTag("tag","<![CDATA[ abc ]]>");
		 * eq(" abc ",$tag->value());
		 */
		return $this->getValue();
	}
	function getRawValue(){
		return $this->value;
	}
	function raw(){
		/***
		 * $tag = new SimpleTag("tag","<![CDATA[ abc ]]>");
		 * eq("<![CDATA[ abc ]]>",$tag->raw());
		 */
		return $this->getRawValue();
	}
	function setValue($value){
		$this->value = (Variable::istype("SimpleTag",$value)) ? $value->get() : $value;
	}
	function addValue($value){
		/***
		 * $tag = new SimpleTag("tag","hoge");
		 * $a = new SimpleTag("a","hoge");
		 * $tag->addValue($a);
		 * eq('<tag>hoge<a>hoge</a></tag>',$tag->get());
		 * 
		 * $tag = new SimpleTag("tag","hoge");
		 * $tag->addValue("hoge");
		 * eq('<tag>hogehoge</tag>',$tag->get());
		 */
		$this->value .= (Variable::istype("SimpleTag",$value)) ? $value->get() : $value;
	}
	function getName(){
		return $this->name;
	}
	function setName($value){
		$this->name = trim($value);
	}
	function getPlain(){
		return $this->plain;
	}
	
	/**
	 * value内のtag.model.SimpleTagをハッシュにして取得
	 *
	 * @param tag.model.SimpleTag $tag
	 * @return array
	 */
	function toHash($israw=false){
		/***
		 * $tag = new SimpleTag("input","",array("type"=>"password"));
		 * eq(array("type"=>"password"),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("input","",array("type"=>"password","name"=>"hoge"));
		 * eq(array("type"=>"password","name"=>"hoge"),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("input",new SimpleTag("user","dummy"),array("type"=>"password"));
		 * eq(array("type"=>"password","user"=>"dummy"),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("input",array(new SimpleTag("user","dummy"),new SimpleTag("status",1)),array("type"=>"password"));
		 * eq(array("type"=>"password","user"=>"dummy","status"=>1),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("input",array(new SimpleTag("user","dummy"),new SimpleTag("status",new SimpleTag("default",123))),array("type"=>"password"));
		 * eq(array("type"=>"password","user"=>"dummy","status"=>array("default"=>123)),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("tag",<<< _XML_
		 * <word_list>
		 * 	<word>
		 * 		<abc>1</abc>
		 * 		<def>2</def>
		 *	</word>
		 * 	<word>
		 * 		<abc>3</abc>
		 * 		<def>4</def>
		 *	</word>
		 * </word_list>
		 * _XML_
		 * );
		 * eq(array(
		 * 	"word_list"=>array(
		 * 		"word"=>array(
		 * 			0=>array("abc"=>1,"def"=>2),
		 * 			1=>array("abc"=>3,"def"=>4)
		 * 		)
		 * 	)),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("tag","<hoge><data>1.23</data></hoge>");
		 * eq(array("hoge"=>array("data"=>1.23)),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("tag","<hoge><data>1</data><data>2</data><data>3</data></hoge>");
		 * eq(array("hoge"=>array("data"=>array(1,2,3))),$tag->toHash());
		 * 
		 * $tag = new SimpleTag("tag","hogehoge");
		 * eq("hogehoge",$tag->toHash());
		 * 
		 * 
		 * $tag = new SimpleTag("tag","<hoge name='fuga' class='funyo'>1.23</hoge>");
		 * eq(array(
		 * 		"_rawvalue_"=>"<hoge name='fuga' class='funyo'>1.23</hoge>",
		 * 		"hoge"=>array(
		 * 				"name"=>"fuga",
		 * 				"class"=>"funyo",
 		 * 				"_rawvalue_"=>1.23,
		 * 				)
		 * 		),$tag->toHash(true));
		 * 
		 * $xml = <<< __XML__
		 * <chat anonymity="1" date="1201934892" mail="184" no="30" thread="1201801480" user_id="BGqtOSY_nL_zF30qi9rBWduKB9A" vpos="12453">オナニーズ★プレイ</chat>
		 * __XML__;
		 * 
		 * $tag = new SimpleTag("tag",$xml);
		 * eq(array(
		 * 			"_rawvalue_"=>$xml,
		 * 			"chat"=>array("anonymity"=>1,
		 * 							"date"=>1201934892,
		 * 							"mail"=>184,
		 * 							"no"=>30,
		 * 							"thread"=>1201801480,
		 * 							"user_id"=>"BGqtOSY_nL_zF30qi9rBWduKB9A",
		 * 							"vpos"=>12453,
		 * 							"_rawvalue_"=>"オナニーズ★プレイ")),
		 * 			$tag->toHash(true));
		 * 
		 * $xml = <<<XML
		 * <sample>
		 * <tags>
		 * <tag>tag 1</tag>
		 * <tag>tag 2</tag>
		 * <tag>tag 3</tag>
		 * <tag>tag 4</tag>
		 * </tags>
		 * </sample>
		 * XML;
		 * if(SimpleTag::setof($tag,$xml,"sample")){
		 * 	eq(array("tags"=>array("tag"=>array("tag 1","tag 2","tag 3","tag 4"))),$tag->toHash());
		 * }
		 * 
		 * $xml = <<<XML
		 * <sample>
		 * <tags>
		 * 	<group>
		 * 		<tag>tag 1</tag>
		 * 		<tag>tag 2</tag>
		 *	</group>
		 * 	<group>
		 * 		<tag>tag 3</tag>
		 * 		<tag>tag 4</tag>
		 * 	</group>
		 * </tags>
		 * </sample>
		 * XML;
		 * if(SimpleTag::setof($tag,$xml,"sample")){
		 * 	eq(array("tags"=>array(
		 * 				"group"=>array(
		 * 						0=>array("tag"=>array("tag 1","tag 2")),
		 * 						1=>array("tag"=>array("tag 3","tag 4")),
		 * 				))
		 * 		),$tag->toHash());
		 * }
		 */
		$list = array();
		foreach($this->getParameter() as $id => $param){
			$paramvalue = $param->getValue();
			$paramvalue = is_string($paramvalue) ? (preg_match("/^[\d]+$/",$paramvalue) ? intval($paramvalue) : (preg_match("/^[\d\.]+$/",$paramvalue) ? floatval($paramvalue) : $paramvalue)) : $paramvalue;			
			$list[$id] = $paramvalue;
		}
		$src = $this->getValue();
		if($israw){
			$list["_rawvalue_"] = $this->getRawValue();
			$list["_rawvalue_"] = is_string($list["_rawvalue_"]) ? (preg_match("/^[\d]+$/",$list["_rawvalue_"]) ? intval($list["_rawvalue_"]) : (preg_match("/^[\d\.]+$/",$list["_rawvalue_"]) ? floatval($list["_rawvalue_"]) : $list["_rawvalue_"])) : $list["_rawvalue_"];		
		}
		while(SimpleTag::setof($ctag,$src)){
			$result = $ctag->toHash($israw);
			$result = is_string($result) ? (preg_match("/^[\d]+$/",$result) ? intval($result) : (preg_match("/^[\d\.]+$/",$result) ? floatval($result) : $result)) : $result;

			if(isset($list[$ctag->getName()])){
				if(!is_array($list[$ctag->getName()]) || ArrayUtil::ishash($list[$ctag->getName()])){
					$list[$ctag->getName()] = array($list[$ctag->getName()]);
				}
				$list[$ctag->getName()][] = $result;
			}else{
				$list[$ctag->getName()] = $result;
			}
			$src = substr($src,strpos($src,$ctag->getPlain())+strlen($ctag->getPlain()));
		}
		return (!empty($list)) ? $list : $src;
	}
	
	/**
	 * XMLテキストとして返す
	 *
	 * @static 
	 * @param string $value
	 * @return string
	 */
	function xmltext($value=""){
		/***
		 * eq("<![CDATA[\n<hoge>&1234;abc</hoge>\n]]>",SimpleTag::xmltext("<hoge>&1234;abc</hoge>"));
		 */
		if(is_string($value) && strpos($value,"<![CDATA[") === false && (preg_match("/<.+>/s",$value) || preg_match("/\&[^#\da-zA-Z]+;/i",$value))) $value = "<![CDATA[\n".$value."\n]]>";
		return $value;
	}

	/**
	 * CDATAを取り外す
	 *
	 * @static 
	 * @param string $value
	 * @return string
	 */
	function getCdata($value){
		/***
		 * $src = <<< __XML__
		 * <![CDATA[ rhaco tag ]]>
		 * <![CDATA[ cdata ]]>
		 * kaeru
		 * <![CDATA[ 
		 * 	hogehoge
		 * ]]>
		 * __XML__;
		 * 
		 * $result = " rhaco tag \n cdata \nkaeru\n \n\thogehoge\n"; 
		 * eq($result,SimpleTag::getCdata($src));
		 */
		if(preg_match_all("/<\!\[CDATA\[(.+?)\]\]>/ims",$value,$match)){
			foreach($match[1] as $key => $v){
				$value = str_replace($match[0][$key],$v,$value);
			}
		}
		return $value;
	}
	
	/**
	 * XMLコメントを除去する
	 *
	 * @static 
	 * @param unknown_type $src
	 * @return unknown
	 */
	function uncomment($src){
		/***
		 * $src = "<test><hoge><!-- commnet --></hoge></test>";
		 * eq("<test><hoge></hoge></test>",SimpleTag::uncomment($src));
		 * $src = "<test>\n<hoge>\n<!--\ncommnet\n-->\n</hoge>\n</test>";
		 * eq("<test>\n<hoge>\n\n</hoge>\n</test>",SimpleTag::uncomment($src));
		 * $src = "<test>\n<hoge>\n<!--\ncommnet\n-->\n</hoge>\n<hoge>\n<!--\ncommnet\n-->\n</hoge>\n</test>";
		 * eq("<test>\n<hoge>\n\n</hoge>\n<hoge>\n\n</hoge>\n</test>",SimpleTag::uncomment($src));
		 * 
		 */
		return preg_replace("/<!--.+?-->/s","",$src);
	}
	function getStart(){
		$parmater = "";
		$attribute = "";

		foreach($this->parameterList as $tagParameter){
			if(Variable::istype("SimpleTagParameter",$tagParameter)){
                $parmater .= sprintf(" %s=\"%s\"",$tagParameter->getName(),$tagParameter->getValue());
			}
		}
		foreach($this->attributeList as $tagAttribute){
			if($tagAttribute[1] === true) $attribute .= " ";
			$attribute .= $tagAttribute[0];
		}
		return sprintf((($this->getRawValue() != "") ? "<%s%s%s>" : "<%s%s%s />"),$this->getName(),$parmater,$attribute);
	}
	function getEnd(){
		return ($this->getRawValue() != "") ? ((empty($this->end)) ? sprintf("</%s>",$this->getName()) : $this->end) : "";
	}
	function setNormalization($bool){
		$this->normalization = Variable::bool($bool);
	}

	/**
	 * XMLとして標準出力に出力
	 *
	 * @param string $name
	 */
	function output($name=""){
		/*** unit("tag.model.SimpleTagTest"); */
		header(sprintf("Content-Type: application/xml%s",(empty($name) ? "" : sprintf("; name=%s",$name))));
		print($this->get(true));
		Rhaco::end();
	}
	function _save(&$tag,$routeSimpleTags,$value){
		krsort($routeSimpleTags);
		$ltag = $rtag = null;
		if(Variable::istype("SimpleTag",$value)) $value = $value->get();
		foreach($routeSimpleTags as $r){
			$ltag = Variable::copy($r);

			if($value === null){
				$value = "";
			}else{
				if(empty($rtag)){
					$r->setValue($value);
				}else{
					$r->setValue(str_replace($rtag->getPlain(),$value,$r->getRawValue()));
				}
				$value = $r->get();
			}
			$rtag = Variable::copy($ltag);
		}
		$tag->setValue(str_replace($ltag->getPlain(),$value,$tag->getRawValue()));
		return;
	}
	function _saveParameter(&$tag,$routeSimpleTags,$name,$value){
		krsort($routeSimpleTags);
		$ltag = $rtag = null;
		$f = false;
		$replace = "";

		foreach($routeSimpleTags as $r){
			$ltag = Variable::copy($r);

			if(!$f){
				if($value === null){
					$r->removeParameter($name);
				}else{
					$r->setParameter($name,$value);
				}
				$f = true;
			}
			if(!empty($rtag)) $r->setValue(str_replace($rtag->getPlain(),$replace,$r->getRawValue()));
			$replace = $r->get();
			$rtag = Variable::copy($ltag);
		}
		$tag->setValue(str_replace($ltag->getPlain(),$replace,$tag->getRawValue()));
		return;
	}
	function _saveAttribute(&$tag,$routeSimpleTags,$name,$value=""){
		krsort($routeSimpleTags);
		$ltag = $rtag = null;
		$f = false;
		$replace = "";
		foreach($routeSimpleTags as $r){
			$ltag = Variable::copy($r);

			if(!$f){
				if($value === null){
					$r->removeAttribute($name);
				}else{
					$r->setAttribute($name);
				}
				$f = true;
			}			
			if(!empty($rtag)) $r->setValue(str_replace($rtag->getPlain(),$replace,$r->getRawValue()));
			$replace = $r->get();
			$rtag = Variable::copy($ltag);
		}
		$tag->setValue(str_replace($ltag->getPlain(),$replace,$tag->getRawValue()));
		return;
	}
	function _parse(){
		if(trim($this->plain) != "" && $this->getName() != ""){
			$value = ($this->normalization) ? $this->_normalizationParameter($this->plain) : $this->plain;
			$name = preg_quote($this->getName());

			if(preg_match("/<(".$name.")([\s][^>]*?)>|<(".$name.")>/is",$value,$parse,PREG_OFFSET_CAPTURE)){
				$this->pos = $parse[0][1];
				$this->start = $parse[0][0];

				if(substr($parse[0][0],-2) == "/>"){
					$this->setName($parse[1][0]);
					$this->plain = $this->_normalizationUnescape($parse[0][0]);
					return $this->_parseParameterAttribute($parse[2][0]);
				}else{
					$balance = 0;
					if(preg_match_all("/<[\/]{0,1}".$name."[\s][^>]*[^\/]>|<[\/]{0,1}".$name."[\s]*>/is",$value,$list,PREG_OFFSET_CAPTURE,$this->pos)){					
						foreach($list[0] as $arg){
							if(($balance += (($arg[0][1] == "/") ? -1 : 1)) <= 0 && preg_match("/^(<[\s]*(".$name.")([\s]*[^>]*)>)(.*)(<[\s]*\/\\2[\s]*>)$/is",substr($value,$this->pos,($arg[1] + strlen($arg[0]) - $this->pos)),$match)){
								$this->end = $arg[0];
								$this->setName($match[2]);
								$this->setValue($this->_normalizationUnescape($match[4]));
								$this->plain = $this->_normalizationUnescape($match[0]);
								unset($list);
								return $this->_parseParameterAttribute($match[3]);
							}
						}
						unset($list);
					}
					$this->setName($parse[1][0]);
					$this->plain = $this->_normalizationUnescape($parse[0][0]);
					return $this->_parseParameterAttribute($parse[2][0]);
				}
			}
		}
		return false;
	}
	function _normalizationParameter($src){
		return StringUtil::replace(str_replace(array("\\\'","\\\""),array("__QUOTE__","__DQUOTE__"),$src),"/([\"\']).*?[<>].*?\\1/","str_replace(array('<','>'),array('__TAGSTART__','__TAGEND__'),'\\0')","",true);
	}
	function _normalizationUnescape($src){
		return ($this->normalization) ? str_replace(array("__QUOTE__","__DQUOTE__","__TAGEND__","__TAGSTART__"),array("\\\'","\\\"",">","<"),$src) : $src;
	}
	function _parseParameterAttribute($src){
		$parameter = array();
		$attribute = array();

		if(!empty($src)){
			if(preg_match_all("/[\s]+([\w\-\:]+)[\s]*=[\s]*([\"\'])([^\\2]*?)\\2/ms",$src,$parameter)){
				foreach($parameter[0] as $id => $value){
					$this->setParameter($parameter[1][$id],$this->_normalizationUnescape($parameter[3][$id]));
					$src = str_replace($value,"",$src);
				}
			}
			if(preg_match_all("/([\w\-]+)/",$src,$attribute)){
				foreach($attribute[1] as $value){
					$this->setAttribute($this->_normalizationUnescape($value));
				}
			}
		}
		return true;
	}
	/**
	 * 指定のタグに閉じタグを追加する
	 *
	 * @static 
	 * @param string $src
	 * @param string $name
	 * @return string
	 */
	function xhtmlnize($src,$name){
		/***
		 * eq("<img src='hoge' />",SimpleTag::xhtmlnize("<img src='hoge'>","img"));
		 * eq("<img src='hoge' />",SimpleTag::xhtmlnize("<img src='hoge' />","img"));
		 */
		if(preg_match_all(sprintf("/<%s(.+?)>/is",$name),$src,$link)){
			foreach($link[0] as $value){
				if(substr($value,-2) != "/>"){
					$src = str_replace($value,substr($value,0,-1)." />",$src);
				}
			}
		}
		return $src;
	}
}
?>