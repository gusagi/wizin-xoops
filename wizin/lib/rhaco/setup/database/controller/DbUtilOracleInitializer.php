<?php
Rhaco::import("database.controller.DbUtilOracle");
Rhaco::import("lang.Variable");
Rhaco::import("setup.database.model.DatabaseModel");
/**
 * database.DBUtilの拡張
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtilOracleInitializer extends DbUtilOracle{
	/**
	 * OracleL用のcreate database処理用SQLを返す
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
					$columnSql		= "";
					$primarySql		= "";
					$referenceSql	= "";
					
					foreach($table->columnList as $column){
						if(Variable::istype("ColumnModel",$column)){
							if(!empty($columnSql)){
								$columnSql .= "\t,";
							}
							$columnSql .= $column->name;

							if(preg_match("/(float)/i",$column->type)){
								$columnSql .= sprintf(" NUMBER(%d,%d)",$column->max_digits+$column->decimal_places,$column->decimal_places);
							}else if(preg_match("/(timestamp)|(date)/i",$column->type)){
								$columnSql .= sprintf(" DATE");
							}else if(preg_match("/(serial)|(int)|(time)|(birthday)/i",$column->type)){
								$columnSql .= sprintf(" NUMBER(%d)",(empty($column->size)) ? 38 : $column->size);
							}else if(preg_match("/(string)|(text)|(email)|(tel)|(zip)/i",$column->type)){
								if($column->size > 0){
									$type = "VARCHAR2";
									$columnSql .= sprintf(" %s",$type);
									$columnSql .= sprintf("(%d char)",$column->size);
								}else{
									$type = "VARCHAR2(1333 char)";
									$columnSql .= sprintf(" %s",$type);
								}
							}else if(preg_match("/(bool)/i",$column->type)){
								$columnSql .= sprintf(" NUMBER(1)",$type);
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
						$primarySql .= sprintf("%s",$column->name);
					}
					$sql .= sprintf("\t %s",$columnSql);
					
					if(!empty($primarySql)){
						$sql .= sprintf("\t,PRIMARY KEY(%s)\n",$primarySql);
					}
					$sql .= sprintf(");\n\n");
					
					foreach($table->primaryList() as $column){
						$contentsql .= sprintf("CREATE SEQUENCE %s_%s_seq;\n",$databaseModel->prefix.$table->name,$column->name);
					}
				}	
			}
		}
		return $sql.$contentsql;
	}
}
?>