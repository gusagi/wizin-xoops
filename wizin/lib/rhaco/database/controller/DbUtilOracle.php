<?php
Rhaco::import("database.DbUtilBase");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("database.model.Criteria");
/**
 * #ignore
 * Oracle用 databse操作クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilOracle extends DbUtilBase{
	var $name = "Oracle";

	/**
	 * 使用可能か
	 *
	 * @return unknown
	 */
	function _valid(){
		return extension_loaded("oci8");
	}	
	/**
	 * データベースをオープンする
	 * @param databse.model.DbConnection $dbConnection
	 * @return boolean
	 */	
	function _open($dbConnection){
		if($this->valid()){
			$dbConnection->port = (empty($dbConnection->port)) ? 1521 : $dbConnection->port;
			$host = sprintf("//%s:%d/%s",$dbConnection->host,$dbConnection->port,$dbConnection->name);
			
			if($dbConnection->new){
				$this->connection = oci_new_connect($dbConnection->user,$dbConnection->password,$host,$dbConnection->encode);
			}else{
				$this->connection = oci_connect($dbConnection->user,$dbConnection->password,$host,$dbConnection->encode);
			}
			if($this->connection != false){
				$this->dbConnection = $dbConnection;
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
			oci_rollback($this->connection);
			oci_close($this->connection);
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
				$this->query("analyze table ".substr($tables,1)." compute statistics");
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
				$this->query("drop table ".$columnObject->sqltablename()." CASCADE CONSTRAINTS");
				break;
			}
		}
	}
	
	/**
	 * 結果セットを返す
	 */
	function _resultset(){
		if($this->resourceId != false){
			$list = @oci_fetch_array($this->resourceId,OCI_ASSOC);

			if(is_array($list)){
				$result = array();
				foreach($list as $key => $value){
					if(Variable::istype("OCI-Lob",$value)) $value = $value->load();
					$result[$key] = $value;
				}
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
			oci_free_statement($this->resourceId);
		}
	}
	/**
	 * SELECTを発行する
	 *
	 * @param database.model.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteriaList
	 * @return array TableObjectBase
	 */
	function select($tableObjectList,$criteriaList){
		$criteriaList = ArrayUtil::arrays($criteriaList);
		$sql = $this->_generateSelect($tableObjectList,$criteriaList);

		$criteria = $criteriaList[0];
		if(!empty($sql) && $criteria->isPaginator()){
			$sql = sprintf("SELECT * FROM (SELECT ROWNUM AS SEQ_NO, TBL.* FROM (%s) TBL) WHERE SEQ_NO >= %d AND SEQ_NO <= %d",$sql,$criteria->getOffset() + 1,($criteria->getOffset() + $criteria->getLimit()) + 1);
		}
		return (!empty($sql)) ? $this->query($sql) : false;
	}


	function _begin(){		
	}
	function _rollback(){
		if($this->connection && $this->transaction) oci_rollback($this->connection);
	}
	function _commit(){
		if($this->connection && $this->transaction) oci_commit($this->connection);
	}	
	function _query($sql){
		$this->resourceId = oci_parse($this->connection,$sql);

		if($this->resourceId !== false) @oci_execute($this->resourceId,OCI_DEFAULT);
		$error = oci_error($this->connection);
		return array($error['offset']+1,$error['message']);
	}
	function _selectColumnDateFormat($column){
		return "TO_CHAR(%s,'YYYY/MM/DD')";
	}
	function _selectColumnTimestampFormat($column){
		return "TO_CHAR(%s,'YYYY/MM/DD HH24:MI:SS')";
	}
	function _generateSelectToDate($value,$column){
		return (empty($value)) ? "NULL" : sprintf("TO_DATE('%s','YYYY/MM/DD HH24:MI:SS')",DateUtil::format($value));
	}
	function _insertSerial(&$columnObject,&$columnString,&$valueString,&$tableString,&$isserial){
		$columnString	.= sprintf(",%s",$columnObject->sqlname());
		$valueString	.= sprintf(",%s.nextval",$columnObject->seq());
		$tableString	= $columnObject->sqltablename();
		$isserial = true;		
	}
	function _insertId($tableObject){
		foreach($tableObject->primaryKey() as $columnObject){
			if($this->query("select ".$columnObject->seq().".currval as curr from dual")){
				$resultset = $this->resultset();	
				$this->free();
				$resultset = array_change_key_case($resultset);
				return intval($resultset["curr"]);
			}
		}
		return null;
	}
	function _escape($value){
		return str_replace(array("'"),array("''"),$value);
	}
	function _generateLimit($criteria){
		return "";
	}
	function _whereLikePattern(){
		return " AND %s %s LIKE TO_NCHAR('%s') ESCAPE TO_NCHAR('\') ";
	}
	function _whereILikePattern(){
		return " AND UPPER(%s) %s LIKE TO_NCHAR('%s') ESCAPE TO_NCHAR('\') ";
	}	
	function _whereLikeEscape($value){
		return str_replace(array("\.",".*",".","__C__"),array("__C__","%","_","."),str_replace(array("%","_"),array("\%","\_"),$value));
	}
}
?>