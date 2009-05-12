<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

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
