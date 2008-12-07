<?php
/**
 * 単位変換を扱うユーティリティ
 * 
 * @author Kentaro YABE
 * @license New BSD License
 * @copyright Copyright 2008- rhaco project. All rights reserved.
 */
class UnitUtil{
	/**
	 * 単位を変換する
	 *
	 * @static 
	 * @param numeric $value 変換元単位での値
	 * @param string $to_unit 変換先単位
	 * @param string $from_unit 変換元単位
	 * @return float
	 */
	function convert($value,$to_unit,$from_unit=""){
		/***
		 * eq(0.0,UnitUtil::convert(1,"pt","kg"));
		 */
		if(!$from_unit) list($value,$from_unit) = UnitUtil::parse($value);
		return $value * UnitUtil::ratio($to_unit,$from_unit);
	}
	
	/**
	 * 文字列から数値と単位を切り分ける
	 * 
	 * @static 
	 * @param string $value
	 * @return array
	 */
	function parse($value){
		/***
		 * eq(array(1.0,""),UnitUtil::parse("1"));
		 * eq(array(1.0,"point"),UnitUtil::parse("1pt"));
		 * eq(array(1.0,"inch"),UnitUtil::parse("1in"));
		 * eq(array(1.0,"cm"),UnitUtil::parse("1cm"));
		 * eq(array(1.0,"mm"),UnitUtil::parse("1mm"));
		 * eq(array(1.0,"point"),UnitUtil::parse("1.0point"));
		 * eq(array(0.1,"inch"),UnitUtil::parse("0.1inch"));
		 * eq(array(floatval(-1.0e+5),"point"),UnitUtil::parse("-1.0e+5pt"));
		 */
		if(is_numeric($value)){
			return array(floatval($value),"");
		}
		
		if(preg_match("/([+-]?\d+(?:\.\d+(?:e[+-]?\d+)?)?)\s*([a-z]+)/i",$value,$matches)){
			return array(floatval($matches[1]), UnitUtil::unittype($matches[2]));
		}
		
		return false;
	}
	
	/**
	 * 正規化された単位名称を取得する
	 * 
	 * @static 
	 * @param string $unit
	 * @return string
	 */
	function unittype($unit){
		/***
		 * eq("point",UnitUtil::unittype("pt"));
		 * eq("",UnitUtil::unittype("hoge"));
		 */
		switch($unit){
		//長さ
		case "pt":
		case "point":
			return "point";
		case "pc":
		case "pica":
			return "pica";
		case "cm":
			return "cm";
		case "mm":
			return "mm";
		case "in":
		case "inch":
			return "inch";
		//質量
		case "kg":
			return "kg";
		case "t":
		case "ton":
			return "ton";
		//時間
		case "d":
		case "day":
			return "day";
		case "h":
		case "hour":
			return "hour";
		case "min":
		case "minute":
			return "minute";
		case "s":
		case "sec":
		case "second":
			return "second";
		}
		
		return "";
	}
	
	/**
	 * 単位間の比率を取得
	 * 
	 * pointとかからの絶対変換だと精度落ちるから、変換マップ使ってるけどどうなんだろう？
	 * MathUtil使うなら一単位からの絶対変換で良いような気もする。
	 * 
	 * @static 
	 * @param string $to_unit
	 * @param string $from_unit
	 * @return float 変換が不可能な場合には0
	 */
	function ratio($to_unit,$from_unit){
		/***
		 * eq(72.0,UnitUtil::ratio("pt","inch"));
		 * eq(0.0,UnitUtil::ratio("pt","kg"));
		 */
		//長さ
		$point = array("point"=>1, "pica"=>12, "inch"=>72, "cm"=>72/2.54, "mm"=>72/25.4);
		$pica = array("point"=>1/12, "pica"=>1, "inch"=>6, "cm"=>6/2.54, "mm"=>6/25.4);
		$inch = array("point"=>1/72, "pica"=>1/6, "inch"=>1, "cm"=>1/2.54, "mm"=>1/25.4);
		$cm = array("point"=>2.54/72, "pica"=>2.54/6, "inch"=>2.54, "cm"=>1, "mm"=>0.1);
		$mm = array("point"=>25.4/72, "pica"=>25.4/6, "inch"=>25.4, "cm"=>10, "mm"=>1);
		
		//重さ
		$kg = array("kg"=>1, "ton"=>1000);
		$ton = array("kg"=>0.001, "ton"=>1);
		
		//時間
		$day = array("day"=>1, "hour"=>1/24, "minute"=>1/1440, "second"=>1/86400);
		$hour = array("day"=>24, "hour"=>1, "minute"=>1/60, "second"=>1/3600);
		$minute = array("day"=>1440, "hour"=>60, "minute"=>1, "second"=>1/60);
		$second = array("day"=>86400, "hour"=>3600, "minute"=>60, "second"=>1);
		
		
		$to_unit = UnitUtil::unittype($to_unit);
		$from_unit = UnitUtil::unittype($from_unit);
		if(!$to_unit || !$from_unit) return 0.0;
		return isset(${$to_unit}[$from_unit]) ? floatval(${$to_unit}[$from_unit]) : 0.0;
	}
	
