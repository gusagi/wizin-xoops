<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {smarty_function_wizin_inputmode} function plugin
 *
 * Type:	 function<br>
 * Name:	 wizin_inputmode<br>
 * Date:	 October 23, 2008<br>
 * Purpose:  return input mode string
 * @link http://www.gusagi.com
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @version  1.0
 * @param array
 * @param Smarty
 * @return string return input mode for mobile in japan
 */
function smarty_function_wizin_inputmode($params, &$smarty)
{
	if ( ! isset($params['mode']) || ! class_exists('Wizin_User')) {
		return '';
	}
	$mode = $params['mode'];
	$user =& Wizin_User::getSingleton();
	$user->checkClient();
	$inputMode = '';
	if ( isset($user->aInputMode[$mode]) ) {
		$inputMode = $user->aInputMode[$mode];
	}
	return $inputMode;
}
