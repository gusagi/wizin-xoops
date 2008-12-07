<?php
Rhaco::import("lang.StringUtil");
Rhaco::import("io.model.File");
/**
 * メール送信に関する情報を制御する
 * 
 * @author Kazutaka Tokushima
 * @author Kentaro YABE
 * @license New BSD License
 */
class Mail{
	var $subject = "";
	var $plain = "";
	var $html = "";
	var $attach = array();
	var $image = array();
	var $encode = "jis";
	var $boundary = array("mixed"=>"mixed","alternative"=>"alternative","related"=>"related");

	var $to = array();
	var $cc = array();
	var $bcc = array();

	var $from = "";
	var $name = "";
	var $returnpath = "";

	var $eol = "\n";
	
	/**
	 * コンストラクタ
	 *
	 * @param string $mail 送信者メールアドレス
	 * @param string $name 送信者名
	 * @param boolean $eol true CRLF/false LF
	 * @return Mail
	 */
	function Mail($mail="",$name="",$eol=false){
		$this->__init__($mail,$name,$eol);
	}
	function __init__($mail="",$name="",$eol=false){
		$this->eol = ($eol) ? "\r\n" : "\n";
		$this->from = $mail;
		$this->name = $name;
		$this->returnpath = $mail;
		$this->boundary = array("mixed"=>"----=_Part_".uniqid("mixed"),"alternative"=>"----=_Part_".uniqid("alternative"),"related"=>"----=_Part_".uniqid("related"));		
	}
	
	/**
	 * 宛先を追加する
	 * 
	 * @param string $mail メールアドレス
	 * @param string $name 名前
	 */
	function to($mail,$name=""){
		/*** unit("network.mail.MailTest"); */
		$this->to[$mail] = $this->_addr($mail,$name);
	}
	
	/**
	 * CCの宛先を追加する
	 * 
	 * @param string $mail
	 * @param string $name
	 */
	function cc($mail,$name=""){
		/*** unit("network.mail.MailTest"); */
		$this->cc[$mail] = $this->_addr($mail,$name);
	}
	
	/**
	 * BCCの宛先を追加する
	 * 
	 * @param string $mail
	 * @param string $name
	 */
	function bcc($mail,$name=""){
		/*** unit("network.mail.MailTest"); */
		$this->bcc[$mail] = $this->_addr($mail,$name);
	}
	
	/**
	 * Return-Pathを設定する
	 * 
	 * @param string $mail
	 */
	function returnpath($mail){
		/*** unit("network.mail.MailTest"); */
		$this->returnpath = $mail;
	}
	
	/**
	 * メールの題名を設定する
	 * 
	 * @param string $subject
	 */
	function subject($subject){
		/***
		 * $mail = new Mail;
		 * $mail->subject("改行は\r\n削除される");
		 * eq("改行は削除される", $mail->subject);
		 */
		$this->subject = str_replace("\n","",StringUtil::toULD($subject));
	}
	
	/**
	 * テキストメール本文を設定する
	 * 
	 * @param string $message
	 */
	function message($message){
		/*** unit("network.mail.MailTest"); */
		$this->plain = $this->_encode($message);
	}
	
	/**
	 * HTMLメール本文を設定する
	 * 
	 * @param string $message
	 */
	function html($message){
		/*** unit("network.mail.MailTest"); */
		$this->html = $this->_encode($message);
		if(empty($this->plain)) $this->message(strip_tags($message));
	}
	
	/**
	 * ファイルを添付する
	 * 
	 * @param string $filename
	 * @param binary $src
	 * @param string $type
	 */
	function attach($filename,$src,$type="application/octet-stream"){
		/*** unit("network.mail.MailTest"); */
		$this->attach[] = array(new File($filename,$src),$type);
	}
	/**
	 * HTMLメール用の画像を追加する
	 * 
	 * @param string $filename
	 * @param binary $src
	 * @param string $type
	 */
	function image($filename,$src,$type="application/octet-stream"){
		/*** unit("network.mail.MailTest"); */
		$this->image[$filename] = array(new File($filename,$src),$type);
	}