	/**
	 * point単位へ変換する
	 *
	 * @static 
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function point($value,$unit=""){
		/***
		 * Rhaco::import("lang.MathUtil");
		 * eq(MathUtil::round(1,6),MathUtil::round(UnitUtil::point("1pt"),6));
		 * eq(MathUtil::round(72,6),MathUtil::round(UnitUtil::point("1in"),6));
		 * eq(MathUtil::round(72/2.54,6),MathUtil::round(UnitUtil::point("1cm"),6));
		 * eq(MathUtil::round(72/25.4,6),MathUtil::round(UnitUtil::point("1mm"),6));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"point",$unit);
	}
	
	/**
	 * pica単位へ変換する
	 *
	 * @static 
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function pica($value,$unit=""){
		/*** #pass see UnitUtil::convert */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"pica",$unit);
	}
	
	/**
	 * inch単位へ変換する
	 *
	 * @static 
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function inch($value,$unit=""){
		/***
		 * Rhaco::import("lang.MathUtil");
		 * eq(MathUtil::round(1/72,6),MathUtil::round(UnitUtil::inch("1pt"),6));
		 * eq(MathUtil::round(1,6),MathUtil::round(UnitUtil::inch("1in"),6));
		 * eq(MathUtil::round(1/2.54,6),MathUtil::round(UnitUtil::inch("1cm"),6));
		 * eq(MathUtil::round(1/25.4,6),MathUtil::round(UnitUtil::inch("1mm"),6));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"inch",$unit);
	}
	
	/**
	 * cm単位へ変換する
	 *
	 * @static 
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function cm($value,$unit=""){
		/***
		 * Rhaco::import("lang.MathUtil");
		 * eq(MathUtil::round(2.54/72,6),MathUtil::round(UnitUtil::cm("1pt"),6));
		 * eq(MathUtil::round(2.54,6),MathUtil::round(UnitUtil::cm("1in"),6));
		 * eq(MathUtil::round(1,6),MathUtil::round(UnitUtil::cm("1cm"),6));
		 * eq(MathUtil::round(0.1,6),MathUtil::round(UnitUtil::cm("1mm"),6));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"cm",$unit);
	}
	
	/**
	 * mm単位へ変換する
	 *
	 * @static 
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function mm($value,$unit=""){
		/***
		 * Rhaco::import("lang.MathUtil");
		 * eq(MathUtil::round(25.4/72,6),MathUtil::round(UnitUtil::mm("1pt"),6));
		 * eq(MathUtil::round(25.4,6),MathUtil::round(UnitUtil::mm("1in"),6));
		 * eq(MathUtil::round(10,6),MathUtil::round(UnitUtil::mm("1cm"),6));
		 * eq(MathUtil::round(1,6),MathUtil::round(UnitUtil::mm("1mm"),6));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"mm",$unit);
	}
	
	/**
	 * kg単位へ変換する
	 * 
	 * @static
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function kg($value,$unit=""){
		/***
		 * eq(1000.0,UnitUtil::kg("1t"));
		 * eq(1.0,UnitUtil::kg("1kg"));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"kg",$unit);
	}
	
	/**
	 * ton単位へ変換する
	 * 
	 * @static
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function ton($value,$unit=""){
		/***
		 * eq(1.0,UnitUtil::ton("1t"));
		 * eq(0.001,UnitUtil::ton("1kg"));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"ton",$unit);
	}
	
	/**
	 * 日単位へ変換する
	 * 
	 * @static
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function day($value,$unit=""){
		/***
		 * eq(1.0,UnitUtil::day("1d"));
		 * eq(1.0,UnitUtil::day("24h"));
		 * eq(1.0,UnitUtil::day("1440min"));
		 * eq(1.0,UnitUtil::day("86400s"));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"day",$unit);
	}
	
	/**
	 * 時単位へ変換する
	 * 
	 * @static
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function hour($value,$unit=""){
		/***
		 * eq(24.0,UnitUtil::hour("1d"));
		 * eq(1.0,UnitUtil::hour("1h"));
		 * eq(1.0,UnitUtil::hour("60min"));
		 * eq(1.0,UnitUtil::hour("3600s"));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"hour",$unit);
	}
	
	/**
	 * 分単位へ変換する
	 * 
	 * @static
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function minute($value,$unit=""){
		/***
		 * eq(1440.0,UnitUtil::minute("1d"));
		 * eq(60.0,UnitUtil::minute("1h"));
		 * eq(1.0,UnitUtil::minute("1min"));
		 * eq(1.0,UnitUtil::minute("60s"));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"minute",$unit);
	}
	
	/**
	 * 秒単位へ変換する
	 * 
	 * @static
	 * @param float $value
	 * @param string $unit
	 * @return float
	 */
	function second($value,$unit=""){
		/***
		 * eq(86400.0,UnitUtil::second("1d"));
		 * eq(3600.0,UnitUtil::second("1h"));
		 * eq(60.0,UnitUtil::second("1min"));
		 * eq(1.0,UnitUtil::second("1s"));
		 */
		if(!$unit) list($value,$unit) = UnitUtil::parse($value);
		return UnitUtil::convert($value,"second",$unit);
	}
}
?>