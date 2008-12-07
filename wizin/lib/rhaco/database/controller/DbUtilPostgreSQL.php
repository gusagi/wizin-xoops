<?php
Rhaco::import("database.DbUtilBase");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.GenericException");
/**
 * #ignore
 * PostgreSQL用 databse操作クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilPostgreSQL extends DbUtilBase{
	var $name = "PostgreSQL";
	var $escapefunc = true;
	
	/**
	 * 使用可能か
	 *
	 * @return unknown
	 */
	function _valid(){
		return extension_loaded("pgsql");
	}	
	/**
	 * データベースをオープンする
	 * @param databse.model.DbConnection $dbConnection
	 * @return boolean
	 */	
	function _open($dbConnection){
		if($this->valid()){
			$con = sprintf("dbname=%s",$dbConnection->name);
	
			if($dbConnection->host != ""){		$con .= sprintf(" host=%s",$dbConnection->host);			}
			if($dbConnection->port != ""){		$con .= sprintf(" port=%s",$dbConnection->port);			}
			if($dbConnection->user != ""){		$con .= sprintf(" user=%s",$dbConnection->user);			}
			if($dbConnection->password != ""){	$con .= sprintf(" password=%s",$dbConnection->password);	}

			if($dbConnection->new){
				$this->connection = @pg_connect($con,PGSQL_CONNECT_FORCE_NEW);
			}else{
				$this->connection = @pg_connect($con);				
			}
			if($this->connection != false){
				if($dbConnection->encode != ""){
					if(0 !== pg_set_client_encoding($this->connection,$dbConnection->encode)){
						ExceptionTrigger::raise(new GenericException(Message::_("an encode string is illegal")));
						$this->connection = false;
						return false;
					}
				}
				$this->dbConnection = $dbConnection;
				$this->_debug_open();
				$this->trans(true);
				if(!function_exists("pg_escape_string")) $this->escapefunc = false;
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
			pg_close($this->connection);
			$this->_debug_close();
		}
		$this->connection = false;
	}

	function _query($sql){
		$this->resourceId	= @pg_query($this->connection,$sql);
		$error				= @pg_last_error($this->connection);
		return array(0,$error);
	}

	/**
	 * analyzeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function _analyze($tableObjectList){
		foreach(ArrayUtil::arrays($tableObjectList) as $tableObject){
			if(Variable::istype("TableObjectBase",$tableObject)){
				foreach($tableObject->columns() as $columnObject){
					$this->query("vacuum analyze ".$columnObject->sqltablename());
					break;
				}
			}
		}
	}
	
	
	/**
	 * optimizeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function _optimize($tableObjectList){
		foreach(ArrayUtil::arrays($tableObjectList) as $tableObject){
			if(Variable::istype("TableObjectBase",$tableObject)){
				foreach($tableObject->columns() as $columnObject){
					$this->query("vacuum ".$columnObject->sqltablename());
					break;
				}
			}
		}
	}
	
	function _droptable($tableObject){
		if(Variable::istype("TableObjectBase",$tableObject)){
			foreach($tableObject->columns() as $columnObject){
				$this->query("drop table ".$columnObject->sqltablename()." CASCADE");
				break;
			}
		}
	}
	
	/**
	 * 結果セットを返す
	 */
	function _resultset(){
		if($this->resourceId != false){
			$list = pg_fetch_assoc($this->resourceId);
			if($list){
				$result = array();
				foreach(ArrayUtil::arrays($list) as $key => $value) $result[$key] = $value;
				return $result;
			}
		}
		return false;
	}

	/**
	 * 結果セットを空にする
	 */	
	function _free(){
		if($this->resourceId != false){
			pg_free_result($this->resourceId);
		}
	}	
	function _generateLimit($criteria){
		if($criteria->isPaginator()){
			return sprintf(" OFFSET %d LIMIT %d ",$criteria->getOffset(),$criteria->getLimit());
		}
		return;
	}
	function _selectColumnDateFormat($column){
		return "to_char(%s,'YYYY/MM/DD')";
	}
	function _selectColumnTimestampFormat($column){
		return "to_char(%s,'YYYY/MM/DD HH24:MI:SS')";
	}
	function _selectColumnBirthdayFormat($column){
		return ($column->dbtype) ? $this->_selectColumnDateFormat($column) : "%s";
	}
	function _generateSelectToDate($value,$column){
		return (empty($value)) ? "NULL" : sprintf("to_timestamp('%s','YYYY/MM/DD HH24:MI:SS')",DateUtil::format($value));
	}
	function _insertId($tableObject){
		foreach($tableObject->primaryKey() as $pkeyColumn){
			if($this->query(sprintf("select last_value from %s",$pkeyColumn->seq()))){
				$resultset = $this->resultset();
				$this->free();
				return intval($resultset["last_value"]);
			}
		}
		return 0;
	}
	function _escape($value){
		return ($this->escapefunc) ? pg_escape_string($value) : addslashes($value);
	}
	
	/**
	 * シーケンスの値を再設定する
	 *
	 * @param unknown_type $tableObject
	 */
	function updateSerialMax($tableObject){
		/*** #pass */
		if(Variable::istype("TableObjectBase",$tableObject)){
			$classname = get_class($tableObject);
			foreach($tableObject->columns() as $columnObject){
				if($columnObject->type == "serial"){
					$this->query("select setval('".$columnObject->seq()."',(select max(".$columnObject->sqlname().") from ".$columnObject->sqltablename()."))");
				}
			}
		}
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