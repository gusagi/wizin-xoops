<?php
Rhaco::import("database.model.DbConnection");
Rhaco::import("database.model.Criteria");
Rhaco::import("util.Logger");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.DateUtil");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotSupportedException");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("database.TableObjectUtil");
/**
 * #ignore
 * 各種DbUtilコントローラの基底クラス
 * DbUtilから利用する
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilBase{
	var $resourceId		= false;
	var $connection		= false;
	var $dbConnection	= null;
	var $transaction	= false;
	
	/** デバッグ用 */
	var $id				= 0;	
	var $debug			= "";
	var $last_sql		= "";
	var $last_sql_block	= "";
	var $last_error		= "";

	/**
	 * 最後に実行したSQL
	 *
	 * @param unknown_type $block
	 * @return unknown
	 */
	function sql($block=false){
		return ($block) ? $this->last_sql_block : $this->last_sql;
	}
	
	/**
	 * 最後に発生したエラー
	 *
	 * @return unknown
	 */
	function error(){
		return $this->last_error;
	}

	/**
	 * 使用可能か
	 *
	 * @return unknown
	 */
	function valid(){
		return $this->_valid();
	}
	function _valid(){
		return false;
	}
	/**
	 * データベースをオープンする
	 * @param databse.model.DbConnection $dbConnection
	 * @param boolean $new
	 * @return boolean
	 */
	function open($dbConnection){
		return $this->_open($dbConnection);
	}
	function _open($dbConnection){
		unset($dbConnection);
		return false;
	}
	function _debug_open(){
		$this->id = sizeof(Rhaco::getVariable("RHACO_CORE_DB_CONNECTION_COUNT"));
		Rhaco::addVariable("RHACO_CORE_DB_CONNECTION_COUNT",$this->id,$this->id);
		Logger::deep_debug(Message::_("[{1}({2})] connection start",$this->connection,$this->id));
	}
	function _debug_close(){
		Logger::deep_debug(Message::_("[{1}({2})] connection close",$this->connection,$this->id));
		Rhaco::clearVariable("RHACO_CORE_DB_CONNECTION_COUNT",$this->id);
	}
	
	/**
	 * データベースをクローズする
	 */
	function close(){
		$this->_close();
	}
	function _close(){
		$this->_commit();
		$this->connection = false;
		$this->transaction = false;
	}
	
	/**
	 * queryを発行する
	 *
	 * @param string $sql
	 * @return boolean
	 */
	function query($sql){
		if($this->connection){
			$this->last_sql = $sql;
			$this->last_sql_block .= $sql."\n";

			list($errono,$error) = $this->_query($sql);

			if(!empty($error)){
				Logger::error(Message::_("[{1}({2})] {3}",$this->connection,$this->id,$sql));
				Logger::error(Message::_("#{1} - {2}",$errono,$error));
				$this->last_error = Message::_("#{1} - {2}",$errono,$error);
				return false;	
			}
			Logger::deep_debug(Message::_("[{1}({2})] {3}",$this->connection,$this->id,$sql));
			return true;
		}
		return false;
	}
	function _query($sql){
		return array(0,null);
	}
	
	/**
	 * 結果セットを返す
	 */
	function resultset(){
		return $this->_resultset();
	}
	function _resultset(){
		return false;
	}
	
	/**
	 * 結果セットを空にする
	 */
	function free(){
		$this->_free();
	}
	function _free(){
	}
	
	/**
	 * トランザクションのOn/Offを行う
	 */
	function trans($bool){
		$bool = Variable::bool($bool);

		if($this->transaction && !$bool){
			$this->_commit();
			$this->transaction = false;
			Logger::deep_debug(Message::_("[{1}({2})] transaction off",$this->connection,$this->id));
		}else if(!$this->transaction && $bool){
			Logger::deep_debug(Message::_("[{1}({2})] transaction on",$this->connection,$this->id));
			$this->transaction = true;
			$this->_begin();
		}
	}
	/**
	 * コミットする
	 */
	function commit(){
		$this->_commit();
		$this->_begin();
	}
	
	/**
	 * ロールバックする
	 */
	function rollback(){
		$this->_rollback();
		$this->_begin();
	}
	function _rollback(){
		if($this->connection && $this->transaction) $this->query("ROLLBACK");
	}
	function _begin(){
		$this->last_sql_block = "";
		if($this->connection && $this->transaction) $this->query("BEGIN");
	}
	function _commit(){
		if($this->connection && $this->transaction) $this->query("COMMIT");
	}
	/**
	 * SELECTを発行する
	 *
	 * @param database.model.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteriaList
	 * @return array TableObjectBase
	 */
	function select($tableObjectList,$criteriaList){
		$sql = $this->_generateSelect($tableObjectList,$criteriaList);
		return (!empty($sql)) ? $this->query($sql) : false;
	}
	/**
	 * COUNTを発行する
	 *
	 * @param database.model.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteriaList
	 * @return int
	 */
	function count($tableObject,$criteria){
		$columns = $this->_targetColumns($tableObject,$criteria);
		if(empty($columns)) return 0;
		if($this->query(sprintf("select count(%s) as count from %s %s",
					$columns[0]->getColumnFullname(),
					$this->_generateFrom($tableObject,$criteria),
					$this->_generateWhere($this->_criteriaToSelectWhereString($criteria))))){
			$resultset = $this->resultset();
			$this->free();
			return intval(ArrayUtil::hget("count",$resultset));
		}
		return 0;
	}
	/**
	 * SUMを発行する
	 *
	 */
	function sum($tableObject,$column,$criteria){
		if($this->query(
			sprintf("select SUM(%s) as sumresult from %s %s",
					$column->getColumnFullname(),
					$this->_generateFrom($tableObject,$criteria),
					$this->_generateWhere($this->_criteriaToSelectWhereString($criteria))))
		){
			$resultset = array_change_key_case($this->resultset());
			$this->free();
			return TableObjectUtil::cast($resultset["sumresult"],$column);
		}
		return 0;
	}
	/**
	 * INSERTを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return TableObjectBase / false
	 */
	function insert($tableObject){
		$columnString	= "";
		$tableString	= "";
		$valueString	= "";
		$sql			= "";
		$isserial		= false;

		$columns = $tableObject->columns();
		foreach($columns as $columnObject){
			$value = $this->_sqlvalue($columnObject,TableObjectUtil::getter($tableObject,$columnObject));
			if($columnObject->isSerial() && ($value === "NULL" || empty($value))){
				$this->_insertSerial($columnObject,$columnString,$valueString,$tableString,$isserial);
			}else if(!($value == "NULL" && TableObjectUtil::isTypeDate($columnObject))){
				$columnString	.= sprintf(",%s",$columnObject->sqlname());
				$valueString	.= sprintf(",%s",$value);
				$tableString	= $columnObject->sqltablename();
			}
		}
		if($this->query(
			sprintf("insert into %s(%s) values(%s)",$tableString,substr($columnString,1),substr($valueString,1))		
		)){
			if($isserial){
				$lastId = $this->_insertId($tableObject);
				if($lastId <= 0) return false;
				foreach($tableObject->primaryKey() as $columnObject){
					if($columnObject->isSerial()){
						TableObjectUtil::setter($tableObject,$columnObject,$lastId);
						break;
					}
				}
			}
			return $tableObject;
		}
		return false;
	}
	/**
	 * UPDATEを発行する
	 * Criteriaを指定する事で複数レコードの更新を行える
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return boolean
	 */
	function update($tableObject,$criteria){
		$columnString	= "";
		$tableString	= "";
		$sql			= "";
		$primaryList	= array();

		$updateColumns = $tableObject->columns();
		
		if(!empty($criteria->whichList)){
			$columns = array();
			foreach($tableObject->columns() as $column){
				$tableName = $column->sqltablename();
				break;
			}				
			foreach($criteria->whichList as $column){
				if(Variable::equal($column->sqltablename(),$tableName)) $columns[$column->sqlname()] = $column;
			}
			if(!empty($columns)) $updateColumns = $columns;
		}
		foreach($tableObject->primaryKey() as $columnObject){
			$primaryList[$columnObject->getColumnFullname()] = $columnObject;
		}	
		foreach($updateColumns as $columnObject){
			if(empty($primaryList[$columnObject->getColumnFullname()])){				
				$columnString .= sprintf(",%s = %s",$columnObject->sqlname(),$this->_sqlvalue($columnObject,TableObjectUtil::getter($tableObject,$columnObject)));
				$tableString = $columnObject->sqltablename();
			}
		}
		$columnString = substr($columnString,1);
		if(empty($columnString)){
			Logger::deep_debug("there was not an update object");
			return true;
		}
		return $this->query(
					sprintf("update %s set %s %s",
						$tableString,
						$columnString,
						$this->_generateWhere($this->_criteriaToUpdateWhereString($tableObject,$criteria))
					)
				);
	}
	/**
	 * DELETEを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return boolean
	 */
	function delete($tableObject,$criteria){
		$tableString = "";
		$sql = "";

		foreach($tableObject->columns() as $columnObject){
			$tableString = $columnObject->sqltablename();
			break;
		}
		return $this->query(
				sprintf("delete from %s %s",$tableString,$this->_generateWhere($this->_criteriaToUpdateWhereString($tableObject,$criteria)))
				);
	}

	function droptable($tableObject){
		$this->_droptable($tableObject);
	}
	function _droptable($tableObject){
		unset($tableObjectList);
		ExceptionTrigger::raise(new NotSupportedException("analyze"));
	}
	
	/**
	 * analyzeを発行する
	 * 
	 * @param database.TableObjectBase $tableObjectList
	 */
	function analyze($tableObjectList){
		$this->_analyze($tableObjectList);
	}
	function _analyze($tableObjectList){
		unset($tableObjectList);
		ExceptionTrigger::raise(new NotSupportedException("analyze"));
	}

	/**
	 * optimizeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function optimize($tableObjectList){
		$this->_optimize($tableObjectList);
	}
	function _optimize($tableObjectList){
		unset($tableObjectList);
		ExceptionTrigger::raise(new NotSupportedException("optimize"));
	}

	function _generateSelect($tableObjectList,$criteriaList){
		$sql			= "";
		$boolean		= true;
		$criteriaList	= ArrayUtil::arrays($criteriaList);
		$count			= sizeof($criteriaList);
		$criteria		= $criteriaList[0];
		$selectString	= "";

		if($criteria->isDistinct()){
			$columnString = "";
			foreach($criteria->distinctList as $column) $columnString .= sprintf("%s as %s,",$column->getColumnFullname(),$column->getXXColumn());
			$selectString = sprintf("DISTINCT %s",substr($columnString,0,-1));
		}else{
			$selectString = $this->_generateSelectColumns($this->_targetColumns($tableObjectList,$criteria));
		}
		$orderString	= $this->_generateOrderBy($criteria,($count > 1));
		$limitString	= $this->_generateLimit($criteria);
		$lockString		= ($criteria->isLock()) ? " FOR UPDATE" : "";
		$count			= 0;

		foreach($criteriaList as $criteria){
			$fromString	= $this->_generateFrom($tableObjectList,$criteria);
			$sql		.= ($count > 0) ? (($criteria->isUnionAll()) ? " all " : " union ") : "";
			$sql 		.= sprintf("select %s from %s %s",$selectString,$fromString,$this->_generateWhere($this->_criteriaToSelectWhereString($criteria)));
			$count++;
		}
		$sql .= empty($orderString) ? "" : sprintf(" ORDER BY %s",$orderString);
		$sql .= $limitString;
		$sql .= $lockString;
		return $sql;
	}
	function _generateOrderBy($criteria,$union=false){
		$orderString = "";
		foreach($criteria->orderList as $criteriaPattern){
			$pattern = ($criteriaPattern->pattern == 102) ? "DESC" : "ASC";
			$orderString .= sprintf(",%s %s",(($union) ? $criteriaPattern->argA->getXXColumn() : $criteriaPattern->argA->getColumnFullname()),$pattern);
		}
		return substr($orderString,1);
	}	
	function _generateLimit($criteria){
		return ($criteria->isPaginator()) ? sprintf(" LIMIT %d,%d ",$criteria->getOffset(),$criteria->getLimit()) : "";
	}
	function _generateSelectColumns($columnList){
		$columnString = "";
		
		foreach($columnList as $column){
			$columnName	= $column->getColumnFullname();

			if($column->type == "timestamp"){
				$columnName = sprintf($this->_selectColumnTimestampFormat($column),$column->getColumnFullname());
			}else if($column->type == "date"){
				$columnName = sprintf($this->_selectColumnDateFormat($column),$column->getColumnFullname());
			}else if($column->type == "birthday"){
				$columnName = sprintf($this->_selectColumnBirthdayFormat($column),$column->getColumnFullname());
			}			
			$columnString .= sprintf("%s as %s,",$column->getColumnFullname(),$column->getXXColumn());
		}
		return substr($columnString,0,-1);
	}
	function _selectColumnDateFormat($column){
		return "%s";
	}
	function _selectColumnTimestampFormat($column){
		return "%s";
	}
	function _selectColumnBirthdayFormat($column){
		return "%s";
	}
	function _generateWhere($whereString){
		if(!empty($whereString)){
			return sprintf(" where %s",(preg_match("/^ AND(.+)$/",$whereString,$value)) ? $value[1] : $whereString);
		}
		return "";
	}
	function _generateFrom($tableObjectList,$criteria){
		$fromTableList = array();

		foreach($criteria->distinctList as $columnObject){
			$fromTableList[$columnObject->sqltablealias()] = $columnObject;
		}		
		foreach(ArrayUtil::arrays($tableObjectList) as $tableObject){
			foreach($tableObject->columns() as $columnObject){
				$fromTableList[$columnObject->sqltablealias()] = $columnObject;
			}
		}
		$fromString = "";
		$joinTableList = array();
		$joinTableParentList = array();
		$froms = array();

		foreach($this->_criteriaToFromColumns($criteria) as $column){
			if(!array_key_exists($column->sqltablealias(),$fromTableList)) $froms[$column->getTableNameAs()] = $column->getTableNameAs();
		}
		foreach($criteria->joinList as $criteriaPattern){
			$joinTableList[$criteriaPattern->argA->sqltablealias()] = $criteriaPattern;

			if(array_key_exists($criteriaPattern->argA->sqltablealias(),$fromTableList)) unset($fromTableList[$criteriaPattern->argA->sqltablealias()]);
			if(array_key_exists($criteriaPattern->argB->sqltablealias(),$fromTableList)) unset($fromTableList[$criteriaPattern->argB->sqltablealias()]);
		}
		foreach($joinTableList as $alias => $criteriaPattern){
			if(isset($joinTableList[$criteriaPattern->argB->sqltablealias()])) $joinTableParentList[$alias] = $criteriaPattern;
		}
		foreach($fromTableList as $column){
			$froms[$column->getTableNameAs()] = $column->getTableNameAs();
		}
		$fromString = implode(",",$froms).((!empty($froms)) ? "," : "");

		foreach($joinTableParentList as $criteriaPattern){
			unset($joinTableList[$criteriaPattern->argA->sqltablealias()]);			
			$joinString = sprintf("(%s LEFT JOIN %s ON %s = %s),",
								$criteriaPattern->argA->getTableNameAs(),
								$criteriaPattern->argB->getTableNameAs(),
								$criteriaPattern->argA->getColumnFullname(),
								$criteriaPattern->argB->getColumnFullname()
							);
			$parent	= $criteriaPattern->argB->sqltablealias();
			while(isset($joinTableList[$parent])){
				$joinString = sprintf("(%s LEFT OUTER JOIN %s ON %s = %s),",
									substr($joinString,0,-1),
									$joinTableList[$parent]->argB->getTableNameAs(),
									$criteriaPattern->argA->getColumnFullname(),
									$criteriaPattern->argB->getColumnFullname()
								);
				$parent = $joinTableList[$parent]->argB->sqltablealias();
				unset($joinTableList[$parent]);
			}
			$fromString .= $joinString;
		}
		foreach($joinTableList as $criteriaPattern){
			$joinString = sprintf("%s LEFT OUTER JOIN %s ON %s = %s,",
								$criteriaPattern->argA->getTableNameAs(),
								$criteriaPattern->argB->getTableNameAs(),
								$criteriaPattern->argA->getColumnFullname(),
								$criteriaPattern->argB->getColumnFullname()
							);
			$fromString .= $joinString;
		}
		return substr($fromString,0,-1);
	}
	function _targetColumns($tableObjectList,$criteria){
		$columnList = array();
		$distinct = array();

		foreach($criteria->distinctList as $column) $distinct[$column->getXXColumn()] = $column;
		foreach(ArrayUtil::arrays($tableObjectList) as $tableObject){
			foreach($tableObject->columns() as $column){
				if(!($criteria->isDistinct() && !array_key_exists($column->getXXColumn(),$distinct))) $columnList[] = $column;
			}
		}
		return $columnList;
	}

	function _insertSerial(&$columnObject,&$columnString,&$valueString,&$tableString,&$isserial){
		$isserial = true;
	}
	function _insertId($tableObject){
		unset($tableObject);
		return 0;
	}
	function _criteriaToFromColumns($criteria){
		$columns = array();
		foreach($criteria->criteriaPatternColumnColumnList as $criteriaPattern){
			$columns[$criteriaPattern->argA->getXXColumn()] = $criteriaPattern->argA;
			$columns[$criteriaPattern->argB->getXXColumn()] = $criteriaPattern->argB;
		}
		foreach($criteria->criteriaPatternColumnValueList as $criteriaPattern){
			$columns[$criteriaPattern->argA->getXXColumn()] = $criteriaPattern->argA;			
		}
		foreach($criteria->orderList as $criteriaPattern){
			$columns[$criteriaPattern->argA->getXXColumn()] = $criteriaPattern->argA;			
		}
		foreach($criteria->criteriaList as $cri){
			$columns = array_merge($columns,$this->_criteriaToFromColumns($cri->argA));
		}
		return $columns;
	}	
	
	
	function _criteriaPatternToWhereComp($pattern,$value=""){
		switch($pattern){
			case 1:
			case 21: return ($value === null || $value === "NULL") ? " IS " : " = ";
			case 2: 
			case 22: return ($value === null || $value === "NULL") ? " IS NOT " : " <> ";
			case 3: return " > ";
			case 4: return " >= ";
			case 5: return " < ";
			case 6: return " <= ";
		}
		return "";
	}
	function _whereLikePattern(){
		return " AND %s %s LIKE('%s') ";
	}
	function _whereILikePattern(){
		return " AND UPPER(%s) %s LIKE('%s') ";
	}
	function _whereLikeEscape($value){
		return str_replace(array("\.",".*",".","__C__"),array("__C__","%","_","."),str_replace(array("%","_"),array("\%","\_"),$value));
	}
	function _criteriaToSelectInWhere($columnObject,$criteria){
		$sql			= "";
		$selectString	= $this->_generateSelectColumns(array($columnObject));
		$orderString	= $this->_generateOrderBy($criteria);
		$sql .= sprintf("select %s from %s %s",$selectString,$this->_generateFrom(array($columnObject->tableObject()),$criteria),$this->_generateWhere($this->_criteriaToSelectWhereString($criteria)));
		$sql .= empty($orderString) ? "" : sprintf(" ORDER BY %s",$orderString);
		$sql .= $this->_generateLimit($criteria);
		return $sql;
	}
	function _criteriaPatternToWhereString($criteriaPattern,$alias=true){
		$columnName = ($alias) ? $criteriaPattern->argA->getColumnFullname() : $criteriaPattern->argA->sqlname();

		if($criteriaPattern->pattern <= 6){
			$value = $this->_sqlvalue($criteriaPattern->argA,$criteriaPattern->argB);
			return sprintf(" AND %s %s %s ",
							$columnName,$this->_criteriaPatternToWhereComp($criteriaPattern->pattern,$value),$value
						);
		}else if($criteriaPattern->pattern == 7 || $criteriaPattern->pattern == 8){
			return sprintf($this->_whereLikePattern(),
								$columnName,
								(($criteriaPattern->pattern == 8)?"NOT":""),
								$this->_whereLikeEscape($criteriaPattern->argB)
						);
		}else if($criteriaPattern->pattern == 9 || $criteriaPattern->pattern == 10){
			return sprintf($this->_whereILikePattern(),
								$columnName,
								(($criteriaPattern->pattern == 10)?"NOT":""),
								$this->_whereLikeEscape(strtoupper($criteriaPattern->argB))
						);
		}else if($criteriaPattern->pattern == 11 || $criteriaPattern->pattern == 12){
			$inString	= "";

			foreach($criteriaPattern->argB as $value){
				$inString .= sprintf(",%s",$this->_sqlvalue($criteriaPattern->argA,$value));
			}
			return sprintf(" AND %s %s IN(%s) ",
							$columnName,
							(($criteriaPattern->pattern == 12)?"NOT":""),
							substr($inString,1)
						);
		}else if($criteriaPattern->pattern == 21 || $criteriaPattern->pattern == 22){
			$select = $this->_criteriaToSelectInWhere($criteriaPattern->argB[0],$criteriaPattern->argB[1]);
			return sprintf(" AND %s %s (%s) ",
							$columnName,$this->_criteriaPatternToWhereComp($criteriaPattern->pattern,$select),$select
						);
		}else if($criteriaPattern->pattern == 23 || $criteriaPattern->pattern == 24){
			return sprintf(" AND %s %s IN(%s) ",
							$columnName,
							(($criteriaPattern->pattern == 24)?"NOT":""),
							$this->_criteriaToSelectInWhere($criteriaPattern->argB[0],$criteriaPattern->argB[1])
						);
		}
		return "";
	}
	function _criteriaToSelectWhereString($criteria){
		$whereString = "";

		foreach($criteria->criteriaPatternColumnValueList as $criteriaPattern){
			$whereString .= $this->_criteriaPatternToWhereString($criteriaPattern);
		}
		foreach($criteria->criteriaPatternColumnColumnList as $criteriaPattern){
			$whereString .= sprintf(" AND %s %s %s ",
										$criteriaPattern->argA->getColumnFullname(),
										$this->_criteriaPatternToWhereComp($criteriaPattern->pattern),
										$criteriaPattern->argB->getColumnFullname()
								);
		}
		foreach($criteria->criteriaList as $criteriaObject){
			$pattern	= ($criteriaObject->pattern == 2) ? "OR" : "AND";
			$where		= $this->_criteriaToSelectWhereString($criteriaObject->argA);
   			if(preg_match("/^ AND(.+)$/",$where,$value)) $where = $value[1];
			if(!empty($where)){
				if(empty($whereString)) $pattern = "";
				$whereString .= sprintf(" %s (%s) ",$pattern,$where);
			}
		}
		return $whereString;
	}
	
	function _criteriaToUpdateWhereString($tableObject,$criteria){
		$whereString = "";
		$tableString = "";

		if(!Variable::istype("Criteria",$criteria)) $criteria = new Criteria();
		foreach($tableObject->columns() as $columnObject){
			$tableString = $columnObject->sqltablename();
			break;
		}
		foreach($criteria->criteriaPatternColumnValueList as $criteriaPattern){
			if($criteriaPattern->argA->sqltablename() == $tableString){
				$whereString .= $this->_criteriaPatternToWhereString($criteriaPattern,false);
			}else{
				ExceptionTrigger::raise(new IllegalArgumentException(Message::_("table is　only [{1}]",$tableString)));				
			}
		}
		return $whereString;
	}
	function _escape($value){
		return addslashes($value);
	}	
	function _sqlvalue($column,$value){
		if(is_null($value)) return (TableObjectUtil::isTypeBool($column) ? 0 : "NULL");
		if(TableObjectUtil::isTypeString($column)) return sprintf("'%s'",$this->_escape($value));
		if(TableObjectUtil::isTypeInt($column)) return intval($this->_escape($value));
		if(TableObjectUtil::isTypeDate($column)) return $this->_generateSelectToDate($this->_escape($value),$column);
		if(TableObjectUtil::isTypeBool($column)) return (Variable::bool($this->_escape($value)) ? 1 : 0);
		if(TableObjectUtil::isTypeFloat($column)) return floatval($this->_escape($value));
		if(TableObjectUtil::isTypeTime($column)) return $this->_generateSelectToTime($this->_escape($value),$column);
		if(TableObjectUtil::isTypeBirthday($column)) return $this->_generateSelectToBirthday($this->_escape($value),$column);
		return "NULL";
	}
	function _generateSelectToDate($value,$column){
		return (empty($value)) ? "NULL" : sprintf("DATE_FORMAT('%s','%%Y/%%m/%%d %%H:%%i:%%S')",DateUtil::format($value));
	}
	function _generateSelectToTime($value,$column){
		return (empty($value)) ? "NULL" : intval($value);
	}
	function _generateSelectToBirthday($value,$column){
		return (empty($value)) ? "NULL" : intval($value);
	}
}
?>