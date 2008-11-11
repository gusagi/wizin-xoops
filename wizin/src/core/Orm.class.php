<?php
/**
 * Wizin framework O/R Mapper "Propel" wrapper class
 *
 * PHP Version 5.2 or Upper version
 *
 * @package  Wizin
 * @author  Makoto Hashiguchi a.k.a. gusagi<gusagi@gusagi.com>
 * @copyright 2008 Makoto Hashiguchi
 * @license http://www.opensource.org/licenses/bsd-license.php The BSD License
 *
 */

if ( ! class_exists('Wizin_Core_Orm') ) {
	require dirname( dirname(__FILE__) ) . '/Wizin.class.php';
	/**
	 * Wizin framework core O/R Mapper class
	 *
	 */
	abstract class Wizin_Core_Orm
	{
		abstract static public function init();

		abstract static public function strpos();
	}
}