	/**
	 * メールを送信する
	 * 
	 * @param string $subject
	 * @param string $message
	 * @return boolean
	 */
	function send($subject="",$message=""){
		if (count($this->to) == 0) return false;
		if(!empty($subject)) $this->subject($subject);
		if(!empty($message)) $this->message($message);
		return mail("",(empty($this->subject) ? "" : $this->_subject()),$this->_messages(),trim($this->_headers()));
	}
	
	/**
	 * メールの原稿を作成する
	 * 
	 * @param boolean $eol true CRLF/false LF
	 * @return string
	 */
	function manuscript($eol=true){
		/*** unit("network.mail.MailTest"); */
		$pre = $this->eol;
		$this->eol = ($eol) ? "\r\n" : "\n";
		$send = $this->_headers();
		$send .= $this->_lw();
		$send .= $this->_messages();
		$this->eol = $pre;
		return $send;
	}

	/**
	 * メールヘッダを取得
	 * 
	 * @return string
	 */
	function _headers(){
		/*** unit("network.mail.MailTest"); */
		$send = "";
		$send .= $this->_lw("MIME-Version: 1.0");
		$send .= $this->_lw("To: ".$this->_addrimp($this->to));
		$send .= $this->_lw("From: ".$this->_addr($this->from,$this->name));
		if(!empty($this->cc)) $send .= $this->_lw("Cc: ".$this->_addrimp($this->cc));
		if(!empty($this->bcc)) $send .= $this->_lw("Bcc: ".$this->_addrimp($this->bcc));
		if(!empty($this->returnpath)) $send .= $this->_lw("Return-Path: ".$this->returnpath);
		$send .= (empty($this->subject)) ? "" : $this->_lw("Subject: ".$this->_subject());
		
		if(!empty($this->attach)){
			$send .= $this->_lw(sprintf("Content-Type: multipart/mixed; boundary=\"%s\"",$this->boundary["mixed"]));
		}else if(!empty($this->html)){
			$send .= $this->_lw(sprintf("Content-Type: multipart/alternative; boundary=\"%s\"",$this->boundary["alternative"]));
		}else{
			$send .= $this->_meta("plain");
		}
		return $send;		
	}
	function _addrimp($list){
		return trim(implode(",".$this->eol." ",$list));
	}
	
	/**
	 * メールコンテンツを取得
	 * 
	 * @return string
	 */
	function _messages(){
		/*** unit("network.mail.MailTest"); */
		$send = "";
		$isattach = (!empty($this->attach));
		$ishtml = (!empty($this->html));

		if($isattach){
			$send .= $this->_lw("--".$this->boundary["mixed"]);

			if($ishtml){
				$send .= $this->_lw(sprintf("Content-Type: multipart/alternative; boundary=\"%s\"",$this->boundary["alternative"]));
				$send .= $this->_lw();
			}
		}
		$send .= (!$ishtml) ? (($isattach) ? $this->_meta("plain").$this->_lw() : "").$this->_lw($this->plain) : $this->_alternative();
		if($isattach){
			foreach($this->attach as $attach){
				$send .= $this->_lw("--".$this->boundary["mixed"]);
				$send .= $this->_attach($attach);
			}
			$send .= $this->_lw("--".$this->boundary["mixed"]."--");			
		}
		return $send;
	}
	
	/**
	 * HTMLメールとテキストメールの代替表現を取得
	 * 
	 * @return string
	 */
	function _alternative(){
		$send = "";
		$send .= $this->_lw("--".$this->boundary["alternative"]);
		$send .= $this->_meta("plain");
		$send .= $this->_lw();
		$send .= $this->_lw($this->_encode($this->plain));
		$send .= $this->_lw("--".$this->boundary["alternative"]);
		if(empty($this->image)) $send .= $this->_meta("html");
		$send .= $this->_lw($this->_encode((empty($this->image)) ? $this->_lw().$this->html : $this->_related()));
		$send .= $this->_lw("--".$this->boundary["alternative"]."--");
		return $send;
	}
	
