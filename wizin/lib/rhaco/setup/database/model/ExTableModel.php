<?php
Rhaco::import("setup.database.model.TableModel");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
/**
 * setup.php用　data model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ExTableModel extends TableModel{
	var $table;
	var $change = false;
	
	function ExTableModel($tableTag,$tableList){
		if(!Variable::istype("SimpleTag",$tableTag)) return false;
		ObjectUtil::copyProperties($tableTag,$this,true);

		$this->class = (empty($this->class)) ? $this->name : $this->class;
		if(empty($this->class)) ExceptionTrigger::raise(new NotFoundException(Message::_("ext table class")));		
		$this->method = StringUtil::regularizedName($this->class);
		$this->plural = strtolower(substr($this->method,0,1)).substr($this->method,1)."s";
		$this->pluralMethod = $this->method."s";	
		$this->description = str_replace("\n"," ",StringUtil::toULD($tableTag->getInValue("description")));
		$this->recognition_code = strtolower($this->method);

		$this->columnList = array();
		$this->extraList = array();
		$this->mapList = array();		
		
		foreach($tableList as $table){
			if(Variable::iequal($this->table,$table->class) || Variable::iequal($this->table,$table->name)){
				$this->table = Variable::copy($table);
				$columns = $tableTag->getIn("column",true);

				if(!empty($columns)){
					foreach($columns as $columnTag){
						$column = new ColumnModel($columnTag);
						$bool = false;
	
						foreach($this->table->columnList as $checkcolumn){
							if(Variable::iequal($column->var,$checkcolumn->var)){
								if($columnTag->isParameter("default")) $column->setDefault($columnTag->getParameter("default"));
								$this->columnList[$checkcolumn->recognition_code] = Variable::copy($checkcolumn);
								$bool = true;
								break;
							}
						}
						if(!$bool) ExceptionTrigger::raise(new NotFoundException(Message::_("column {1}",$column->var)));
					}
					$this->change = true;
				}else{
					foreach($this->table->columnList as $column){
						$this->columnList[$column->recognition_code] = Variable::copy($column);
					}
				}
				foreach($tableTag->getIn("extra",true) as $columnTag){
					$column = new ColumnModel($columnTag);
					$this->extraList[$column->recognition_code] = $column;
					$this->change = true;					
				}
				break;
			}
		}
		if(!Variable::istype("TableModel",$this->table)){
			ExceptionTrigger::raise(new NotFoundException(Message::_("table {1}",$this->table)));
		}
	}
}
?>