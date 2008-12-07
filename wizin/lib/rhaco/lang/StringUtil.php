<?php
/**
 * 文字列を扱うユーティリティ
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class StringUtil{
	/**
	 * エンコード用の文字列を取得
	 *
	 * @return string
	 */
	function UTF8(){
		/*** eq("UTF-8",StringUtil::UTF8()); */
		return "UTF-8";
	}
	/**
	 * エンコード用の文字列を取得
	 *
	 * @return string
	 */
	function SJIS(){
		/*** eq("SJIS",StringUtil::SJIS()); */
		return "SJIS";
	}
	/**
	 * エンコード用の文字列を取得
	 *
	 * @return string
	 */
	function EUC(){
		/*** eq("EUC-JP",StringUtil::EUC()); */
		return "EUC-JP";
	}
	/**
	 * エンコード用の文字列を取得
	 *
	 * @return string
	 */
	function JIS(){
		/*** eq("JIS",StringUtil::JIS()); */
		return "JIS";
	}
	
	/**
	 * エンコード用の文字列を取得
	 *
	 * @return string
	 */
	function ASCII(){
		/*** eq("ASCII",StringUtil::ASCII()); */		
		return "ASCII";
	}
	
	/**
	 * エンコードした文字列を取得
	 *
	 * @param string $value
	 * @param string $encodeType
	 * @param string $lang
	 * @return string
	 */
	function encode($value,$encodeType="",$lang="Japanese"){
		if(is_array($value) || is_object($value)) return null;
		if(extension_loaded("mbstring")){
			if(!empty($value)){
				if(empty($encodeType)) $encodeType = StringUtil::UTF8();
				if(empty($lang) || "neutral" == mb_language()) $lang = "Japanese";
				@mb_language("Japanese");
				return @mb_convert_encoding($value,$encodeType,StringUtil::detectEncoding($value));
			}
		}
		return $value;
		
	}
	
	/**
	 * 文字列からエンコード文字列を取得
	 *
	 * @param string $value
	 * @return string
	 */
	function detectEncoding($value){
		$enc = Rhaco::getVariable("STRING_INTERNAL_CODE","detect");
		if($enc == "detect" && extension_loaded("mbstring")){
			if(is_array($value) || is_object($value)) return mb_internal_encoding();
			return mb_detect_encoding($value);
		}
		return $enc;
	}
	
	function setFromEncoding($enc){
		Rhaco::setVariable("STRING_INTERNAL_CODE",$enc);
	}
	
	/**
	 * 半角カナを全角カナに変換した文字列を取得
	 *
	 * @param string $value
	 * @return string
	 */
	function convertKana($value){
		/*** eq("123abcアイウエオ",StringUtil::convertKana("123abcｱｲｳｴｵ")); */
		if(is_array($value) || is_object($value)) return null;
		if(extension_loaded("mbstring")){
			return mb_convert_kana($value,"KV",StringUtil::detectEncoding($value));
		}
		return $value;
	}
	
	/**
	 * 全角を半角に変換した文字列を取得
	 *
	 * @param string $value
	 * @return string
	 */
	function convertZenhan($value){
		/*** eq("123123abcabcｱｲｳｴｵ",StringUtil::convertZenhan("123１２３abcａｂｃｱｲｳｴｵ")); */
		if(is_array($value) || is_object($value)) return null;
		if(extension_loaded("mbstring")){
			$value = mb_convert_kana($value,"askV",StringUtil::detectEncoding($value));
		}
		return $value;
	}
	
	/**
	 * 半角を全角に返還した文字列を取得
	 *
	 * @param string $value
	 * @return string
	 */
	function convertHanzen($value){
		/***
		 * eq("１２３１２３ａｂｃａｂｃアイウエオ",StringUtil::convertHanzen("123１２３abcａｂｃｱｲｳｴｵ"));
		 * eq("グループ",StringUtil::convertHanzen("ｸﾞﾙｰﾌﾟ"));
		 */
		if(is_array($value) || is_object($value)) return null;
		if(extension_loaded("mbstring")){
			$value = mb_convert_kana($value,"ASKV",StringUtil::detectEncoding($value));
		}
		return $value;		
	}
	
	/**
	 * 文字列を指定の長さ繰り返した文字列をつなげて取得
	 *
	 * @param int $multiplier
	 * @param string $input_str
	 * @param string $str
	 * @return string
	 */
	function repeat($length,$input_str=" ",$str=""){
		/***
		 * eq("ZYABABABABABABABABAB",StringUtil::repeat(20,"AB","ZY"));
		 * eq("ZYABABABABABABABABABA",StringUtil::repeat(21,"AB","ZY"));
		 */
		return StringUtil::substring($str.str_repeat($input_str,($length-StringUtil::strlen($str))/StringUtil::strlen($input_str)+1),0,$length);
	}
	
	/**
	 * 文字列を置換する
	 *
	 * @param string $src
	 * @param string $preg
	 * @param string $replace
	 * @param string $option imsxADSUX
	 * @return string
	 */
	function replace($src,$preg,$replace="",$option="",$eval=false){
		/***
			eq("123１２３xyzxyzｱｲｳｴｵ",StringUtil::replace("123１２３abcａｂｃｱｲｳｴｵ","abcａｂｃ","xyzxyz"));
			eq("123１２３abcａｂｃｱｲｳｴｵ",StringUtil::replace("123１２３abcａｂｃｱｲｳｴｵ","abcabc","xyzxyz"));		
		*/
		if(!empty($preg)){
			if($eval) $option .= "e";
			if(substr($preg,0,1) == "/"){
				if(extension_loaded("mbstring") && !$eval){
					if(strpos($option,"i") === false){
						return mb_ereg_replace(substr($preg,1,-1),$replace,$src);						
					}
					return mb_eregi_replace(substr($preg,1,-1),$replace,$src);
				}
				$src = preg_replace(sprintf("%s%s",$preg,$option."u"),$replace,StringUtil::encode($src,StringUtil::UTF8()));
				if(strpos($option,"e") !== false) $src = str_replace(array("\\\"","\\'","\\\\"),array("\"","\'","\\"),$src);
				return $src;
			}
			return str_replace($preg,$replace,$src);
		}
		return $src;
	}
	
	/**
	 * 文字列の数を取得する
	 *
	 * @param string $value
	 * @return int
	 */
	function strlen($value){
		/*** eq(17,StringUtil::strlen("123１２３abcａｂｃｱｲｳｴｵ")); */
		if(!empty($value)){
			if(extension_loaded("mbstring")){
				return mb_strlen($value,StringUtil::detectEncoding($value));
			}
			return strlen($value);
		}
		return 0;
	}
	
	/**
	 * 文字列を抜き出す
	 *
	 * @param string $value
	 * @param int $start
	 * @param int $length
	 * @return string
	 */
	function substring($value,$start,$length=""){
		/*** eq("１２３a",StringUtil::substring("123１２３abcａｂｃｱｲｳｴｵ",3,4)); */		
		if(extension_loaded("mbstring")){
			if($length == ""){
				$length = StringUtil::strlen($value);
			}
			return mb_substr($value,$start,$length,StringUtil::detectEncoding($value));
		}
		return substr($value,$start,$length);
	}
	
	/**
	 * 指定の長さで分割する
	 *
	 * @param string $str
	 * @param int $length
	 * @return string
	 */
	function strsplit($str,$length=1){
		/***
			$list = StringUtil::strsplit("abcdefghi",2);
			eq(5,sizeof($list));
			eq("ab",$list[0]);
			eq("cd",$list[1]);
			eq("ef",$list[2]);
			eq("gh",$list[3]);
			eq("i",$list[4]);
	
			$list = StringUtil::strsplit("abcdefghi");
			eq(9,sizeof($list));
			
			$list = StringUtil::strsplit("abcあeいghi",2);
			eq(5,sizeof($list));
			eq("ab",$list[0]);
			eq("cあ",$list[1]);
			eq("eい",$list[2]);
			eq("gh",$list[3]);
			eq("i",$list[4]);
		*/
		if($str != ""){
			$length = (empty($length)) ? 1 : $length;
			$list	= array();
			$max	= StringUtil::strlen($str);
			$count	= 0;

			while($max > $count){
				$list[] = StringUtil::substring($str,$count,$length);
				$count += $length;
			}
			return $list;
		}
		return array();
	}	

	/**
	 * フォーマットされた文字列を取得
	 *
	 * @return string
	 */
	function sprintf(){
		/*** eq("123１２３abcａｂｃｱｲｳｴｵ",StringUtil::sprintf("123１２３%s%sｱｲｳｴｵ","abc","ａｂｃ")); */
		$argList		= func_get_args();
		$argsize		= sizeof($argList);

		if($argsize > 0){
			$paramList		= array();
			$paramsize		= substr_count($argList[0],"%");
			$propertiesize	= $argsize - 1;

			for($i=1;$i<$argsize;$i++){
				$paramList[] = $argList[$i];
			}			
			if($paramsize > $propertiesize){
				for($i=0;$i<$paramsize-$propertiesize;$i++){
					$paramList[] = "";
				}
			}
			return vsprintf($argList[0],$paramList);
		}
		return "";
	}
	/**
	 * 空かどうか
	 *
	 * @param string $val
	 * @return boolean
	 */
	function isBlank($val){
		/***
			assert(StringUtil::isBlank(""));
			assert(!StringUtil::isBlank(0));
			assert(StringUtil::isBlank(null));
			assert(!StringUtil::isBlank("A"));
			assert(!StringUtil::isBlank(array(1,2,3)));
		*/
		return ($val !== 0 && (is_null($val) || (is_string($val) && empty($val))));
	}
	/**
	 * 改行コードをUnix Line Delimiter(\n)に変換する
	 */
	function toULD($src){
		/***
		 * eq("a\nb\nc\n",StringUtil::toULD("a\r\nb\rc\n"));
		 */
		return str_replace(array("\r\n","\r"),"\n",$src);
	}
	
	/**
	 * 文字数から行数を取得する
	 */
	function len2line($src,$length=-1,$offset=0){
		/***
		 * eq(3,StringUtil::len2line("a\r\nb\rc\nd\ne\r",7));
		 * eq(2,StringUtil::len2line("a\r\nb\rc\nd\ne\r",5,3));
		 */
		$src = ($length > 0) ? StringUtil::substring($src,$offset,$length) : substr($src,$offset);
		return substr_count(StringUtil::toULD($src),"\n");
	}
	
	
	
	
	
	/**
	 * magic_quotesを解除した文字列を取得
	 *
	 * @param string $value
	 * @return string
	 */
	function getMagicQuotesOffValue($value){
		return (is_string($value) && get_magic_quotes_gpc()) ? stripslashes($value) : $value;
	}
	/**
	 * magic_quotesをした文字列を取得
	 *
	 * @param string $value
	 * @return string
	 */
	function getMagicQuotesOnValue($value){
		return (!get_magic_quotes_gpc()) ? addslashes($value) : $value;
	}

	/**
	 * コメントブロックを抜き出す
	 */
	function comments($value){
		/***
		 * $ast = "*";
		 * $comment = sprintf("/%s%s hogehoge %s/",$ast,$ast,$ast);
		 * eq("hogehoge",StringUtil::comments($comment));
		 * 
		 */
		$description = "";
		if(preg_match_all("/\/[\*]+(.+?)\*\//s",$value,$comments)){
			foreach($comments[1] as $c){
				foreach(explode("\n",$c) as $line){
					$description .= preg_replace("/^[\s]*\*[\s]{0,1}/","",$line)."\n";
				}
			}
		}
		return trim($description);
	}
	function isInt($value){
		/***
		 * assert(StringUtil::isInt("123"));
		 * assert(StringUtil::isInt("-123"));
		 * assert(!StringUtil::isInt("123.123"));
		 * assert(!StringUtil::isInt("ABC"));
		 * 
		 */
		return (preg_match("/^[-]{0,1}[\d]+$/",$value) && true);
	}
	function isFloat($value){
		/***
		 * assert(StringUtil::isFloat("123"));
		 * assert(StringUtil::isFloat("-123"));
		 * assert(StringUtil::isFloat("123.123"));
		 * assert(StringUtil::isFloat("-123.123"));
		 * assert(!StringUtil::isFloat("1.4.1"));
		 * assert(!StringUtil::isFloat("1..1"));
		 * assert(!StringUtil::isFloat("ABC"));
		 * assert(StringUtil::isFloat("+1e2"));
		 * assert(StringUtil::isFloat("1e-2"));
		 * assert(!StringUtil::isFloat("1a-2"));
		 * 
		 */
		return (preg_match("/^[+-]?\d+(?:(?:\.\d+)?(?:e[+-]?\d+)?)?$/i",$value) && 2 > substr_count(str_replace("..",". .",$value),"."));
	}
	
	/**
	 * クラス名に適した名前に変換する
	 *
	 * @param string $name
	 * @param boolean $lower
	 * @return string
	 */
	function regularizedName($name,$lower=false){
		/***
		 * eq("AaaBbb",StringUtil::regularizedName("aaa_bbb"));
		 * eq("aaaBbb",StringUtil::regularizedName("aaa_bbb",true));
		 * 
		 * eq("AaaBbb",StringUtil::regularizedName("aaaBbb"));
		 * eq("aaaBbb",StringUtil::regularizedName("aaaBbb",true));
		 * 
		 * eq("AaaBbb",StringUtil::regularizedName("AAA_BBB"));
		 * eq("aaaBbb",StringUtil::regularizedName("AAA_BBB",true));
		 * 
		 * eq("AAABB",StringUtil::regularizedName("aAABB"));
		 * eq("aAABB",StringUtil::regularizedName("aAABB",true));
		 * 
		 * eq("AaaBbb",StringUtil::regularizedName("AAA_bbb"));
		 * eq("aaaBbb",StringUtil::regularizedName("AAA_bbb",true));
		 */
		$result = "";
		if(strpos($name,"_") !== false || preg_match("/^[a-z0-9]+$/",$name) || preg_match("/^[A-Z0-9]+$/",$name)){
			foreach(explode("_",$name) as $str) $result = $result.trim(ucwords(strtolower($str)));
		}else{
			$result = $name;
		}
		return ($lower) ? (strtolower(substr($result,0,1)).substr($result,1)) : ucwords($result);
	}
	
	/**
	 * 指定の開始文字／終了文字でくくられた部分を取得
	 * ブロックの中身,ブロックの開始位置,ブロックの終了位置を返す
	 *
	 * @param string $src
	 * @param string $start
	 * @param string $end
	 * @return array(string,int,int)
	 */
	function block($src,$start,$end){
		/***
		 * $src = "xyz[abc[def]efg]hij";
		 * $rtn = StringUtil::block($src,"[","]");
		 * eq(array("abc[def]efg",3,16),$rtn);
		 * eq("[abc[def]efg]",substr($src,$rtn[1],$rtn[2] - $rtn[1]));
		 * 
		 * $src = "[abc[def]efg]hij";
		 * eq(array("abc[def]efg",0,13),StringUtil::block($src,"[","]"));
		 * 
		 * $src = "[abc[def]efghij";
		 * eq(array(null,0,15),StringUtil::block($src,"[","]"));
		 * 
		 * $src = "[abc/def/efghij";
		 * eq(array("def",4,9),StringUtil::block($src,"/","/"));
		 * 
		 * $src = "[abc|def|efghij";
		 * eq(array("def",4,9),StringUtil::block($src,"|","|"));
		 * 
		 * $src = "[abc<abc>def</abc>efghij";
		 * eq(array("def",4,18),StringUtil::block($src,"<abc>","</abc>"));
		 * 
		 * $src = "[abc<abc>def<abc>efghij";
		 * eq(array("def",4,17),StringUtil::block($src,"<abc>","<abc>"));
		 * 
		 * $src = "[<abc>abc<abc>def</abc>efg</abc>hij";
		 * $rtn = StringUtil::block($src,"<abc>","</abc>");
		 * eq(array("abc<abc>def</abc>efg",1,32),$rtn);
		 * eq("<abc>abc<abc>def</abc>efg</abc>",substr($src,$rtn[1],$rtn[2] - $rtn[1]));
		 */
		$eq = ($start == $end);
		if(preg_match_all("/".(($end == null || $eq) ? preg_quote($start,"/") : "(".preg_quote($start,"/").")|(".preg_quote($end,"/").")")."/sm",$src,$match,PREG_OFFSET_CAPTURE)){
			$count = 0;
			$pos = null;

			foreach($match[0] as $key => $value){
				if($value[0] == $start){
					$count++;
					if($pos === null) $pos = $value[1];
				}else if($pos !== null){
					$count--;
				}
				if($count == 0 || ($eq && ($count % 2 == 0))) return array(substr($src,$pos + strlen($start),($value[1] - $pos - strlen($start))),$pos,$value[1] + strlen($end));
			}
		}
		return array(null,0,strlen($src));
	}
}
?>