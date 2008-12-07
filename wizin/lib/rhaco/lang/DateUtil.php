<?php
/**
 * 日付関係ユーティリティ
 * PHP日付関数の仕様により1970-2069年の間のみ対応
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DateUtil{
	/**
	 * 指定した日時を加算したタイムスタンプを取得
	 *
	 * @param int $time
	 * @param int $seconds
	 * @param int $minutes
	 * @param int $hours
	 * @param int $day
	 * @param int $month
	 * @param int $year
	 * @return int
	 */
	function add($time,$seconds=0,$minutes=0,$hours=0,$day=0,$month=0,$year=0){
		/***
		 * eq(time()+1,DateUtil::add(time(),1,0));
		 * eq(time()+60,DateUtil::add(time(),0,1));
		 * eq(time()+3600,DateUtil::add(time(),0,0,1));
		 * eq(time()-1,DateUtil::add(time(),-1,0));
		 * eq(time()-60,DateUtil::add(time(),0,-1));
		 * eq(time()-3600,DateUtil::add(time(),0,0,-1));
		 * 
		 */
		$dateList = getdate(intval(DateUtil::parse($time)));
		return mktime($dateList["hours"] + $hours,
						$dateList["minutes"] + $minutes,
						$dateList["seconds"] + $seconds,
						$dateList["mon"] + $month,
						$dateList["mday"] + $day,
						$dateList["year"] + $year
					);					
	}

	/**
	 * 日を加算する
	 * 
	 *
	 * @param int $time
	 * @param int $int
	 * @return unknown
	 */
	function addDay($time,$add){
		/***
		 * 	$time = time();
		 * 	eq(date("Y-m-d H:i:s",$time+(3600*24)),date("Y-m-d H:i:s",DateUtil::addDay($time,1)));
		 * 	eq(date("Y-m-d H:i:s",$time-(3600*24)),date("Y-m-d H:i:s",DateUtil::addDay($time,-1)));
		*/
		return DateUtil::add($time,0,0,0,$add);
	}
	
	/**
	 * 時を加算する
	 *
	 * @param int $time
	 * @param int $add
	 * @return int
	 */
	function addHour($time,$add){
		/***
			$time = time();
			eq(date("Y-m-d H:i:s",$time+3600),date("Y-m-d H:i:s",DateUtil::addHour($time,1)));
			eq(date("Y-m-d H:i:s",$time-3600),date("Y-m-d H:i:s",DateUtil::addHour($time,-1)));
		*/
		return DateUtil::add($time,0,0,$add);
	}
	
	/**
	 * 日付文字列からタイムスタンプを取得する
	 *
	 * @param string $str
	 * @return int
	 */
	function parseString($str){
		/***
		 * //1970年以前はnullになる
			eq(null,DateUtil::parseString("1969-12-31 23:59:59+00:00"));
			eq("1976-07-23 09:00:00",date("Y-m-d H:i:s",DateUtil::parseString("1976-07-23 05:00:00+05:00")));
			eq("2005-06-21 16:21:00",date("Y-m-d H:i:s",DateUtil::parseString("2005-06-21 16:21:00")));
			eq("1976-10-04 09:00:00",date("Y-m-d H:i:s",DateUtil::parseString("1976-10-04T00:00:00Z")));
			eq("1976-10-04 09:00:00",date("Y-m-d H:i:s",DateUtil::parseString("1976-10-04 00:00:00 UTC")));
			eq("2005-08-15 01:01:01",date("Y-m-d H:i:s",DateUtil::parseString("Mon, 15 Aug 2005 01:01:01")));
			eq("2007-07-18 18:02:22",date("Y-m-d H:i:s",DateUtil::parseString("2007-07-18T09:02:22+00:00")));

			eq("2005-08-15 09:52:01",date("Y-m-d H:i:s",DateUtil::parseString("2005-08-15T01:52:01+0100")));
			eq("2005-08-15 10:01:01",date("Y-m-d H:i:s",DateUtil::parseString(" Monday, 15-Aug-05 01:01:01 UTC")));
			eq("2005-08-15 10:01:01",date("Y-m-d H:i:s",DateUtil::parseString("Monday, 15-Aug-05 01:01:01 UTC")));

			eq("2005-08-15 10:01:01",date("Y-m-d H:i:s",DateUtil::parseString("Mon, 15 Aug 2005 01:01:01 UTC")));
			eq("2006-08-15 09:01:01",date("Y-m-d H:i:s",DateUtil::parseString("Tue, 15 Aug 2006 01:01:01 +0100")));
			eq("2005-08-15 10:01:01",date("Y-m-d H:i:s",DateUtil::parseString("Mon, 15 Aug 2005 01:01:01 UTC")));
			eq("2005-08-15 09:01:01",date("Y-m-d H:i:s",DateUtil::parseString("2005-08-15T01:01:01+01:00")));

			eq(null,DateUtil::parseString(null));
			eq(null,DateUtil::parseString(0));
			eq(null,DateUtil::parseString(""));
		*/
		$time = null;
		$ztime = 0;		
		if(!empty($str)){
			if(!is_numeric($str)){
				$str		= trim($str);
				$zonetime	= 0;

				if(preg_match("/(.+)([\+\-])(\d\d)[\:]*(\d\d)$/",$str,$tmp)){
					$zonetime	= (intval($tmp[3]) * 3600) + (intval($tmp[4]) * 60);
					$zonetime	= ($tmp[2] == "-") ? $zonetime * -1 : $zonetime;
					$str		= $tmp[1];
					$ztime		= date("Z");
				}else if(!(strrpos($str,"Z") === false)){
					$ztime	= date("Z");
				}
				if(preg_match("/(UTC)|(Jan)|(Feb)|(Mar)|(Apr)|(May)|(Jun)|(Jul)|(Aug)|(Sep)|(Oct)|(Nov)|(Dec)/i",$str)){
					$time = strtotime($str) - $zonetime;
				}else{
					$str	= preg_replace("/[^0-9]/","",$str);
					if($str == 0 || substr($str,0,3) == "000")	return null;
					$time	= mktime(substr($str,8,2),substr($str,10,2),substr($str,12,2),substr($str,4,2),substr($str,6,2),substr($str,0,4)) - $zonetime;
				}
			}else{
				$time = intval($str);
			}
		}
		if($time !== false) $time = $time + $ztime;
		return ($time !== false && $time > 0) ? $time : null;
	}
	
	/**
	 * 日付文字列からタイムスタンプを取得する
	 *
	 * @param string $str
	 * @return int
	 */
	function parse($str){
		/***
		 * eq(null,DateUtil::parse("1960-07-23 05:00:00+05:00"));
		 * eq("1976-07-23 09:00:00",date("Y-m-d H:i:s",DateUtil::parse("1976-07-23 05:00:00+05:00")));
		 * eq("2005-08-15 09:52:01",date("Y-m-d H:i:s",DateUtil::parse("2005-08-15T01:52:01+0100")));
		 * eq("2005-08-15 10:01:01",date("Y-m-d H:i:s",DateUtil::parse("Mon, 15 Aug 2005 01:01:01 UTC")));
		 * eq(null,DateUtil::parse(null));
		 * eq(null,DateUtil::parse(0));
		 * eq(null,DateUtil::parse(""));
		 * eq("2005-03-02 00:00:00",date("Y-m-d H:i:s",DateUtil::parse("2005/02/30 00:00:00")));
		*/
		return DateUtil::parseString($str);
	}

	/**
	 * 時間文字列からタイムスタンプを取得する
	 *
	 * @param string $str
	 * @return int
	 */
	function parseTime($str){
		/***
		 * eq(3661,DateUtil::parseTime("01:01:01"));
		 * eq(3661,DateUtil::parseTime("1:1:1"));
		 * eq(61,DateUtil::parseTime("0:1:1"));
		 * eq(null,DateUtil::parseTime("0/1/1"));
		 */
		if(preg_match("/^(\d+):(\d+):(\d+)$/",$str,$match)) $str = (intval($match[1]) * 3600) + (intval($match[2]) * 60) + intval($match[3]);
		if(preg_match("/[^\d]/",$str)) return null;
		return ($str > 0) ? $str : null;
	}
	
	/**
	 * 日付文字列からintdateを取得する
	 *
	 * @param string $str
	 * @return int
	 */
	function parseIntDate($str){
		/***
		 * eq(20080401,DateUtil::parseIntDate("2008/04/01"));
		 * eq(20080401,DateUtil::parseIntDate("2008-04-01"));
		 * eq(20080401,DateUtil::parseIntDate("2008-04/01"));
		 * eq(20080401,DateUtil::parseIntDate("2008-4-1"));
		 * eq(2080401,DateUtil::parseIntDate("2080401"));
		 * eq(null,DateUtil::parseIntDate("2008A04A01"));
		 * eq(intval(date("Ymd")),DateUtil::parseIntDate(time()));
		 * eq(19000401,DateUtil::parseIntDate("1900-4-1"));
		 * eq(19001010,DateUtil::parseIntDate("1900/10/10"));
		 * eq(10101,DateUtil::parseIntDate("1/1/1"));
		 * eq(19601110,DateUtil::parseIntDate("1960/11/10"));
		 */
		if(preg_match("/[^\d\/\-]/",$str)) return null;
		if(strlen(preg_replace("/[^\d]/","",$str)) > 8) $str = DateUtil::format($str,"Y/m/d");
		if(preg_match("/^(\d+)[^\d](\d+)[^\d](\d+)$/",$str,$match)) $str = sprintf("%d%02d%02d",intval($match[1]),intval($match[2]),intval($match[3]));
		return ($str > 0) ? intval($str) : null;
	}
	
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function format($time,$format=""){
		/***
		 * eq("2007/07/19",DateUtil::format("2007-07-18T16:16:31+00:00","Y/m/d"));
		 */
		$format = str_replace(array("YYYY","MM","DD"),array("Y","m","d"),$format);
		$time = DateUtil::parseString($time);
		if(empty($time)) return "";
		if(empty($format)) $format = "Y/m/d H:i:s";
		return date($format,DateUtil::parseString($time));
	}
	
	/**
	 * 整形された時間文字列を取得
	 *
	 * @param int $time
	 * @return string
	 */
	function formatTime($time){
		/***
		 * eq("01:01:01",DateUtil::formatTime(3661));
		 * eq("00:01:01",DateUtil::formatTime(61));
		 * eq("300:01:01",DateUtil::formatTime(1080061));
		 */
		$time = DateUtil::parseTime($time);
		if(empty($time)) return "";
		return sprintf("%02d:%02d:%02d",intval($time/3600),intval(($time%3600)/60),intval(($time%3600)%60));
	}
	
	/**
	 * 整形された日付文字列を取得
	 *
	 * @param int $intdate
	 * @return string
	 */
	function formatDate($intdate){
		/***
		 * eq("2008/04/07",DateUtil::formatDate(20080407));
		 * eq("208/04/07",DateUtil::formatDate(2080407));
		 */
		$date = DateUtil::parseIntDate($intdate);
		if(preg_match("/^([\d]+)([\d]{2})([\d]{2})$/",$date,$match)) return sprintf("%d/%02d/%02d",$match[1],$match[2],$match[3]);
		return "";
	}
	
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatFull($time){
		/***
		 *  eq("2007/07/19 01:16:31 (Thu)",DateUtil::formatFull(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"Y/m/d H:i:s (D)");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatAtom($time){
		/***
		 * eq("2007-07-19T01:16:31Z",DateUtil::formatAtom(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"Y-m-d\TH:i:s\Z");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatCookie($time){
		/***
		 * eq("Thu, 19 Jul 2007 01:16:31 JST",DateUtil::formatCookie(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"D, d M Y H:i:s T");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatISO8601($time){
		/***
		 * eq("2007-07-19T01:16:31+0900",DateUtil::formatISO8601(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"Y-m-d\TH:i:sO");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatRFC822($time){
		/***
		 * eq("Thu, 19 Jul 2007 01:16:31 JST",DateUtil::formatRFC822(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"D, d M Y H:i:s T");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatRFC850($time){
		/***
		 * eq("Thursday, 19-Jul-07 01:16:31 JST",DateUtil::formatRFC850(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"l, d-M-y H:i:s T");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatRFC1036($time){
		/***
		 * eq("Thursday, 19-Jul-07 01:16:31 JST",DateUtil::formatRFC1036(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"l, d-M-y H:i:s T");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatRFC1123($time){
		/***
		 * eq("Thu, 19 Jul 2007 01:16:31 JST",DateUtil::formatRFC1123(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"D, d M Y H:i:s T");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatRfc2822($time){
		/***
		 * eq("Thu, 19 Jul 2007 01:16:31 +0900",DateUtil::formatRfc2822(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"D, d M Y H:i:s O");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatRss($time){
		/***
		 * eq("Thu, 19 Jul 2007 01:16:31 JST",DateUtil::formatRss(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		return DateUtil::format($time,"D, d M Y H:i:s T");
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @param string $format
	 * @return unknown
	 */
	function formatW3C($time){
		/***
		 * eq("2007-07-19T01:16:31+09:00",DateUtil::formatW3C(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		 */
		$time = DateUtil::parseString($time);
		if($time === null) return "";
		$tzd = date("O",$time);
		$tzd = $tzd[0].substr($tzd,1,2).":".substr($tzd,3,2);
		return DateUtil::format($time,"Y-m-d\TH:i:s").$tzd;
	}
	/**
	 * 日付書式にフォーマットした文字列を取得
	 *
	 * @param int $time
	 * @return string
	 */
	function formatPDF($time){
		/***
		* eq("D:20070719011631+09'00'",DateUtil::formatPDF(DateUtil::parseString("2007-07-18T16:16:31+00:00")));
		*/
		$tzd = date("O",$time);
		$tzd = $tzd[0].substr($tzd,1,2)."'".substr($tzd,3,2)."'";
		return "D:".DateUtil::format($time,"YmdHis").$tzd;
	}
	
	/**
	 * 日付比較 ==
	 *
	 * @param unknown_type $timestampA
	 * @param unknown_type $timestampB
	 * @return unknown
	 */
	function eq($timestampA,$timestampB){
		/***
		 * assert(DateUtil::eq("2008/03/31","2008/03/31"));
		 * assert(!DateUtil::eq("2008/03/31","2008/03/30"));
		 */
		return DateUtil::parse($timestampA) == DateUtil::parse($timestampB);
	}
	/**
	 * 日付比較 >
	 *
	 * @param unknown_type $timestampA
	 * @param unknown_type $timestampB
	 * @return unknown
	 */
	function gt($timestampA,$timestampB){
		/***
		 * assert(DateUtil::gt("2008/03/31","2008/03/30"));
		 * assert(!DateUtil::gt("2008/03/30","2008/03/31"));
		 * assert(!DateUtil::gt("2008/03/31","2008/03/31"));
		 */
		return DateUtil::parse($timestampA) > DateUtil::parse($timestampB);
	}
	
	/**
	 * 日付比較 >=
	 *
	 * @param unknown_type $timestampA
	 * @param unknown_type $timestampB
	 * @return unknown
	 */
	function gte($timestampA,$timestampB){
		/***
		 * assert(DateUtil::gte("2008/03/31","2008/03/30"));
		 * assert(!DateUtil::gte("2008/03/30","2008/03/31"));
		 * assert(DateUtil::gte("2008/03/31","2008/03/31"));
		 */
		return DateUtil::parse($timestampA) >= DateUtil::parse($timestampB);
	}
	
	/**
	 * 年齢の算出
	 *
	 * @param int $intdate
	 * @param int $time
	 * @return unknown
	 */
	function age($intdate,$time=null){
		/***
		 * eq(5,DateUtil::age(20001010,DateUtil::parse("2005/01/01")));
		 * eq(6,DateUtil::age(20001010,DateUtil::parse("2005/10/10")));
		 * eq(5,DateUtil::age(20001010,DateUtil::parse("2005/10/9")));
		 * eq(5,DateUtil::age(20001010,DateUtil::parse("2005/10/11")));
		 */
		if($time === null) $time = time();
		$intdate = intval(preg_replace("/[^\d]/","",$intdate));
		$a = intval(substr(DateUtil::format($time,"Ymd"),0,-4)) - intval(substr($intdate,0,-4));
		if(DateUtil::gte(DateUtil::parse("2000".substr($intdate,-4)),DateUtil::parse("2000".substr($time,-4)))) $a += 1;
		return $a;
	}
}
?>