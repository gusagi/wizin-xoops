<?php
/**
 * 数値を扱うユーティリティ
 * 
 * @author Kentaro YABE
 * @author Kazutaka Tokushima
 * @license New BSD License
 */
class MathUtil{
	/**
	 * 数値を有効桁数で丸める
	 * 
	 * @param float $value
	 * @param integer $sig
	 * @return float
	 */
	function round($value,$sig){
		/***
		 * eq(floatval(0),MathUtil::round(4, 0));
		 * eq(floatval(10),MathUtil::round(5, 0));
		 * eq(floatval(-0),MathUtil::round(-4, 0));
		 * eq(floatval(-10),MathUtil::round(-5, 0));
		 * eq(floatval(120),MathUtil::round(123.456, 2));
		 * eq(floatval(-120),MathUtil::round(-123.456, 2));
		 * eq(floatval(123.5),MathUtil::round(123.456, 4));
		 * eq(floatval(-123.5),MathUtil::round(-123.456, 4));
		 * eq(floatval(0.01235),MathUtil::round(0.0123456, 4));
		 * eq(floatval(-0.01235),MathUtil::round(-0.0123456, 4));
		 * eq(floatval(1.235e12),MathUtil::round(1.23456e12, 4));
		 * eq(floatval(-1.235e12),MathUtil::round(-1.23456e12, 4));
		 */
		if (!is_numeric($value) || !is_numeric($sig) || $value == 0 || $sig < 0) {
			return floatval(0);
		}
		$d = MathUtil::digit($value);
		$d += ($d < 0) ? 1 : 0;
		$r = round($value, -1 * ($d - intval($sig)));
		return ($r == 0) ? floatval(0) : $r;
	}
	
	/**
	 * 数値を有効桁数で切り捨てる
	 *
	 * @param float $value
	 * @param integer $sig
	 * @return float
	 */
	function floor($value,$sig){
		/***
		 * eq(floatval(0),MathUtil::floor(4, 0));
		 * eq(floatval(0),MathUtil::floor(5, 0));
		 * eq(floatval(-10),MathUtil::floor(-4, 0));
		 * eq(floatval(-10),MathUtil::floor(-5, 0));
		 * eq(floatval(120),MathUtil::floor(123.456, 2));
		 * eq(floatval(-130),MathUtil::floor(-123.456, 2));
		 * eq(floatval(123.4),MathUtil::floor(123.456, 4));
		 * eq(floatval(-123.5),MathUtil::floor(-123.456, 4));
		 * eq(floatval(0.01234),MathUtil::floor(0.0123456, 4));
		 * eq(floatval(-0.01235),MathUtil::floor(-0.0123456, 4));
		 * eq(floatval(1.234e12),MathUtil::floor(1.23456e12, 4));
		 * eq(floatval(-1.235e12),MathUtil::floor(-1.23456e12, 4));
		 */
		if (!is_numeric($value) || !is_numeric($sig) || $value == 0 || $sig < 0) {
			return floatval(0);
		}
		$d = MathUtil::digit($value);
		$d += ($d < 0) ? 1 : 0;
		$d = -1 * ($d - intval($sig));
		return floor($value * pow(10, $d)) / pow(10, $d);
	}
	
	/**
	 * 数値を有効桁数で切り上げる
	 *
	 * @param float $value
	 * @param int $sig
	 * @return float
	 */
	function ceil($value,$sig){
		/***
		 * eq(floatval(10),MathUtil::ceil(4, 0));
		 * eq(floatval(10),MathUtil::ceil(5, 0));
		 * eq(floatval(0),MathUtil::ceil(-4, 0));
		 * eq(floatval(0),MathUtil::ceil(-5, 0));
		 * eq(floatval(130),MathUtil::ceil(123.456, 2));
		 * eq(floatval(-120),MathUtil::ceil(-123.456, 2));
		 * eq(floatval(123.5),MathUtil::ceil(123.456, 4));
		 * eq(floatval(-123.4),MathUtil::ceil(-123.456, 4));
		 * eq(floatval(0.01235),MathUtil::ceil(0.0123456, 4));
		 * eq(floatval(-0.01234),MathUtil::ceil(-0.0123456, 4));
		 * eq(floatval(1.235e12),MathUtil::ceil(1.23456e12, 4));
		 * eq(floatval(-1.234e12),MathUtil::ceil(-1.23456e12, 4));
		 */
		if (!is_numeric($value) || !is_numeric($sig) || $value == 0 || $sig < 0) {
			return floatval(0);
		}
		$d = MathUtil::digit($value);
		$d += ($d < 0) ? 1 : 0;
		$d = -1 * ($d - intval($sig));
		$r = ceil($value * pow(10, $d)) / pow(10, $d);
		return ($r == 0) ? floatval(0) : $r;
	}
	
	/**
	 * 数値の桁数を取得する
	 * 
	 * 数値の絶対値が1以上の場合には桁数を正の値で返す。
	 * 数値の絶対値が1未満の場合には最初に0以外の数値が現れる小数点以下の桁数を負の値で返す。
	 * 
	 * @param float $value
	 * @return integer
	 */
	function digit($value){
		/***
		 * eq(0,MathUtil::digit(0));
		 * eq(1,MathUtil::digit(1));
		 * eq(1,MathUtil::digit(9.9999999));
		 * eq(2,MathUtil::digit(10));
		 * eq(10,MathUtil::digit(0.12345e10));
		 * eq(-1,MathUtil::digit(0.9999999));
		 * eq(-1,MathUtil::digit(0.1));
		 * eq(-2,MathUtil::digit(0.0999999));
		 * eq(-2,MathUtil::digit(0.01));
		 * eq(-10,MathUtil::digit(1.2345e-10));
		 * eq(3,MathUtil::digit(-100));
		 * eq(-2,MathUtil::digit(-0.01));
		 */
		if(!is_numeric($value) || $value == 0){
			return 0;
		}
		$value = abs($value);
		$value = ($value >= 1) ? log10($value) + 1 : log10($value);
		return intval(floor($value));
	}
	
	/**
	 * ピタゴラスの定理[三平方の定理]
	 *
	 * @param float $x1
	 * @param float $y1
	 * @param float $x2
	 * @param float $y2
	 * @param int $precision
	 * @return float
	 */
	function pythagoras($x1,$y1,$x2,$y2,$precision=null){
		/***
		 * eq(102.116143833,MathUtil::pythagoras(136.862824,35.674167,34.851612,31.046051,9));
		 */
		$result = (float)sqrt(pow((float)$x2 - (float)$x1,2) + pow((float)$y2 - (float)$y1,2));
		if($precision !== null) $result = round($result,$precision);
		return $result;
	}
	
	/**
	 * 数値を分割する
	 *
	 * @param number $number
	 * @param number $x
	 * @param int $precision
	 * @return array
	 */
	function slice($number,$x,$precision=0){
		/***
		 * $result = MathUtil::slice(100.13,9,3);
		 * eq(9,count($result));
		 * eq(11.126,$result[0]);
		 * eq(11.126,$result[8]);
		 * 
		 * $result = MathUtil::slice(100.13,9);
		 * eq(10,count($result));
		 * eq((float)11,$result[0]);
		 * eq(1.13,$result[9]);
		 * 
		 */
		$result = array();
		$r = round($number / $x,$precision);
		for($i=0;$i<$x;$i++){
			$result[] = (float)$r;
			$number -= $r;
		}
		if($number > 0) $result[] = (float)$number;
		return $result;
	}
}
?>