<?php
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.Env");
Rhaco::import("tag.model.SimpleTag");
/**
 * 変数を扱うユーティリティ
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Variable{
	/**
	 * booleanとして取得する
	 * true = true/"true"/1
	 * false = false/"false"/true以外
	 *
	 * @param unknown_type $value
	 * @return boolean or string
	 */
	function bool($value=false,$to_str=false){
		/***
		 * 
		    assert(Variable::bool("true"));
			assert(Variable::bool(true));		
			assert(Variable::bool(1));
			assert(!Variable::bool("false"));
			assert(!Variable::bool(false));
			assert(!Variable::bool(-1));
			assert(!Variable::bool(0));
			assert(!Variable::bool(""));
			assert(!Variable::bool("ABC"));
		 */		
		$bool = false;
		if(is_string($value) && preg_match("/^true$/i",$value))	$bool = true;
		if(intval($value) > 0) $bool = true;
		if($to_str) return ($bool) ? "true" : "false";
		return $bool;
	}
	
	/**
	 * 継続的な真偽値を得る
	 *
	 * @param unknown_type $varible
	 * @param boolean $base
	 */
	function is($varible,$base=true){
		/***
		 * $bool = true;
		 * eq(true,Variable::is($bool,true));
		 * eq(false,Variable::is($bool,false));
		 * 
		 * $bool = false;
		 * eq(false,Variable::is($bool,true));
		 * eq(false,Variable::is($bool,false));
		 * 
		 */
		return ($base && Variable::bool($varible));
	}

	/**
	 * 数字として取得
	 *
	 * @param unknown_type $value
	 * @return int
	 */	
	function int($value){
		/***
			eq(123,Variable::int("123"));
			eq(0,Variable::int("ＡＢＣ"));
			if(extension_loaded("mbstring")){
				eq(123,Variable::int("１２３"));
			}
		 */
		return intval(StringUtil::convertZenhan($value));
	}

	/**
	 * インスタンスをコピーする
	 *
	 * @param object $variable
	 * @return object
	 */
	function copy($variable){
		/*** unit("lang.VariableTest"); */
		if(is_object($variable) && Env::isphp(5)){
			return clone($variable);
		}
		return $variable;
	}
	
	/**
	 * 変数の型が一致するか
	 *
	 * @param string/object $type
	 * @param object $var
	 * @return boolean
	 */
	function istype($type,$var){
		/***
		 * assert(!Variable::istype("Rhaco",""));
		 * assert(!Variable::istype("Rhaco",null));
		 * assert(!Variable::istype("Rhaco",false));
		 * assert(!Variable::istype("Rhaco",true));
		 */
		$type = strtolower(is_object($type) ? get_class($type) : $type);
		if(is_object($var)){
			if(Rhaco::istype($type,$var)) return true;
			return ($type == strtolower(Rhaco::get_class($var)) || is_subclass_of($var,$type));
		}
		return strtolower(gettype($var)) == $type;
	}

	/**
	 * Jsonに変換して取得
	 *
	 * @param unknown_type $variable
	 * @param boolean $isMethod
	 * @return unknown
	 */
	function toJson($variable,$isMethod=false){
		/***
		 * $variable = array(1,2,3);
		 * eq("[1,2,3]",Variable::toJson($variable,true));
		 * $variable = "ABC";
		 * eq("\"ABC\"",Variable::toJson($variable,true));
		 * $variable = 10;
		 * eq(10,Variable::toJson($variable,true));
		 * $variable = 10.123;
		 * eq(10.123,Variable::toJson($variable,true));
		 * $variable = true;
		 * eq("true",Variable::toJson($variable,true));
		 * 
		 * $variable = array('foo', 'bar', array(1, 2, 'baz'), array(3, array(4)));
		 * eq('["foo","bar",[1,2,"baz"],[3,[4]]]',Variable::toJson($variable,true));
		 * 
		 * $variable = array("foo"=>"bar",'baz'=>1,3=>4);
		 * eq('{"foo":"bar","baz":1,"3":4}',Variable::toJson($variable,true));
		 * 
		 * unit("lang.VariableTest");
		 * 
		 */
		switch(gettype($variable)){
			case "boolean":
				return ($variable) ? "true" : "false";
			case "integer":
				return intval(sprintf("%d",$variable));
			case "double":
				return floatval(sprintf("%f",$variable));
			case "array":
				if(ArrayUtil::ishash($variable)){
					$list = array();
					foreach($variable as $key => $value) $list[] = sprintf("\"%s\":%s",$key,Variable::toJson($value));
					return sprintf("{%s}",implode(",",$list));
				}
				$list = array();
				foreach($variable as $key => $value) $list[] = Variable::toJson($value);
				return sprintf("[%s]",implode(",",$list));
			case "object":
				$list = array();
				foreach(ObjectUtil::objectConvHash($variable,array(),$isMethod) as $key => $value){
					$list[] = sprintf("\"%s\":%s",$key,Variable::toJson($value));
				}
				return sprintf("{%s}",implode(",",$list));
			case "string":
				return sprintf("\"%s\"",addslashes(StringUtil::encode($variable,StringUtil::UTF8())));
			default:
		}
		return "null";
	}
	
	/**
	 * JsonからPHPの変数に変換
	 *
	 * @param string $json
	 * @return unknown
	 */
	function parseJson($json){
		/***
		 * $variable = "ABC";
		 * eq($variable,Variable::parseJson('"ABC"'));
		 * $variable = 10;
		 * eq($variable,Variable::parseJson(10));
		 * $variable = 10.123;
		 * eq($variable,Variable::parseJson(10.123));
		 * $variable = true;
		 * eq($variable,Variable::parseJson("true"));
		 * $variable = false;
		 * eq($variable,Variable::parseJson("false"));
		 * $variable = null;
		 * eq($variable,Variable::parseJson("null"));
		 * $variable = array(1,2,3);
		 * eq($variable,Variable::parseJson("[1,2,3]"));
		 * $variable = array(1,2,array(9,8,7));
		 * eq($variable,Variable::parseJson("[1,2,[9,8,7]]"));
		 * $variable = array(1,2,array(9,array(10,11),7));
		 * eq($variable,Variable::parseJson("[1,2,[9,[10,11],7]]"));
		 * 
		 * $variable = array("A"=>"a","B"=>"b","C"=>"c");
		 * eq($variable,Variable::parseJson('{"A":"a","B":"b","C":"c"}'));
		 * $variable = array("A"=>"a","B"=>"b","C"=>array("E"=>"e","F"=>"f","G"=>"g"));
		 * eq($variable,Variable::parseJson('{"A":"a","B":"b","C":{"E":"e","F":"f","G":"g"}}'));
		 * $variable = array("A"=>"a","B"=>"b","C"=>array("E"=>"e","F"=>array("H"=>"h","I"=>"i"),"G"=>"g"));
		 * eq($variable,Variable::parseJson('{"A":"a","B":"b","C":{"E":"e","F":{"H":"h","I":"i"},"G":"g"}}'));
		 * 
		 * $variable = array("A"=>"a","B"=>array(1,2,3),"C"=>"c");
		 * eq($variable,Variable::parseJson('{"A":"a","B":[1,2,3],"C":"c"}'));
		 * $variable = array("A"=>"a","B"=>array(1,array("C"=>"c","D"=>"d"),3),"C"=>"c");
		 * eq($variable,Variable::parseJson('{"A":"a","B":[1,{"C":"c","D":"d"},3],"C":"c"}'));
		 * 
		 * $variable = array(array("a"=>1,"b"=>array("a","b",1)),array(null,false,true));
		 * eq($variable,Variable::parseJson('[ {"a" : 1, "b" : ["a", "b", 1] }, [ null, false, true ] ]'));
		 * 
		 * eq(null,Variable::parseJson("[1,2,3,]"));
		 * eq(null,Variable::parseJson("[1,2,3,,,]"));
		 * eq(array(1,null,3),Variable::parseJson("[1,[1,2,],3]"));
		 * eq(null,Variable::parseJson('{"A":"a","B":"b","C":"c",}'));
		 */
		if(!is_string($json)) return $json;
		if($json == "null") return null;
		if($json == "true") return true;
		if($json == "false") return false;
		if(is_numeric($json)){
			if(strpos($json,".") !== false) return floatval($json);
			return intval($json);
		}
		$json = preg_replace("/[\s]*([,\:\{\}\[\]])[\s]*/","\\1",
						preg_replace("/[\"].*?[\"]/esm",'str_replace(array(",",":","{","}","[","]"),array("#B#","#C#","#D#","#E#","#F#","#G#"),"\\0")',
							str_replace("\\\"","#A#",trim($json))));
		if(preg_match("/^\"([^\"]*?)\"$/",$json)){
			return str_replace(array("#A#","#B#","#C#","#D#","#E#","#F#","#G#"),array("\\\"",",",":","{","}","[","]"),substr($json,1,-1));
		}
		$start = substr($json,0,1);
		$end = substr($json,-1);
		if(($start == "[" && $end == "]") || ($start == "{" && $end == "}")){
			$hash = ($start == "{");
			$src = substr($json,1,-1);
			$list = array();
			while(strpos($src,"[") !== false){
				list($value,$start,$end) = StringUtil::block($src,"[","]");
				if($value === null) return null;
				$src = str_replace("[".$value."]",str_replace(array("[","]",","),array("#AA#","#AB","#AC"),"[".$value."]"),$src);
			}
			while(strpos($src,"{") !== false){
				list($value,$start,$end) = StringUtil::block($src,"{","}");
				if($value === null) return null;
				$src = str_replace("{".$value."}",str_replace(array("{","}",","),array("#BA#","#BB","#AC"),"{".$value."}"),$src);
			}
			foreach(explode(",",$src) as $value){
				if($value === "") return null;
				$value = str_replace(array("#AA#","#AB","#BA#","#BB","#AC"),array("[","]","{","}",","),$value);

				if($hash){
					list($key,$var) = explode(":",$value,2);
					$index = Variable::parseJson($key);
					if($index === null) $index = $key;
					$list[$index] = Variable::parseJson($var);
				}else{
					$list[] = Variable::parseJson($value);
				}
			}
			return $list;
		}
		return null;
	}
	
	/**
	 * tag.model.SimpleTagに変換して取得
	 *
	 * @param string $name
	 * @param unknown_type $variable
	 * @return tag.model.SimpleTag
	 */
	function toSimpleTag($name,$variable){
		/*** unit("lang.VariableTest"); */
		if(Rhaco::import("tag.model.SimpleTag")){
			if(is_array($variable)){
				if(!ArrayUtil::ishash($variable)){
					$result = "";
					foreach($variable as $value){
						$tag = Variable::toSimpleTag($name,$value);
						$result  .= $tag->get();
					}
					return new SimpleTag(null,$result);
				}else{
					$tag = new SimpleTag($name);
					foreach($variable as $key => $value){
						$tag->addValue(Variable::toSimpleTag($key,$value));
					}
					return $tag;
				}
			}else if(is_object($variable)){
				if(Variable::istype("SimpleTag",$variable)) return $variable;
				$name = (preg_match("/^[\d]+$/",$name)) ? strtolower(get_class($variable)) : $name;
				$tag = new SimpleTag($name);

				foreach(ObjectUtil::objectConvHash($variable,array(),true) as $key => $value){
					$tag->addValue(Variable::toSimpleTag($key,$value));
				}
				return $tag;
			}else{
				$variable = (is_bool($variable)) ? Variable::bool($variable,true) : $variable;
				return new SimpleTag($name,$variable);
			}
		}
		return "";
	}

	/**
	 * 変数からHTTPクエリ文字列に変換
	 *
	 * @param unknown_type $variable
	 * @param string $name
	 * @param boolean $null
	 * @param boolean $isobject オブジェクトも対象にするか
	 * @return string
	 */
	function toHttpQuery($variable,$name,$null=true,$isobject=false){
		/***
		 * unit("lang.VariableTest");
		 * 
		 * $list = array(123);
		 * eq("req[0]=123&",Variable::toHttpQuery($list,"req"));
		 * 
		 * $list = array(123,456,789);
		 * eq("req[0]=123&req[1]=456&req[2]=789&",Variable::toHttpQuery($list,"req"));
		 */
		$result = "";
		if($null === false && ($variable === null || $variable === "")) return "";
		if(!preg_match("/^[\w_]+/i",$name)) return "";
		if(is_object($variable)) $variable = ($isobject) ? ObjectUtil::objectConvHash($variable,array()) : "";
		if(is_array($variable)){
			foreach($variable as $key => $var){
				$result .= Variable::toHttpQuery($var,$name."[".$key."]",$null,$isobject);
			}
		}else{
			if(is_bool($variable)) $variable = Variable::bool($variable,true);
			$result .= $name."=".urlencode($variable)."&";
		}
		return $result;
	}

	/**
	 * 大文字小文字を区別しない比較
	 *
	 * @param unknown_type $var1
	 * @param unknown_type $var2
	 * @return boolean
	 */
	function iequal($var1,$var2){
		/***
		 * assert(Variable::iequal(1,1));
		 * assert(Variable::iequal("ABC","abC"));
		 */
		return Variable::equal($var1,$var2,false);
	}
	
	/**
	 * 大文字小文字を区別する比較
	 *
	 * @param unknown_type $var1
	 * @param unknown_type $var2
	 * @return boolean
	 */
	function equal($var1,$var2,$caseSensitive=true){
		/***
		 * assert(Variable::equal(1,1));
		 * assert(Variable::equal("ABC","ABC"));
		 * assert(Variable::equal(array(1,2,3),array(1,2,3)));
		 * assert(Variable::equal(array("A"=>1,"B"=>2,"C"=>3),array("A"=>1,"B"=>2,"C"=>3)));
		 * 
		 * assert(Variable::equal(1,1,false));
		 * assert(Variable::equal("ABC","AbC",false));
		 * assert(Variable::equal(array("A","B","C"),array("A","b","c"),false));
		 * assert(Variable::equal(array("A"=>1,"B"=>2,"C"=>3),array("A"=>1,"B"=>2,"C"=>3),false));
		 * assert(Variable::equal(array("A"=>"A","B"=>"B","C"=>"C"),array("A"=>"a","B"=>"B","C"=>"C"),false));
		 * assert(!Variable::equal(array("A"=>1,"B"=>2,"C"=>3),array("A"=>1,"b"=>2,"c"=>3),false));
		 * 
		 */
		if(is_object($var1) && is_object($var2)){
			if(Variable::istype($var1,$var2)){
				$vars = get_object_vars($var2);				
				foreach(get_object_vars($var1) as $name => $value){
					if(!array_key_exists($name,$vars)) return false;
					if(!Variable::equal($var1->$name,$var2->$name,$caseSensitive)) return false;
				}
				return true;
			}
			return false;
		}else if(is_array($var1) && is_array($var2)){
			if(sizeof($var1) != sizeof($var2)) return false;
			$size = sizeof($var1);
			$key1 = array_keys($var1);
			$key2 = array_keys($var2);
			for($i=0;$i<$size;$i++){
				if($key1[$i] !== $key2[$i]) return false;
			}
			$value1 = array_values($var1);
			$value2 = array_values($var2);
			for($i=0;$i<$size;$i++){
				if(!Variable::equal($value1[$i],$value2[$i],$caseSensitive)) return false;
			}
			return true;
		}else if(is_float($var1) && is_float($var2)){
			return (strval($var1) === strval($var2));
		}else if(!$caseSensitive && is_string($var1) && is_string($var2)){
			return (strtolower(strval($var1)) === strtolower(strval($var2)));
		}
		return ($var1 === $var2);
	}
	
	/**
	 * プロパティーをコピーする
	 * fromobjectがtag.model.SimpleTagの場合はXMLからコピーする
	 * propertyOnly=falseの場合はsetter,getterでコピーする
	 * ObjectUtil::copyPropertiesとは違い、元の変数に変化はない
	 *
	 * @param unknown_type $fromobject
	 * @param unknown_type $toobject
	 * @param unknown_type $propertyOnly
	 * @param unknown_type $excludeProperty
	 * @return unknown
	 */
	function copyProperties($fromobject,$toobject,$propertyOnly=false,$excludeProperty=array()){
		/*** unit("lang.VariableTest"); */
		$obj = Variable::copy($toobject);
		return ObjectUtil::copyProperties($fromobject,$obj,$propertyOnly,$excludeProperty);
	}
	
	/**
	 * 変数を入れ替える
	 *
	 * @param unknown_type $varA
	 * @param unknown_type $varB
	 * @param boolean $cond
	 * @return array($varA,$varB)
	 */
	function exchange(&$varA,&$varB,$cond=true){
		/***
		 * $a = 1;
		 * $b = 2;
		 * Variable::exchange($a,$b);
		 * eq($a,2);
		 * eq($b,1);
		 * 
		 * $a = 1;
		 * $b = 2;
		 * list($a,$b) = Variable::exchange($a,$b);
		 * eq($a,2);
		 * eq($b,1);
		 * 
		 * $a = 1;
		 * $b = 2;
		 * list($a,$b) = Variable::exchange($a,$b,false);
		 * eq($a,1);
		 * eq($b,2);
		 */
		if($cond){
			$tmp = Variable::copy($varA);
			$varA = $varB;
			$varB = $tmp;
		}
		return array($varA,$varB);
	}
}
?>