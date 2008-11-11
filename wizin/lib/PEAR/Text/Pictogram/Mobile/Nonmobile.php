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

class Text_Pictogram_Mobile_Nonmobile extends Text_Pictogram_Mobile_Common
{

	public function __construct()
	{
		$this->getEscapeSequence('&');
		$this->setIntercodePrefix('[({');
		$this->setIntercodeSuffix('})]');
	}

	protected function toIntercodeUnpacked($unpackedChar)
	{
		return $unpackedChar;
	}

	public function toIntercode($char)
	{
		return $char;
	}

	public function isPictogram($char)
	{
		return $char;
	}

	public function getFormattedPictogramsArray($carrier = null)
	{
		return array();
	}

	public function convert($inputString)
	{
		return $inputString;
	}

	public function replace($inputString)
	{
		return $inputString;
	}

	public function restore($inputString)
	{
		$pattern = '/' . preg_quote($this->getIntercodePrefix(), '/') . '(.*)' . preg_quote($this->getIntercodeSuffix(), '/') . '/mUs';
		$restoreString = preg_replace($pattern, '', $inputString);

		return $restoreString;
	}
}
