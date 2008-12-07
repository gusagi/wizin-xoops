<?php
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("lang.StringUtil");
Rhaco::import("setup.database.model.ColumnModel");
Rhaco::import("setup.database.model.DatabaseModel");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.Variable");
/**
 * setup.php用　data model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class TableModel{
	var $recognition_code;
	var $name;
	var $class;
	var $method;
	var $default;
	var $plural;
	var $pluralMethod;
	var $description;
	var $columnList = array();
	var $extraList = array();
	var $mapList = array();
	var $defaults = "";
	
	function TableModel($tableTag){
		if(!Variable::istype("SimpleTag",$tableTag)) return false;
		ObjectUtil::copyProperties($tableTag,$this,true);
		if(empty($this->name)) ExceptionTrigger::raise(new NotFoundException(Message::_("table name")));

		$this->class = (empty($this->class)) ? $this->name : $this->class;
		$this->method = StringUtil::regularizedName($this->class);
		$this->plural = strtolower(substr($this->method,0,1)).substr($this->method,1)."s";
		$this->pluralMethod = $this->method."s";
		$this->description = str_replace("\n"," ",StringUtil::toULD($tableTag->getInValue("description")));
		$this->recognition_code = strtolower($this->method);
		$this->default = (!empty($this->default)) ? FileUtil::path(Rhaco::setuppath(),$this->default) : "";

		foreach($tableTag->getIn("default",true) as $defaultTag){
			$tag = new SimpleTag("default",$defaultTag->getValue(),array("class"=>$this->class));
			$this->defaults .= $tag->get();
		}		
		foreach($tableTag->getIn("column",true) as $columnTag){
			$column = new ColumnModel($columnTag,$this);
			$this->columnList[$column->recognition_code] = $column;
		}
		foreach($this->columnList as $recognition_code => $column){
			$this->columnList[$column->recognition_code]->check($this->columnList);
		}		
		foreach($tableTag->getIn("extra",true) as $columnTag){
			$column = new ColumnModel($columnTag);
			$this->extraList[$column->recognition_code] = $column;
		}
		DatabaseModel::isReserved($this->name,"table",$this->name);
	}
	function primaryList(){
		$list = array();
		foreach($this->columnList as $column){
			if(Variable::bool($column->primary)) $list[] = $column;
		}
		return $list;
	}
	function choices(){
		$list = array();
		foreach($this->columnList as $column){
			if(!empty($column->choices)) $list[$column->var] = $column->choices;
		}
		foreach($this->extraList as $column){
			if(!empty($column->choices)) $list[$column->var] = $column->choices;
		}
		return $list;
	}
	function isChoices(){
		foreach($this->columnList as $column) if($column->isChoices()) return true;
		foreach($this->extraList as $column) if($column->isChoices()) return true;
		return false;
	}
	function isDefaults(){
		return !empty($this->defaults);
	}
	function getColumnExtra(){
		return array("column"=>$this->columnList,"extra"=>$this->extraList);
	}
}
?>