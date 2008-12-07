<?php
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotSupportedException");
Rhaco::import("database.DbUtil");
Rhaco::import("lang.Variable");
/**
 * database.DBUtilの拡張
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilInitializer extends DbUtil{
	/**
	 * コンストラクタ
	 *
	 * @param database.model.DbConnection $dbConnection
	 * @param boolean $new
	 * @return ExtDbUtil
	 */
	function DbUtilInitializer($dbConnection){
		if(!Variable::istype("DbConnection",$dbConnection)) $dbConnection = new DbConnection($dbConnection);
		$this->base = $dbConnection->initializer();

		if($this->base == null){
			ExceptionTrigger::raise(new NotSupportedException(Message::_("database controler [{1}]",$dbConnection->type)));
		}else{
			$this->_open($dbConnection);
		}
	}			
	
	/**
	 * create database処理用SQLを返す
	 *
	 * @param setup.model.Databasemodel $databaseModel
	 * @return unknown
	 */
	function forward($databaseModel){
		/*** #pass */		
		if($this->base != null){
			return $this->base->forward($databaseModel);
		}
		return "";
	}
}
?>