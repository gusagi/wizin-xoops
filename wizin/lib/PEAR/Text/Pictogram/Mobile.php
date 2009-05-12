<?php
/*
 * Text_Pictogram_Mobile - 携帯絵文字処理クラス
 *
 * @category   Text
 * @package    Text_Pictogram_Mobile
 * @author     Daichi Kamemoto <daikame@gmail.com>
 * @license    MIT License
 * @since      File available since Release 0.0.3
 */

/**
 * The MIT License
 *
 * Copyright (c) 2008 Daichi Kamemoto <daikame@gmail.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
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
