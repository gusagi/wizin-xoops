<?php
Rhaco::import("database.DbUtilBase");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
/**
 * #ignore
 * MySQL用 databse操作クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilMySQL extends DbUtilBase{
	var $mysqlvar = "040100";
	var $name = "MySQL";
	
	/**
	 * 使用可能か
	 *
	 * @return unknown
	 */
	function _valid(){
		return extension_loaded("mysql");
	}
	/**
	 * データベースをオープンする
	 * @param databse.model.DbConnection $dbConnection
	 * @return boolean
	 */	
	function _open($dbConnection){
		if($this->valid()){
			$host = sprintf("%s:3306",$dbConnection->host);

			if($dbConnection->port != "") $host = sprintf("%s:%d",$dbConnection->host,$dbConnection->port);
			$this->connection = @mysql_connect($host,$dbConnection->user,$dbConnection->password,$dbConnection->new);
			if($this->connection != false){
				if(!mysql_select_db($dbConnection->name)){
					$this->connection = false;
					return false;
				}
				$this->dbConnection = $dbConnection;
				$this->_debug_open();
				if($this->query("SHOW VARIABLES LIKE 'version'")){
					$this->mysqlvar = "";
					$resultset = $this->resultset();
					$this->free();
					foreach(ArrayUtil::arrays(explode(".",preg_replace("/[^\d\.]/","",$resultset["Value"])),0,3,true) as $no) $this->mysqlvar .= sprintf("%02d",$no);
				}
				if(!$this->_isUnderVarsion() && !empty($dbConnection->encode)) $this->query("SET NAMES ".$dbConnection->encode);
				$this->query("SET AUTOCOMMIT=0");
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
			@mysql_close($this->connection);
			$this->_debug_close();
		}
		$this->connection = false;
	}
	/**
	 * analyzeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function _analyze($tableObjectList){
		$tables = "";
		
		foreach(ArrayUtil::arrays($tableObjectList) as $tableObject){
			if(Variable::istype("TableObjectBase",$tableObject)){
				foreach($tableObject->columns() as $columnObject){
					$tables .= ",".$columnObject->sqltablename();
					break;
				}
			}
			if(!empty($tables)){
				$this->query("analyze table ".substr($tables,1));
			}
		}
	}
	
	/**
	 * optimizeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function _optimize($tableObjectList){
		$tables = "";
		
		foreach(ArrayUtil::arrays($tableObjectList) as $tableObject){
			if(Variable::istype("TableObjectBase",$tableObject)){
				foreach($tableObject->columns() as $columnObject){
					$tables .= ",".$columnObject->sqltablename();
					break;
				}
			}
			if(!empty($tables)){
				$this->query("optimize table ".substr($tables,1));
			}
		} 
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
			return mysql_fetch_array($this->resourceId,MYSQL_ASSOC);
		}
		return false;
	}
	
	/**
	 * 結果セットを空にする
	 */
	function _free(){
		if($this->resourceId != false){
			mysql_free_result($this->resourceId);
		}
	}

	function _isUnderVarsion(){
		return ((int)$this->mysqlvar < 40100);
	}	
	function _query($sql){
		$this->resourceId	= @mysql_query($sql,$this->connection);
		$errono				= @mysql_errno($this->connection);
		$error				= @mysql_error($this->connection);
		return array($errono,$error);
	}
	function _criteriaToSelectInWhere($columnObject,$criteria){
		if($this->_isUnderVarsion()){
			if($this->query(parent::_criteriaToSelectInWhere($columnObject,$criteria))){
				$results = array();
				foreach($this->resultset() as $result) $results[] = $this->_sqlvalue($columnObject,StringUtil::getMagicQuotesOffValue($result));
				return implode(",",$results);
			}
		}
		return parent::_criteriaToSelectInWhere($columnObject,$criteria);
	}	
	function _selectColumnDateFormat($column){
		return "DATE_FORMAT(%s,'%%Y/%%m/%%d')";
	}
	function _selectColumnTimestampFormat($column){
		return "DATE_FORMAT(%s,'%%Y/%%m/%%d %%H:%%i:%%S')";
	}
	function _selectColumnBirthdayFormat($column){
		return ($column->dbtype) ? $this->_selectColumnDateFormat($column) : "%s";
	}
	function _whereILikePattern(){
		if((!empty($this->dbConnection->encode) && !$this->_isUnderVarsion())){
			return " AND UPPER(CONVERT(%s using ".$this->dbConnection->encode.")) %s LIKE('%s') "; 
		}
		return " AND UPPER(%s) %s LIKE('%s') ";
	}
	function _insertId($tableObject){
		unset($tableObject);
		return mysql_insert_id($this->connection);
	}
	function _escape($value){
		if(extension_loaded("mysql")){
			return mysql_escape_string($value);
		}
		return addslashes($value);
	}
	function _generateSelectToTime($value,$column){
		return (empty($value)) ? "NULL" : 
					(($column->dbtype) ? sprintf("'%s'",DateUtil::formatTime($value)) : intval($value));
	}
	function _generateSelectToBirthday($value,$column){
		return (empty($value)) ? "NULL" : 
					(($column->dbtype) ? sprintf("'%s'",DateUtil::formatDate($value)) : intval($value));
	}
}
?>