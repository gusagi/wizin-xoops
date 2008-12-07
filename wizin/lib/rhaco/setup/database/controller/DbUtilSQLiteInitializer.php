<?php
Rhaco::import("database.controller.DbUtilSQLite");
Rhaco::import("lang.Variable");
Rhaco::import("setup.database.model.DatabaseModel");
/**
 * database.DBUtilの拡張
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilSQLiteInitializer extends DbUtilSQLite{
	/**
	 * SQLite用のcreate database処理用SQLを返す
	 *
	 * @param setup.model.Databasemodel $databaseModel
	 * @return string $sql
	 */
	function forward($databaseModel){
		/*** #pass */
		$sql = "";
		$defaultSql	= "";
		$settables = array();
		$contentsql = "";
		
		if(Variable::istype("DatabaseModel",$databaseModel)){
			foreach($databaseModel->tableList as $table){
				if(Variable::istype("TableModel",$table) && !Variable::istype("ExTableModel",$table) && !isset($settables[strtolower($table->name)])){
					$settables[strtolower($table->name)] = $table;
					$sql			.= sprintf("CREATE TABLE %s (\n",$databaseModel->prefix.$table->name);
					$columnSql	= "";
					$primarySql	= "";
					
					foreach($table->columnList as $column){
						if(Variable::istype("ColumnModel",$column)){
							if(!empty($columnSql)){
								$columnSql .= "\t,";
							}
							$columnSql .= $column->name;

							if(preg_match("/(serial)/i",$column->type)){
								$columnSql .= sprintf(" INTEGER ");
							}else if(preg_match("/(float)/i",$column->type)){
								$columnSql .= sprintf(" FLOAT(%d,%d)",$column->max_digits+$column->decimal_places,$column->decimal_places);
							}else if(preg_match("/(timestamp)|(date)/i",$column->type)){
								$columnSql .= sprintf(" TIMESTAMP");
							}else if(preg_match("/(int)|(time)|(birthday)/i",$column->type)){
								$columnSql .= sprintf(" INTEGER");
							}else if(preg_match("/(string)|(text)|(email)|(tel)|(zip)/i",$column->type)){
								if($column->size > 16777215){
									$columnSql .= sprintf(" VARCHAR");
								}else if($column->size > 65535){
									$columnSql .= sprintf(" VARCHAR");
								}else if($column->size > 255){
									$columnSql .= sprintf(" VARCHAR");
								}else if($column->size > 0){
									$columnSql .= sprintf(" VARCHAR");
								}else{
									$columnSql .= sprintf(" TEXT");
								}
							}else if(preg_match("/(bool)/i",$column->type)){
								$columnSql .= sprintf(" INTEGER(1)");
							}
							if(Variable::bool($column->require)){
								$columnSql .= sprintf(" NOT NULL");
							}
							$columnSql .= "\n";
						}
					}
					foreach($table->primaryList() as $column){
						if(!empty($primarySql)){
							$primarySql .= ",";
						}
						$primarySql .= $column->name;
					}
					$sql	 .= sprintf("\t %s",$columnSql);
					if(!empty($primarySql)){
						$sql .= sprintf("\t,PRIMARY KEY(%s)\n",$primarySql);
					}
					$sql .= sprintf(");\n\n");
				}
			}
		}
		return $sql.$contentsql;
	}
}
?>