	/**
	 * HTMLメールと画像コンテンツを取得
	 */
	function _related(){
		$send = $this->_lw().$this->html;
		$html = $this->html;
		foreach(array_keys($this->image) as $name){
			// tags
			$preg = '/(\s)(src|href)\s*=\s*(["\']?)' . preg_quote($name) . '\3/';
			$replace = sprintf('\1\2=\3cid:%s\3', $name);
			$html = StringUtil::replace($html, $preg, $replace, "i");
			// css
			$preg = '/url\(\s*(["\']?)' . preg_quote($name) . '\1\s*\)/';
			$replace = sprintf('url(\1cid:%s\1)', $name);
			$html = StringUtil::replace($html, $preg, $replace, "i");
		}		
		if($html != $this->html){
			$send = "";
			$send .= $this->_lw(sprintf("Content-Type: multipart/related; boundary=\"%s\"",$this->boundary["related"]));
			$send .= $this->_lw();
			$send .= $this->_lw("--".$this->boundary["related"]);
			$send .= $this->_meta("html");
			$send .= $this->_lw();
			$send .= $this->_lw($this->_encode($html));
			
			foreach($this->image as $image){
				$send .= $this->_lw("--".$this->boundary["related"]);
				$send .= $this->_attach($image,true);
			}
			$send .= $this->_lw("--".$this->boundary["related"]."--");
		}
		return $send;
	}
	
	/**
	 * エンコード済みSubjectを取得する。
	 * 
	 * @return string
	 */
	function _subject(){
		return $this->_toJis($this->subject);
	}
	
	/**
	 * エンコード済み文字列を取得する。
	 * 
	 * @return string
	 */
	function _toJis($str){
		if(preg_match("/^[\w- ]+$/i",$str)) return $str;
		return sprintf("=?ISO-2022-JP?B?%s?=",base64_encode(StringUtil::encode($str,StringUtil::JIS())));
	}
	
	/**
	 * テキストエンコードに沿ったメタ情報を取得する
	 *
	 * @param string $type
	 * @return string
	 */
	function _meta($type){
		switch(strtolower($type)){
			case "html": $type = "text/html"; break;
			default: $type = "text/plain";
		}
		switch($this->_enctype($this->encode)){
			case "utf8":
				return $this->_lw(sprintf("Content-Type: %s; charset=\"utf-8\"",$type)).
						$this->_lw("Content-Transfer-Encoding: 8bit");
			case "sjis":
				return $this->_lw(sprintf("Content-Type: %s; charset=\"iso-2022-jp\"",$type)).
						$this->_lw("Content-Transfer-Encoding: base64");
			default:
				return $this->_lw(sprintf("Content-Type: %s; charset=\"iso-2022-jp\"",$type)).
						$this->_lw("Content-Transfer-Encoding: 7bit");
		}
	}
	/**
	 * エンコードする
	 * 
	 * @param string $message
	 * @return string 
	 */
	function _encode($message){
		switch($this->_enctype($this->encode)){
			case "utf8": return StringUtil::encode($message,StringUtil::UTF8());
			case "sjis": return StringUtil::encode(base64_encode(StringUtil::encode($message,StringUtil::SJIS())),StringUtil::JIS());
			default: return StringUtil::encode($message,StringUtil::JIS());
		}
	}
	/**
	 * 正規化された行表現を取得する
	 * 
	 * @param string $value
	 */
	function _lw($value=""){
		return $value.$this->eol;
	}

	/**
	 * 正規化されたエンコーディング表記を取得する
	 * 
	 * @param string $type エンコーディング
	 * @return string
	 */
	function _enctype($type){
		switch(strtolower($type)){
			case "utf-8":
			case "utf8":
				return "utf8";
			case "sjis":
				return "sjis";
			default:
				return "jis";
		}
	}
	function _attach($list,$id=false){
		list($file,$type) = $list;
		$send = "";
		$send .= $this->_lw(sprintf("Content-Type: %s; name=\"%s\"",(empty($type) ? "application/octet-stream" : $type),$file->getName()));
		$send .= $this->_lw(sprintf("Content-Transfer-Encoding: base64"));
		if($id) $send .= $this->_lw(sprintf("Content-ID: <%s>", $file->getName()));
		$send .= $this->_lw();
		$send .= $this->_lw(trim(chunk_split(base64_encode($file->read()),76,$this->eol)));
		return $send;
	}
	/**
	 * RFC規程のユーザ記述形式を取得
	 * 
	 * @param string $mail
	 * @param string $name
	 */
	function _addr($mail,$name){
		return (empty($name) ? $mail : "\"".$this->_toJis($name)."\"")." <".$mail.">";
	}
}
?>