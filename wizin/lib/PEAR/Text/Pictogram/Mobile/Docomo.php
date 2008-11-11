<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   Text
 * @package    Text_Pictogram_Mobile
 * @author     Daichi Kamemoto <daichi@asial.co.jp>
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @since      File available since Release 0.0.1
 */

require_once 'Text/Pictogram/Mobile/Common.php';

/**
 * Docomo
 * 
 * @package Text_Pictogram_Mobile
 * @author  Daichi Kamemoto <daichi@asial.co.jp>
 */

class Text_Pictogram_Mobile_Docomo extends Text_Pictogram_Mobile_Common
{
	public function __construct($type = 'sjis', $db_dir = null)
	{
		$this->carrier = 'docomo';
		$this->setPictogramType($type);

		switch ($type) {
			case 'sjis':
				$encode = 'sjis-win';
				break;
			case 'utf-8':
				$encode = 'UTF-8';
				break;
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

	protected function toBinary($unpackedChar)
	{
		return pack('H*', $unpackedChar);
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
			case "utf-8":
				$result = $this->_convertUTF8($inputString);
				break;
			default:
				$result = $inputString;
				break;
		}

		return $result;
	}
}
