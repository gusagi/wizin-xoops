<?php
Rhaco::import("database.DbUtilBase");
Rhaco::import("io.FileUtil");
Rhaco::import("lang.Env");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.PermissionException");
/**
 * #ignore
 * SQLite用 databse操作クラス
 * 
 * 大文字小文字の区別やLIKE時の%や_のエスケープに非対応 (SQLite2)
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilSQLite extends DbUtilBase{
	var $name = "SQLite";
	
	/**
	 * 使用可能か
	 *
	 * @return unknown
	 */
	function _valid(){
		return extension_loaded("SQLite");
	}	
	/**
	 * データベースをオープンする
	 * @param databse.model.DbConnection $dbConnection
	 * @return boolean
	 */	
	function _open($dbConnection){
		if($this->valid()){
			$this->dbConnection = $dbConnection;
				
			if(!FileUtil::mkdir(dirname($dbConnection->host))){
				ExceptionTrigger::raise(new PermissionException($dbConnection->host));
				return false;
			}
			if($dbConnection->new){
				if($this->transaction && Rhaco::isVariable("RHACO_DATABASE_SQLITE_CONNECTION",$this->dbConnection->host)){
					$this->connection = Rhaco::getVariable("RHACO_DATABASE_SQLITE_CONNECTION",null,$this->dbConnection->host);
					$this->close();
				}
				$this->connection = sqlite_open($dbConnection->host,0666);
			}else{
				$this->connection = sqlite_popen($dbConnection->host,0666);
			}
			if($this->connection != false){
				if($this->transaction) Rhaco::addVariable("RHACO_DATABASE_SQLITE_CONNECTION",$this->connection,$this->dbConnection->host);
				sqlite_busy_timeout($this->connection,1000);
				$this->_debug_open();
				$this->trans(true);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * データベースをクローズする
	 */
	function _close(){
		if($this->connection){
			$this->_commit();
			@sqlite_close($this->connection);
			if($this->transaction) Rhaco::clearVariable("RHACO_DATABASE_SQLITE_CONNECTION",$this->dbConnection->host);
			$this->_debug_close();
		}
		$this->connection = false;
	}

	function _query($sql){
		$errormsg = "";
		$errono = 0;
		
		$this->resourceId = Env::isphp("5.1.0") ? 
									@sqlite_query($this->connection,$sql,SQLITE_BOTH,$errormsg) :
									@sqlite_query($this->connection,$sql,SQLITE_BOTH);
		return array($errono,(($this->resourceId === false) ? $errormsg : ""));
	}
	
	function _droptable($tableObject){
		if(Variable::istype("TableObjectBase",$tableObject)){
			foreach($tableObject->columns() as $columnObject){
				$this->query("drop table ".$columnObject->sqltablename());
				break;
			}
		}
	}
	
	/**
	 * 結果セットを返す
	 */
	function _resultset(){
		if($this->resourceId != false){
			return sqlite_fetch_array($this->resourceId,SQLITE_ASSOC);
		}
		return false;
	}
	
	/**
	 * 結果セットを空にする
	 */
	function _free(){
		if($this->resourceId != false){
			$this->resourceId = false;
		}
	}
	function _generateSelectToDate($value,$column){
		return (empty($value)) ? "NULL" : sprintf("'%s'",DateUtil::format($value));
	}
	function _insertId($tableObject){
		unset($tableObject);
		return sqlite_last_insert_rowid($this->connection);
	}
	function _escape($value){
		if(is_object($value) || is_array($value))	return "";
		return (extension_loaded("SQLite")) ? sqlite_escape_string($value) : addslashes($value);
	}
	function _whereLikeEscape($value){
		return str_replace(array("\.",".*",".","__C__"),array("__C__","%","_","."),$value);
	}
}
?>