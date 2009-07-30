<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

require_once 'Text/Pictogram/Mobile/Common.php';

/**
 * Text_Pictogram_Mobile_Ezweb
 * 
 * @category Text
 * @package  Text_Pictogram_Mobile
 * @author   Daichi Kamemoto <daikame@gmail.com>
 */
class Text_Pictogram_Mobile_Ezweb extends Text_Pictogram_Mobile_Common
{
	public function __construct($type = 'sjis', $db_dir = null)
	{
		$this->carrier = 'ezweb';
		$this->setPictogramType($type);
		switch ($type) {
			case 'sjis':
				$encode = 'sjis-win';
				break;
			case 'utf-8':
				$encode = 'UTF-8';
				break;
			case 'jis-email':
				$encode = 'ISO-2022-JP';
			default:
				$encode = 'sjis-win';
				break;
		}

		$this->characterEncode = $encode;

		$this->initialize($db_dir);
	}

	protected function toIntercodeUnpacked($unpackedChar)
	{
		if ($this->isPictogramUnpacked($unpackedChar) === false) {
			$return = pack('H*', $unpackedChar);
		} else {
			$return = $this->getIntercodePrefix() . " " . $this->getCarrier() . " " . $this->getPictogramNumber($unpackedChar) . " " .  $this->getIntercodeSuffix();
		}

		return $return;
	}
	
	public function toIntercode($char)
	{
		return $this->toIntercodeUnpacked(strtoupper(bin2hex($char)));
	}

	public function isPictogram($char)
	{
		return $this->isPictogramUnpacked(strtoupper(bin2hex($char)));
	}

	public function toBinary($unpackedChar)
	{
		switch ($this->getPictogramType()) {
			case "jis-email":
				$unpackedChar = '1B2442' . $unpackedChar . '1B2842';
				break;
			default:
				break;
		}
		$result = pack('H*', $unpackedChar);

		return $result;
	}

	/**
	 * 入力した文字列中の絵文字全部内部コードに置き換える。
	 */
	public function convert($inputString)
	{
		$inputString = $this->escapeString($inputString);
		switch ($this->getPictogramType()) {
			case "sjis":
				$result = $this->_convertSJIS($inputString);
				break;
			case "jis-email":
				$result = $this->_convertJIS($inputString);
				break;
			case "utf-8":
				$result = $this->_convertUTF8($inputString);
				break;
			default:
				$result = $inputString;
				break;
		}

		return $result;
	}

	public function _convertJIS($inputString)
	{
		if (!strlen($inputString)) return $inputString;

		// バイナリにして、ISO-2022-JPの日本語部分とそれ以外に分ける
		// 0x1b 0x24 0x42 の文頭3バイトと、0x1b 0x28 0x42の文末3バイトをセパレータに。
		$binaryString = array_shift(unpack('H*', $inputString));

		$iso_pattern = "/1B2442((.*))1B2842/mUi";
		$binarySplitArray = preg_split($iso_pattern, $binaryString, -1, PREG_SPLIT_DELIM_CAPTURE);

		$convertString = '';
		for ($i = 0; $i < count($binarySplitArray); $i++) {
			if (isset($binarySplitArray[$i+1])
				&& ($binarySplitArray[$i] == $binarySplitArray[$i+1])) {
				// SJISじゃないけど、仕組みが丸のまま同じなのでこっちに放り投げる。差異はtoBinaryで吸収。
				$convertString .= $this->_convertSJIS(pack('H*', $binarySplitArray[$i]));
				++$i;
			} else {
				$convertString .= pack('H*', $binarySplitArray[$i]);
			}
		}

		return $convertString;
	}
}
