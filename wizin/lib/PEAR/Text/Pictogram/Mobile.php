<?php
/**
 * Text_Pictogram_Mobile - 携帯絵文字処理クラス
 *
 * @package Text_Pictogram_Mobile
 * @author Daichi Kamemoto <daichi@asial.co.jp>
 * @version 0.0.1
 */

require_once 'Text/Pictogram/Mobile/Exception.php';

class Text_Pictogram_Mobile {

	public static function factory($agent = null, $type = 'sjis')
	{

		if (isset($agent) && $agent != "") {
			switch (strtolower($agent)) {
				case 'docomo':
				case 'imode':
				case 'i-mode':
					$agent = 'docomo';
					break;
				case 'ezweb':
				case 'au':
				case 'kddi':
					$agent = 'ezweb';
					break;
				case 'disney':
				case 'softbank':
				case 'vodafone':
				case 'jphone':
				case 'j-phone':
					$agent = 'softbank';
					break;
				default:
					$agent = 'nonmobile';
			}
			$agent = ucfirst(strtolower($agent));
		} else {
			$agent = 'Nonmobile';
		}

		$className = "Text_Pictogram_Mobile_{$agent}";
		if (!class_exists($className)) {
			$file = str_replace('_', '/', $className) . '.php';
			if (!include_once $file) {
				throw new Text_Pictogram_Mobile_Exception('Class file not found:' . $file);
			}
		}

		$instance = new $className($type);
		return $instance;
	}
}
?>
