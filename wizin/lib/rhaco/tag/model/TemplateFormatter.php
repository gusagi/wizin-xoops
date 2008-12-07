<?php
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("lang.DateUtil");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.Env");
/**
 * tag.TemplateParserで利用するフォーマッタ
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class TemplateFormatter{

	/**
	 * HTML表現を返す
	 *
	 * @param string $value
	 * @param int $letgth
	 * @param int $lines
	 * @return string
	 */
	function text2html($value,$length=0,$lines=0){
		/***
		 * 
		 * eq("&lt;hoge&gt;hoge&lt;/hoge&gt;<br />\n&lt;hoge&gt;hoge&lt;/hoge&gt;",TemplateFormatter::text2html("<hoge>hoge</hoge>\n<hoge>hoge</hoge>"));
		 * eq("aaa<br />\nb",TemplateFormatter::text2html("aaa\nbbb\nccc",5));
		 * eq("aaa<br />\nbbb",TemplateFormatter::text2html("aaa\nbbb\nccc",0,2));
		 * eq("aaa<br />\nb",TemplateFormatter::text2html("aaa\nbbb\nccc",5,2));
		 */
		$value = SimpleTag::getCdata($value);
		if($length > 0) $value = StringUtil::substring($value,0,$length);
		if($lines > 0) $value = implode("\n",ArrayUtil::arrays(explode("\n",$value),0,$lines));
		return TemplateFormatter::nl2br(TemplateFormatter::escape($value));
	}
	
	/**
	 * HTMLエスケープを行う
	 *
	 * @param string $value
	 * @return string
	 */
	function escape($value){
		/***
		 * eq("&lt;hoge&gt;hoge&lt;/hoge&gt;\n&lt;hoge&gt;hoge&lt;/hoge&gt;",TemplateFormatter::escape("<hoge>hoge</hoge>\n<hoge>hoge</hoge>"));
		 */
		return (is_array($value) || is_object($value)) ? "" : str_replace(array("<",">","'","\""),array("&lt;","&gt;","&#039;","&quot;"),$value);		
	}
	
	/**
	 * HTMLエスケープされた文字を戻す
	 *
	 * @param string $value
	 * @return string
	 */
	function unescape($value){
		/***
		 * eq("<hoge>hoge</hoge>\n<hoge>hoge</hoge>",TemplateFormatter::unescape("&lt;hoge&gt;hoge&lt;/hoge&gt;\n&lt;hoge&gt;hoge&lt;/hoge&gt;"));
		 */
		return (is_array($value) || is_object($value)) ? "" : str_replace(array("&lt;","&gt;","&#039;","&quot;"),array("<",">","'","\""),$value);		
	}
	
	/**
	 * HTMLエンコードした文字列を返す
	 *
	 * @param string $value
	 * @return string
	 */
	function htmlencode($value){
		/***
		 * eq("ほげほげ",TemplateFormatter::htmlencode("ほげほげ"));
		 * eq("&lt;hoge&gt;hogehoge&lt;/hoge&gt;",TemplateFormatter::htmlencode("<hoge>hogehoge</hoge>"));
		 */
		return (is_array($value) || is_object($value)) ? "" : htmlentities(StringUtil::encode($value,StringUtil::UTF8()),ENT_QUOTES,StringUtil::UTF8());
	}
	
	/**
	 * HTMLデコードした文字列を返す
	 *
	 * @param string $value
	 * @return string
	 */
	function htmldecode($value){
		/***
		 * eq("ほげほげ",TemplateFormatter::htmldecode("&#12411;&#12370;&#12411;&#12370;"));
		 * eq("&gt;&lt;ほげ& ほげ",TemplateFormatter::htmldecode("&amp;gt;&amp;lt;&#12411;&#12370;&amp; &#12411;&#12370;"));
		 */
		if(!empty($value) && is_string($value)){
			$map = array(0x0,0x10000,0,0xfffff);
			$value = StringUtil::encode($value,StringUtil::UTF8());
			$value = StringUtil::replace($value,"/&#[xX]([0-9a-fA-F]+);/","'&#'.hexdec('\\1').';'","",true);
			$value = (extension_loaded("mbstring")) ? mb_decode_numericentity($value,$map,StringUtil::UTF8()) : $value;
			$value = (Env::isphp(5)) ?
									html_entity_decode($value,ENT_QUOTES,StringUtil::UTF8()) :
									strtr(StringUtil::replace($value,"/&#([0-9]+);/","chr('\\1')","",true),array_flip(get_html_translation_table(HTML_ENTITIES)));
		}
		return $value;
	}
	
	/**
	 * XMLエコードした文字列を返す
	 *
	 * @param string $value
	 * @return string
	 */
	function xmlencode($value){
		/***
		 * eq("&#060;hoge&#062;",TemplateFormatter::xmlencode("<hoge>"));
		 */
		return (is_array($value) || is_object($value)) ? "" : str_replace(array("&","<",">","'","\""),array("&#038;","&#060;","&#062;","&#039;","&#034;"),$value);		
	}
	
	/**
	 * 改行を<br />に変換する
	 *
	 * @param string $value
	 * @return string
	 */
	function nl2br($value){
		/***
		 * eq("hoge<br />\nhoge",TemplateFormatter::nl2br("hoge\nhoge"));
		 */
		return (is_array($value) || is_object($value)) ? "" : nl2br(StringUtil::toULD($value));
	}
	
	/**
	 * URLエンコードを行う
	 *
	 * @param string $value
	 * @return string
	 */
	function urlencode($value){
		/***
		 * eq("%E3%81%BB%E3%81%92",TemplateFormatter::urlencode("ほげ"));
		 */
		return (is_array($value) || is_object($value)) ? "" : rawurlencode($value);
	}
	
	/**
	 * JavaScript文字列表現時に困りそうな文字をエスケープする
	 *
	 * @param string $value
	 * @return string
	 */
	function getJsDocument($value){
		/***
		 * eq("document.write(\\'hoge\\rhoge\\n\')",TemplateFormatter::getJsDocument("document.write('hoge\rhoge\n')"));
		 */
		if(!is_array($value) && !is_object($value)){
			$value = preg_replace("/([\"\'])/",("\\\\"."\\1"),$value);
			$value = str_replace("\n","\\n",$value);
			$value = str_replace("\r","\\r",$value);
			
			return $value;
		}
		return "";
	}
	
	/**
	 * get queryを生成する
	 *
	 * @return string
	 */
	function httpBuildQuery(){
		/***
		 * unit("tag.model.TemplateFormatterTest");
		 * 
		 * $list = array("hoge"=>123,"rhaco"=>"lib");
		 * eq("hoge=123&rhaco=lib",TemplateFormatter::httpBuildQuery($list));
		 * 
		 * $list = array(123,456,789);
		 * eq("0=123&1=456&2=789",TemplateFormatter::httpBuildQuery($list));
		 * 
		 * $list = array('foo'=>1,'bar'=>null,'baz'=>'test');
		 * eq('foo=1&baz=test', TemplateFormatter::httpBuildQuery($list));
		 * 
		 */
		$list = array();
		foreach(func_get_args() as $arg){
			foreach(ArrayUtil::arrays($arg) as $key => $value){
				if($key !== "pathinfo"){
					$list[] = preg_replace("/^(.+)&$/","\\1",Variable::toHttpQuery($value,$key,false));
				}
			}
		}
		return empty($list) ? "" : preg_replace("/&[&]+/","&",implode("&",$list));
	}
	
	/**
	 * 文字列の一部を返す
	 *
	 * @param string $value
	 * @param int $limit
	 * @param int $offset
	 * @return string
	 */
	function substring($value,$limit,$offset=0){
		/***
		 * eq("012...",TemplateFormatter::substring("0123456798",3));
		 * eq("345...",TemplateFormatter::substring("0123456798",3,3));
		 */
		$sep = "";
		if(StringUtil::strlen($value) > ($limit - $offset)) $sep = "...";
		return StringUtil::substring($value,$offset,$limit).$sep;
	}
	
	/**
	 * 文字列分割を行う
	 *
	 * @param string $value
	 * @param string $separator
	 * @param int $num
	 * @return string
	 */
	function subexplode($value,$separator,$num=0){
		/***
		 * eq("012",TemplateFormatter::subexplode("012,345,678",","));
		 * eq("345",TemplateFormatter::subexplode("012,345,678",",",1));
		 */
		$list = explode($separator,$value);
		if(sizeof($list) > $num) return $list[$num];
		return "";
	}
	
	/**
	 * 配列を結合する
	 *
	 * @param array $list
	 * @param string $sep
	 * @return string
	 */
	function implode($list,$sep="/"){
		/***
		 * $array = array(123,456,789);
		 * eq("123,456,789",TemplateFormatter::implode($array,","));
		 * 
		 */
		return implode($sep,ArrayUtil::arrays($list));
	}
	
	/**
	 * 正規表現をquoteする
	 *
	 * @param string $value
	 * @return string
	 */
	function pregquote($value){
		/*** eq(preg_quote("/[\w]/"),TemplateFormatter::pregquote("/[\w]/")); */
		return preg_quote($value);
	}
	
	/**
	 * 偶数なら even 奇数なら　oddを返す
	 *
	 * @param unknown_type $number
	 * @return unknown
	 */
	function evenodd($number){
		/***
		 * eq("even",TemplateFormatter::evenodd(2));
		 * eq("odd",TemplateFormatter::evenodd(1));
		 */
		return (($number % 2) == 0) ? "even" : "odd";
	}
	
	/**
	 * ページングに利用するqueryを返す
	 *
	 * @param integer $number
	 * @param string $string
	 * @return string
	 */
	function pagingQuery($number,$string=""){
		/***
		 * eq("hoge=1&abc=abc&page=2",TemplateFormatter::pagingQuery(2,"hoge=1&page=1&abc=abc&"));
		 */
		$string = preg_replace("/([^\w]{0,1})page=[\d]*/","\\1",$string);	
		if(!empty($string)) $string	= (substr($string,-1) != "&") ? $string."&" : $string;
		return preg_replace("/[\&]+/","&",$string."page=".$number);
	}
	
	/**
	 * CDATAの中身を返す
	 *
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
		 * eq($result,TemplateFormatter::getCdata($src));
		 */
		return SimpleTag::getCdata($value);
	}
	
	/**
	 * XMLから中身を取り出す.
	 *
	 * @param string $value
	 * @return string
	 */
	function xml2html($value){
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
		 * eq($result,TemplateFormatter::getCdata($src));
		 * 
		 * eq("ほげほげ",TemplateFormatter::xml2html("&#12411;&#12370;&#12411;&#12370;"));
		 * eq("&gt;&lt;ほげ& ほげ",TemplateFormatter::xml2html("&amp;gt;&amp;lt;&#12411;&#12370;&amp; &#12411;&#12370;"));
		 */
		return TemplateFormatter::htmldecode(TemplateFormatter::getCdata($value));
	}
	
	/**
	 * 日付をフォーマットした文字列を返す
	 *
	 * @param int $date
	 * @param string $format
	 * @return string
	 */
	function dateformat($date,$format="Y/m/d H:i:s"){
		/***
		 * eq("2007/07/19",TemplateFormatter::dateformat("2007-07-18T16:16:31+00:00","Y/m/d"));
		 */
		return DateUtil::format($date,$format);
	}
	
	/**
	 * 文字列を指定の長さ繰り返した文字列をつなげて取得
	 *
	 * @param int $multiplier
	 * @param string $input_str
	 * @param string $str
	 * @return string
	 */
	function repeat($multiplier,$input_str=" ",$str=""){
		/***
		 * eq("ZYABABABABABABABABAB",TemplateFormatter::repeat(20,"AB","ZY"));
		 * eq("ZYABABABABABABABABABA",TemplateFormatter::repeat(21,"AB","ZY"));
		 */
		return StringUtil::repeat($multiplier,$input_str,$str);
	}
	
	/**
	 * pathっぴく連結した文字列を返す
	 *
	 * @param string ......
	 * @return string
	 */
	function path(){
		/***
		 * eq("http://www.rhaco.org/doc/test/hoge",TemplateFormatter::path("http://www.rhaco.org/doc/","/test","hoge"));
		 */
		$args = func_get_args();
		$path = "";
		
		if(!empty($args)){
			$path = array_shift($args);

			foreach($args as $arg){
				if(substr($arg,0,1) != "/") $arg = "/".$arg;
				if(substr($path,-1) == "/") $path = substr($path,0,-1);
				$path .= $arg;
			}
		}
		return $path;
	}
	
	/**
	 * 何もしない
	 *
	 * @param unknown_type $value
	 * @return unknown_type
	 */
	function noop($value){
		/*** eq("hoge",TemplateFormatter::noop("hoge")); */
		return $value;
	}
	

	/**
	 * URLをリンクにする
	 *
	 * @param string $src
	 * @return string
	 */
	function toA($src){
		/***
		 * eq("rhacoのurlは<a href=\"http://www.rhaco.org\">http://www.rhaco.org</a>です",TemplateFormatter::toA("rhacoのurlはhttp://www.rhaco.orgです"));
		 */
		return preg_replace("/(http:\/\/[\w\.\/\?&\-\:=%~]+)/i","<a href=\"\\1\">\\1</a>",$src);
	}
	
	/**
	 * タグを除去する
	 *
	 * @param string $src
	 * @return string
	 */
	function striptags($src){
		/***
		 * eq("rhaco",TemplateFormatter::striptags('<a href="http://rhaco.org">rhaco</a>'));
		 */
		return strip_tags($src);
	}
	
	/**
	 * 小文字にする
	 *
	 * @param string $string
	 * @return string
	 */
	function lower($string){
		/***
		 * eq("abc",TemplateFormatter::lower("AbC"));
		 */
		return strtolower($string);
	}
}
?>