<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.StringUtil");
/**
 * 配列を扱うユーティリティ
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ArrayUtil{
	/**
	 * 辞書をハッシュに変換
	 */
	function dict($dict,$keys,$fill=true){
		/***
		 * $dict = "name=hogehoge,title='rhaco',arg=get\,tha";
		 * $keys = array("name","arg","description","title");
		 * $result = ArrayUtil::dict($dict,$keys);
		 * eq(4,sizeof($result));
		 * foreach($result as $key => $value) $$key = $value;
		 * eq("hogehoge",$name);
		 * eq("rhaco",$title);
		 * eq(null,$description);
		 * eq("get,tha",$arg);
		 * 
		 * $dict = array("hogehoge","rhaco","get,tha");
		 * $keys = array("name","arg","description","title");
		 * $result = ArrayUtil::dict($dict,$keys);
		 * eq(4,sizeof($result));
		 * foreach($result as $key => $value) $$key = $value;
		 * eq("hogehoge",$name);
		 * eq(null,$title);
		 * eq("get,tha",$description);
		 * eq("rhaco",$arg);
		 * 
		 * $dict = array("name=hogehoge,title='rhaco',arg=get\,tha");
		 * $keys = array("name","arg","description","title");
		 * $result = ArrayUtil::dict($dict,$keys);
		 * eq(4,sizeof($result));
		 * foreach($result as $key => $value) $$key = $value;
		 * eq("hogehoge",$name);
		 * eq("rhaco",$title);
		 * eq(null,$description);
		 * eq("get,tha",$arg);
		 * 
		 * $dict = array("id=123,hoge=,abc=abc");
		 * $result = ArrayUtil::dict($dict,array("id","hoge","abc"));
		 * eq(123,$result["id"]);
		 * eq(null,$result["hoge"]);
		 * eq("abc",$result["abc"]);
		 */
		$result = $args = array();
		if(is_array($dict) && sizeof($dict) == 1 && isset($dict[0]) && strpos($dict[0],"=") !== false) $dict = $dict[0];
		if(is_array($dict)){
			$dict = ArrayUtil::arrays($dict,0,sizeof($keys,true));
			foreach($keys as $i => $key){
				if($fill){
					$result[$key] = array_key_exists($i,$dict) ? $dict[$i] : null;
				}else if(array_key_exists($i,$dict)){
					$result[$key] = $dict[$i];
				}
			}
		}else{
			if(preg_match_all("/.+?[^\\\],|.+?$/",$dict,$match)){
				foreach($match[0] as $arg){
					list($key,$value) = ArrayUtil::arrays(explode("=",$arg,2),0,2,true);
					$value = preg_replace("/^(.*),$/","\\1",$value);

					if(StringUtil::isInt($value)){
						$args[$key] = intval($value);
					}else if(StringUtil::isFloat($value)){
						$args[$key] = floatval($value);
					}else if($value == ""){
						$args[$key] = null;
					}else{
						$args[$key] = str_replace("\\,",",",preg_replace("/^[\'\"](.*)[\'\"]$/","\\1",$value));
					}
				}
			}
			foreach($keys as $name){
				if($fill){
					$result[$name] = array_key_exists($name,$args) ? $args[$name] : null;
				}else if(array_key_exists($name,$args)){
					$result[$name] = $args[$name];
				}
			}
		}
		return $result;
	}
	
	/**
	 * 変数がハッシュか
	 */
	function ishash($var){
		/***
		 * assert(!ArrayUtil::ishash(array("A","B","C")));
		 * assert(!ArrayUtil::ishash(array(0=>"A",1=>"B",2=>"C")));
		 * assert(ArrayUtil::ishash(array(1=>"A",2=>"B",3=>"C")));
		 * assert(ArrayUtil::ishash(array("a"=>"A","b"=>"B","c"=>"C")));
		 * assert(!ArrayUtil::ishash(array("0"=>"A","1"=>"B","2"=>"C")));
		 * assert(!ArrayUtil::ishash(array(0=>"A",1=>"B","2"=>"C")));
		 */
		if(!is_array($var)) return false;
		$keys = array_keys($var);
		$size = sizeof($keys);

		for($i=0;$i<$size;$i++){
			if($keys[$i] !== $i) return true;
		}
		return false;
	}
	
	/**
	 * 配列要素を文字列により連結する
	 */
	function implode($array,$glue="",$offset=0,$length=0,$fill=false){
		/***
		 * eq("hogekokepopo",ArrayUtil::implode(array("hoge","koke","popo")));
		 * eq("koke:popo",ArrayUtil::implode(array("hoge","koke","popo"),":",1));
		 * eq("koke",ArrayUtil::implode(array("hoge","koke","popo"),":",1,1));
		 * eq("hoge:koke:popo::",ArrayUtil::implode(array("hoge","koke","popo"),":",0,5,true));
		 * 
		 */
		return implode($glue,ArrayUtil::arrays($array,$offset,$length,$fill));
	}
	
	/**
	 * ハッシュからキーをcase insensitiveで値を取得する
	 *
	 * @param string $name
	 * @param array $array
	 * @return unknown
	 */
	function hget($name,$array){
		/***
		 * $list = array("ABC"=>"AA","deF"=>"BB","gHi"=>"CC");
		 * 
		 * eq("AA",ArrayUtil::hget("abc",$list));
		 * eq("BB",ArrayUtil::hget("def",$list));
		 * eq("CC",ArrayUtil::hget("ghi",$list));
		 * 
		 * eq(null,ArrayUtil::hget("jkl",$list));
		 * eq(null,ArrayUtil::hget("jkl","ABCD"));
		 */
		if(!is_array($array)) return null;
		$array = array_change_key_case($array);
		$name = strtolower($name);
		return (array_key_exists($name,$array)) ? $array[$name] : null;
	}
	
	/**
	 * 配列として取得
	 *
	 * @param unknown_type $array
	 * @param int $low
	 * @param int $high
	 * @return array
	 */
	function arrays($array,$offset=0,$length=0,$fill=false){
		/***
		 * eq(1,sizeof(ArrayUtil::arrays(array(0,1),1,1)));
		 * eq(2,sizeof(ArrayUtil::arrays(array(0,1,2),0,2)));
		 * eq(3,sizeof(ArrayUtil::arrays(array(0,1),0,3,true)));
		 * eq(2,sizeof(ArrayUtil::arrays(array(0,1,2,3,4),3,6)));
		 * eq(3,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),3,3)));
		 * eq(1,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),3,1)));
		 * eq(7,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),3)));
		 * eq(3,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),-3,3)));
		 * eq(1,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),-3,1)));
		 * eq(3,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),-3,5)));
		 * eq(7,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),0,-3)));
		 * eq(1,sizeof(ArrayUtil::arrays(array(0))));
		 * eq(1,sizeof(ArrayUtil::arrays(array(0),0,-1)));
		 * eq(2,sizeof(ArrayUtil::arrays(array(0,1,2,3,4,5,6,7,8,9),8,-3)));
		 * 
		 * eq(array("abc"),ArrayUtil::arrays("abc"));
		 * eq(array("abc","123"),ArrayUtil::arrays(array("abc","123")));
		 */
		$array = (is_array($array)) ? $array : (is_null($array) ? array() : array($array));
		if($offset == 0 && $length == 0) return $array;
		$array = (empty($length) || ($length < 0 && (sizeof($array) - ($offset - $length)) <= 0)) ? array_slice($array,$offset) : array_slice($array,$offset,$length);
		if($fill) for($i=sizeof($array);$i<$length;$i++) $array[] = null;
		return $array;
	}
	
	/**
	 * 配列のキーと値を逆にしてキーを小文字に変換する
	 *
	 * @param unknown_type $list
	 * @return unknown
	 */
	function lowerflip($list){
		/***
		 * $list = array("abc"=>"hoGe","def"=>123,"ghi"=>"__A__");
		 * eq(array("hoge"=>"abc",123=>"def","__a__"=>"ghi"),ArrayUtil::lowerflip($list));
		 */
		if(is_array($list)) return array_change_key_case(array_flip($list));
		return $list;
	}
	
	/**
	 * $patternで分割されたハッシュキーを持つ値にして返す
	 *
	 * @param strin $pattern
	 * @param string $str
	 * @return array
	 */
	function splitkeys($pattern,$str){
		/***
		 * $result = ArrayUtil::splitkeys("/","/abc/def/ghi/jklmn");
		 * assert(isset($result["abc"]["def"]["ghi"]["jklmn"]));
		 * 
		 * if(isset($result["abc"]["def"]["ghi"]["jklmn"])){
		 * 	eq("/abc/def/ghi/jklmn",$result["abc"]["def"]["ghi"]["jklmn"]);
		 * }
		 */
		$result = $str;		
		$list = split($pattern,$str);
		rsort($list);
		foreach($list as $key){
			if($key !== "" && $key !== null) $result = array($key=>$result);
		}
		return $result;
	}
	
	/**
	 * 配列からランダムに選択された値の配列を返す
	 *
	 * @param array $list
	 * @param int $size 0がしていされた場合は値を、0より大きければ配列を返す
	 * @return array
	 */
	function rand($list,$size=0){
		/***
		 * $list = array("A"=>123,"B"=>456,"C"=>789);
		 * $result = ArrayUtil::rand($list,2);
		 * eq(2,count($result));
		 * 
		 * foreach($result as $key => $value){
		 * 	switch($key){
		 * 		case "A": eq(123,$value); break;
		 * 		case "B": eq(456,$value); break;
		 * 		case "C": eq(789,$value); break;
		 * 		default: assert(false,"ここにははいらないはず");
		 * 	}
		 * }
		 */
		$keys = ArrayUtil::arrays(array_rand($list,$size));
		if($size <= 0) return $list[$keys[0]];
		$result = array();
		foreach($keys as $key){
			$result[$key] = Variable::copy($list[$key]);
		}
		return $result;
	}
	

	/**
	 * 配列からCSVを生成
	 *
	 * @param array $list
	 * @param boolean $isheader
	 * @return string
	 */
	function toCsv($list,$isheader=false){
		/***
		 * $list = array(array(1,2,3,"hoge"),array(4,5,6,"agaga"),array(7,8,9,null));
		 * $result = "1,2,3,\"hoge\"\n4,5,6,\"agaga\"\n7,8,9,\n";
		 * eq($result,ArrayUtil::toCsv($list));
		 * 
		 * $list = array(array("A"=>1,"B"=>2),array("A"=>9,"B"=>8));
		 * $result = "\"A\",\"B\"\n1,2\n9,8\n";
		 * eq($result,ArrayUtil::toCsv($list,true));
		 * 
		 * $list = array(array("A"=>1,"B"=>2),array("A"=>9,"B"=>8,"C"=>7));
		 * $result = "\"A\",\"B\"\n1,2\n9,8\n";
		 * eq($result,ArrayUtil::toCsv($list,true));
		 * 
		 * $list = array(array("A\""=>1,"B"=>2),array("A\""=>9,"B"=>8));
		 * $result = "\"A\"\"\",\"B\"\n1,2\n9,8\n";
		 * eq($result,ArrayUtil::toCsv($list,true));
		 */
		$result = "";
		if(!empty($list) && is_array($list)){
			$head = array();

			foreach($list as $values){
				foreach(ArrayUtil::arrays($values) as $key => $value) $head[] = $key;
				break;
			}
			$size = sizeof($head);
			foreach($list as $values){
				$line = "";
				$count = 1;
				$values = ArrayUtil::arrays($values);
				
				for($i=0;$i<$size;$i++){
					$value = (isset($values[$head[$i]])) ? $values[$head[$i]] : null;
					$line .= ((is_numeric($value) || empty($value)) ? $value : ("\"".str_replace("\"","\"\"",$value)."\"")).",";
				}
				$result .= substr($line,0,-1)."\n";
			}
			if($isheader){
				$headline = "";
				foreach($head as $value) $headline .= "\"".str_replace("\"","\"\"",$value)."\",";
				$result = substr($headline,0,-1)."\n".$result;
			}
		}
		return $result;
	}
	
	/**
	 * CSVから配列へ
	 *
	 * @param string $src
	 * @param boolean $isheader 一行目をヘッダ行とするか
	 * @return array
	 */
	function parseCsv($src,$isheader=false){
		/***
		 * $src = "1,2,3,\"hoge\"\n4,5,6,\"agaga\"\n";
		 * $result = array(array(1,2,3,"hoge"),array(4,5,6,"agaga"));
		 * eq($result,ArrayUtil::parseCsv($src));
		 * 
		 * $src = "1,2,\"hoge\nhoge\",3\n4,5,\"aga\"\"aga\",6\n";
		 * $result = array(array(1,2,"hoge\nhoge",3),array(4,5,"aga\"aga",6));
		 * eq($result,ArrayUtil::parseCsv($src));
		 * 
		 * $src = <<< __CSV__
		 * 1,2008/8/8,hoge,,abc
		 * 2,2006/7/9,hige,ccb,
		 * 3,,"abc""def",,
		 * 4,,,"abb,bba",
		 * __CSV__;
		 * $result = array(
		 * 					array(1,"2008/8/8","hoge",null,"abc"),
		 * 					array(2,"2006/7/9","hige","ccb",null),
		 * 					array(3,null,"abc\"def",null,null),
		 * 					array(4,null,null,"abb,bba",null),
		 * 				);
		 * eq($result,ArrayUtil::parseCsv($src));
		 * 
		 * $result = array(array("A"=>1,"B"=>2),array("A"=>9,"B"=>8));
		 * $src = "\"A\",\"B\"\n1,2\n9,8\n";
		 * eq($result,ArrayUtil::parseCsv($src,true));
		 * 
		 * $result = array(array("A"=>1,"B"=>2),array("A"=>9,"B"=>8));
		 * $src = "\"A\",\"B\"\n1,2\n9,8\n";
		 * eq($result,ArrayUtil::parseCsv($src,true));
		 * 
		 * $result = array(array("A\""=>1,"B"=>2),array("A\""=>9,"B"=>8));
		 * $src = "\"A\"\"\",\"B\"\n1,2\n9,8\n";
		 * eq($result,ArrayUtil::parseCsv($src,true));
		 */
		$result = array();
		$head = array();
		$src = preg_replace("/\".+?\"/se",
						'str_replace(array(",","\n"),array("RHACO__COMMA","RHACO__ENTER"),"\\0")',
						str_replace(array("\"\"","\"\"","\\","\$"),array("RHACO__DOUBLE","RHACO__ESCAPE","RHACO__DOLLAR"),trim(StringUtil::toULD($src)))
				);
		if($isheader){
			list($header,$src) = explode("\n",$src,2);
			$head = explode(",",$header);
			foreach($head as $key => $value){
				$head[$key] = str_replace(array("RHACO__COMMA","RHACO__ENTER","RHACO__DOUBLE","RHACO__ESCAPE","RHACO__DOLLAR"),
						array(",","\n","\"","\\","\$"),preg_replace('/^"(.+)"$/',"\\1",$value));
			}
		}
		foreach(explode("\n",$src) as $line){
			$list = array();

			foreach(explode(",",trim($line)) as $key => $value){
				if($value == "RHACO__DOUBLE") $value = null;
				$value = ($value == "RHACO__DOUBLE") ? "" : 
							(str_replace(array("RHACO__COMMA","RHACO__ENTER","RHACO__DOUBLE","RHACO__ESCAPE","RHACO__DOLLAR"),
								array(",","\n","\"","\\","\$"),preg_replace('/^"(.+)"$/',"\\1",$value)));
				$value = ($value == "") ? null : ((is_numeric($value)) ? ((strpos($value,".") !== false) ? floatval($value) : intval($value)) : $value);
				$list[(isset($head[$key]) ? $head[$key] : $key)] = $value;
			}
			$result[] = $list;
		}
		return $result;
	}
}
?>