<?php
Rhaco::import("lang.StringUtil");
/**
 * 検証クラス
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Validate{
	/**
	 * 数字か
	 * Validate::isIntegerのエイリアス
	 *
	 * @param unknown_type $variable
	 * @param int $minSize
	 * @param int $maxSize
	 * @return boolean
	 */
	function isInt($variable,$minSize,$maxSize){
		/***
		 * assert(Validate::isInt(123,0,200));
		 * assert(Validate::isInt("123",0,200));
		 * assert(!Validate::isInt("abc",0,200));
		 * assert(!Validate::isInt(123,1,100));
		 * assert(!Validate::isInt(123,200,300));
		 * assert(Validate::isInt(-123,-200,300));
		 */
		return Validate::isInteger($variable,$minSize,$maxSize);
	}
	
	/**
	 * 数字でかつ指定の桁数以内か
	 *
	 * @param unknown_type $variable
	 * @param int $maxLength
	 * @return boolean
	 */
	function isIntegerLength($variable,$maxLength){
		/***
		 * assert(Validate::isIntegerLength(123,3));
		 * assert(!Validate::isIntegerLength(123,2));
		 * assert(Validate::isIntegerLength(-123,3));
		 * assert(!Validate::isIntegerLength(-123,2));
		 */
		if(preg_match("/([\-]{0,1})[0-9]+/",$variable,$match)){
			if($match[1] == "-") $maxLength = $maxLength + 1;
			if(strlen($variable) <= $maxLength) return true;
		}
		return false;
	}
	
	/**
	 * 数字でかつ指定の桁数内か
	 *
	 * @param unknown_type $variable
	 * @param int $minSize
	 * @param int $maxSize
	 * @return boolean
	 */
	function isInteger($variable,$minSize,$maxSize){
		/***
		 * assert(Validate::isInteger(123,0,200));
		 * assert(Validate::isInteger("123",0,200));
		 * assert(!Validate::isInteger("abc",0,200));
		 * assert(!Validate::isInteger(123,1,100));
		 * assert(!Validate::isInteger(123,200,300));
		 * assert(Validate::isInteger(-123,-200,300));
		 */
		if(preg_match("/^[\-]{0,1}[0-9]+$/",$variable) && $minSize <= $variable && $variable <= $maxSize){
			return true;
		}
		return false;
	}
	
	/**
	 * 文字列でかつ指定の長さ内か
	 *
	 * @param unknown_type $variable
	 * @param int $minSize
	 * @param int $maxSize
	 * @return boolean
	 */
	function isString($variable,$minSize,$maxSize){
		/***
		 * assert(Validate::isString("abc",1,3));
		 * assert(!Validate::isString("abc",4,5));
		 * assert(!Validate::isString(true,2,5));
		 */
		if($minSize <= StringUtil::strlen($variable) && StringUtil::strlen($variable) <= $maxSize){
			return true;
		}
		return false;
	}
	
	/**
	 * アルファベットか
	 *
	 * @param unknown_type $variable
	 * @return boolean
	 */
	function isAlphabet($variable){
		/***
		 * assert(Validate::isAlphabet("abc"));
		 * assert(Validate::isAlphabet("ABC"));
		 * assert(Validate::isAlphabet("AbC"));
		 * assert(!Validate::isAlphabet("ABC123"));
		 */
		if(preg_match("/^[a-z]+$/i",$variable)){
			return true;
		}
		return false;
	}	
	/**
	 * ファイル名として妥当か
	 *
	 * @param unknown_type $variable
	 * @return boolean
	 */
	function isFilename($variable){
		/***
		 * assert(Validate::isFilename("hoge.php"));
		 * assert(Validate::isFilename("123.php"));
		 * assert(Validate::isFilename("abc-def.php"));
		 * assert(Validate::isFilename("abc_def.php"));
		 * assert(Validate::isFilename("012345678901234567890123456789012345678901234567890123456789.php"));
		 * assert(!Validate::isFilename("012345678901234567890123456789012345678901234567890123456789.phps"));
		 */
		if(Validate::isString($variable,1,64)){
			if(preg_match("/^[\w][\w\.\-]*$/i",$variable)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * タイムスタンプとして妥当か
	 *
	 * @param unknown_type $timestamp
	 * @return boolean
	 */
	function isTimestamp($timestamp){
		/***
		 * assert(Validate::isTimestamp("1976/10/04 16:24:21"));
		 * assert(!Validate::isTimestamp("6/11/32 16:24:21"));
		 */
		if(is_int($timestamp)) return true;
		$timestampList	= array();
		$timestamp		= preg_replace("/[^0-9]/","",$timestamp);
		if(preg_match("/^([1-2][0-9][0-9][0-9])([0-1][0-9])([0-3][0-9])([0-2][0-9])([0-5][0-9])([0-5][0-9])$/",$timestamp,$timestampList)){
			return Validate::isDate($timestampList[1],$timestampList[2],$timestampList[3]);
		}
		return false;
	}
	
	/**
	 * 日付として妥当か
	 *
	 * @param unknown_type $year
	 * @param unknown_type $month
	 * @param unknown_type $day
	 * @return boolean
	 */
	function isDate($year,$month,$day){
		/***
		 * assert(Validate::isDate(1976,10,4));
		 * assert(!Validate::isDate(6,10,4));
		 */
		if(Validate::isInteger($year,1900,2050)){
			if(Validate::isInteger($month,1,12)){
				if(Validate::isInteger($day,1,31)){
					return checkdate($month,$day,$year);
				}
			}
		}
		return false;
	}
	/**
	 * 日付の妥当性チェック
	 *
	 * @param unknown_type $intdate
	 * @return unknown
	 */
	function isDay($intdate){
		/***
		 * assert(Validate::isDay(19761004),19761004);
		 * assert(Validate::isDay(19761031),19761031);
		 * assert(!Validate::isDay(19761032),19761032);
		 * assert(Validate::isDay(20040229),20040229);
		 * assert(Validate::isDay(20000229),20000229);
		 * assert(!Validate::isDay(19000229),19000229);
		 * assert(Validate::isDay("190/01/01"),"190/01/01");
		 * assert(Validate::isDay(1900101),1900101);
		 */
		$intdate = intval(preg_replace("/[^\d]/","",$intdate));

		$m = intval(substr($intdate,-4,2));
		if($m < 1 || $m > 12) return false;
		
		$d = intval(substr($intdate,-2));
		if($d > 0){
			if($m == 2){
				$y = intval(substr($intdate,0,-4));
				if($d < 29 || (($y % 4 == 0) && (($y % 100 != 0) || ($y % 400 == 0))) && $d < 30) return true;
			}else if(in_array($m,array(4,6,9,11)) && $d < 31){
				return true;
			}else if($d < 32){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 電話番号として妥当か
	 *
	 * @param unknown_type $tel1
	 * @param unknown_type $tel2
	 * @param unknown_type $tel3
	 * @return boolean
	 */
	function isTel($tel1,$tel2,$tel3){
		/***
		 * assert(Validate::isTel("03","123","4567"));
		 * assert(!Validate::isTel("03","4","56"));
		 */
		if(Validate::isInteger($tel1,0,99999) &&
			Validate::isInteger($tel2,0,99999) &&
			Validate::isInteger($tel3,0,99999)
		){
			if(Validate::isString($tel1.$tel2.$tel3,9,11)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 郵便番号として妥当か
	 *
	 * @param unknown_type $zip1
	 * @param unknown_type $zip2
	 * @return boolean
	 */
	function isZip($zip1,$zip2){
		/***
		 * assert(Validate::isZip("102","0075"));
		 * assert(!Validate::isZip("102","75"));
		 * 
		 */
		if(Validate::isInteger($zip1,0,999) &&
			Validate::isInteger($zip2,0,9999)
		){
			if(Validate::isString($zip1.$zip2,7,7)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Emailアドレスとして妥当か
	 *
	 * @param unknown_type $email
	 * @return boolean
	 */
	function isEmail($email){
		/***
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@s.cd"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@a.s.cd"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@b.a.s.cd"));
		 * assert(!Validate::isEmail("aaaaaaa.aaaaaaaaa.a@b.a.s.c"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@b.a.s.abcdef")); 
		 * assert(!Validate::isEmail("aaaaaaa.aaaaaaaaa.a@b.a.s.abcdefg")); 
		 * 
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@s.sss.cd"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@sss.cd"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.a@s.sss.sss.cd"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaaaaaa.@s.sss.sss.cd"));
		 * 
		 * assert(Validate::isEmail("aaaaaaa.aaaaa1234.@ssss.sss.ss.cd"));
		 * assert(Validate::isEmail("aaaaaaa.aaaaa1234.@ssss.ss.cd"));
		 */
		if(preg_match("/^[\x01-\x7F]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}$/i",$email) && strlen($email) <= 255){
			return true;
		 }
		 return false;
	}
	
	/**
	 * クレジットカード番号として妥当か
	 *
	 * @param unknown_type $no1
	 * @param unknown_type $no2
	 * @param unknown_type $no3
	 * @param unknown_type $no4
	 * @return boolean
	 */
	function isCreditcard($no1,$no2,$no3,$no4){
		/***
		 * assert(Validate::isCreditcard("0123","4567","8901","2345"));
		 * assert(!Validate::isCreditcard("0123","4567","A901","234"));
		 * assert(!Validate::isCreditcard("0123","467","8901","2345"));
		 */
		if(Validate::isInteger($no1,0,9999) &&
			Validate::isInteger($no2,0,9999) &&
			Validate::isInteger($no3,0,9999) &&
			Validate::isInteger($no4,0,9999)
		){
			if(Validate::isString($no1.$no2.$no3.$no4,16,16)){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 空か
	 *
	 * @param unknown_type $str
	 * @return boolean
	 */
	function isEmpty($str){
		/***
		 * assert(!Validate::isEmpty(123));
		 * assert(!Validate::isEmpty("abc"));
		 * assert(!Validate::isEmpty(true));
		 * assert(Validate::isEmpty(""));
		 * assert(Validate::isEmpty(0));
		 * assert(Validate::isEmpty(null));
		 * assert(!Validate::isEmpty("0"));
		 */
		return ($str === null || $str === "" || $str === 0) ? true : false;
	}
}