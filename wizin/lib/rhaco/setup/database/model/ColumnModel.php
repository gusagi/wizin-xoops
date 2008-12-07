<?php
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("lang.Variable");
Rhaco::import("lang.DateUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("setup.database.model.DatabaseModel");
Rhaco::import("tag.model.SimpleTag");
/**
 * setup.php用　data model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class ColumnModel{
	var $recognition_code = "";

	var $name = "";
	var $var = "";
	var $method = "";
	var $description = "";

	var $default = "null";
	var $label = "";
	var $reference = "";
	var $dependList = array();

	var $type = "";	
	var $size = null;

	var $max_digits = 24;
	var $decimal_places = 2;
	var $chartype = "";	
	var $min = null;
	var $max = null;

	var $require = false;	
	var $requirewith = "";
	var $choices = array();
	var $primary = false;
	var $pureprimary = false;
	var $unique = false;
	var $uniquewith = "";
	
	var $dbtype = false;
	var $seq = "";
	var $tableModel = null;
	
	/**
	 * <column
	 * 	name="string"
	 * 	var="string"
	 * 	type="string"
	 * 	require="boolean"
	 * 	unique="boolean"
	 * 	size="integer"
	 * 	default="unknown_type"
	 * >
	 *
	 * @param unknown_type $columnTag
	 * @param unknown_type $tableModel
	 * @return ColumnModel
	 */
	function ColumnModel($columnTag,$tableModel=null){
		if(!Variable::istype("SimpleTag",$columnTag)) return false;
		ObjectUtil::copyProperties($columnTag,$this,true);
		$this->tableModel = $tableModel;

		if($this->isReference()){
			$this->reference = explode(".",strtolower($this->reference));
			if(empty($this->name)) $this->name = implode("_",$this->reference);
		}
		if(empty($this->name)) ExceptionTrigger::raise(new NotFoundException(Message::_("column name")));
		if(empty($this->label)) $this->label = $this->name;
		if(!empty($this->uniquewith)) $this->unique = true;

		$this->_defaultType();
		$this->type = $this->_parseFloat($this->type);
		$this->type = $this->_parseType($this->type);		
		
		foreach($columnTag->getIn("choices",true) as $choicesTag){
			foreach($choicesTag->getIn("data") as $data){
				$value = trim($data->getValue());
				if($value !== "") $this->choices[$value] = trim($data->param("caption",$value));
			}
		}
		$this->description = StringUtil::toULD($columnTag->getInValue("description",trim($columnTag->getValue())));
		$this->pureprimary = Variable::bool($this->primary);
		$this->primary = Variable::bool($this->primary);
		$this->require = Variable::bool($this->require);
		$this->unique = Variable::bool($this->unique);
		$this->uniquewith = trim($this->uniquewith);
		$this->chartype = (!empty($this->chartype) && !preg_match("/^\/.+\/[imsxeADSUXu]*$/",$this->chartype)) ? "/".$this->chartype."/" : $this->chartype;
		
		$this->dbtype = Variable::bool($this->dbtype);

		$this->_parseMethod();
		$this->_parseDefault();
		$this->_parseSize();
		$this->recognition_code = strtolower($this->method);
		DatabaseModel::isReserved($this->name,"column",(Variable::istype("TableModel",$tableModel) ? $tableModel->class."::" : "").$this->name);
	}
	function setDefault($value){
		$this->default = $value;
		$this->_parseDefault();
	}
	function _defaultType(){
		if(strtolower($this->name) == "id" && empty($this->type)) $this->type = "serial";
		if(empty($this->type)) $this->type = "string";
	}
	function _parseMethod(){
		if(empty($this->method)){
			if(empty($this->var)){
				$this->var = $this->name;
				$this->var = StringUtil::regularizedName($this->var,true);
			}
			$this->method = ucwords($this->var);
		}
	}
	function _parseDefault(){
		if($this->type == "serial"){
			$this->default = "null";
		}else if($this->type == "integer" || $this->type == "time"){
			$this->default = ($this->default === "null") ? "null" : sprintf("%d",$this->default);
		}else if($this->type == "float"){
			$this->default = ($this->default === "null") ? "null" : sprintf("%f",$this->default);
		}else if($this->type == "boolean"){
			$this->default = intval(Variable::bool($this->default));
		}else if(($this->type == "timestamp" || $this->type == "date") && $this->default != "null"){
			$this->default = (preg_match("/sysdate/i",$this->default)) ? "time()" : "\"".DateUtil::parseString($this->default)."\"";
		}else if($this->default !== "null"){
			$this->default = sprintf("\"%s\"",$this->default);
		}else{
			$this->default = "null";
		}		
	}
	function _parseSize(){
		if(empty($this->size)){
			if($this->type == "email"){
				$this->size = 255;
			}else if($this->type == "integer" || $this->type == "serial" || $this->type == "time"){
				$this->size = 22;
			}else if($this->type == "zip"  || $this->type == "birthday"){
				$this->size = 8;
			}else if($this->type == "tel"){
				$this->size = 13;
			}else{
				$this->size = null;
			}
		}
	}
	function _parseFloat($value){
		if(preg_match("/float\(([\d,]+)\)/i",$value,$match)){
			$digits = explode(",",$match[1]);
			if(isset($digits[0])) $this->max_digits = intval($digits[0]);
			if(isset($digits[1])) $this->decimal_places = intval($digits[1]);
			if($this->max_digits > 65) ExceptionTrigger::raise(new IllegalArgumentException(Message::_("max_digits [{1}]",$this->max_digits)));
			if($this->decimal_places > 30) ExceptionTrigger::raise(new IllegalArgumentException(Message::_("decimal_places [{1}]",$this->decimal_places)));
			return "float";
		}
		return $value;
	}
	function _parseType($value){
		switch($value){
			case "str":
			case "string":
				return "string";
			case "text":
			case "textarea":
				return "text";
			case "email":
			case "mail":
				return "email";
			case "tel":
				return "tel";
			case "zip":
				return "zip";
			case "int":
			case "integer":
				return "integer";
			case "float":
			case "double":
				return "float";
			case "date":
				return "date";
			case "timestamp":
				return "timestamp";
			case "time":
				return "time";
			case "birthday":
			case "bd":
				return "birthday";
			case "serial":
				$this->primary = Variable::bool(true,true);
				return "serial";
			case "bool":
			case "boolean":
			case "flag":
				return "boolean";
			default:
				ExceptionTrigger::raise(new NotFoundException(Message::_("column type [{1}]",$value)));
		}
		return "";
	}

	function check($columnList){
		if(!$this->_checkRequireWith($columnList)){
			ExceptionTrigger::raise(new IllegalArgumentException(Message::_("require with column '{1}'",$this->var)));
		}
		if(!$this->_checkUniquewith($columnList)){
			ExceptionTrigger::raise(new IllegalArgumentException(Message::_("unique with column '{1}'",$this->var)));
		}
	}
	function _checkRequireWith($columnList){
		if(!empty($this->requirewith)){
			foreach($columnList as $rcolumn){
				if(Variable::iequal($rcolumn->var,$this->requirewith) || Variable::iequal($rcolumn->name,$this->requirewith)){
					$this->requirewith = $rcolumn;
					return true;
				}
			}
			return ExceptionTrigger::raise(new NotFoundException(Message::_("column '{1}' matched to require '{2}'",$this->var,$this->requirewith)));
		}
		return true;
	}
	function _checkUniquewith($columnList){
		if(!empty($this->uniquewith)){
			foreach($columnList as $rcolumn){
				if(Variable::iequal($rcolumn->var,$this->uniquewith) || Variable::iequal($rcolumn->name,$this->uniquewith)){
					$this->uniquewith = $rcolumn;
					return true;
				}
			}
			return ExceptionTrigger::raise(new NotFoundException(Message::_("column '{1}' matched to unique '{2}'",$this->var,$this->uniquewith)));
		}
		return true;
	}
	function checkReference($tableList){
		if($this->isReference() && is_string($this->reference[0])){
			foreach($tableList as $table){
				if(Variable::iequal($table->name,$this->reference[0]) || Variable::iequal($table->class,$this->reference[0])){
					$this->reference[0] = $table;
					
					if(isset($this->reference[1])){
						foreach($this->reference[0]->columnList as $column){
							if(Variable::iequal($column->name,$this->reference[1]) || Variable::iequal($column->var,$this->reference[1])){
								$this->reference[1] = $column;
								$this->type = $column->type;
								$this->size = $column->size;
								if($this->type == "serial") $this->type = "integer";
								return true;
							}
						}
					}else{
						foreach($this->reference[0]->columnList as $column){
							if($column->type == "serial"){
								$this->reference[1] = $column;
								$this->type = $column->type;
								$this->size = $column->size;
								if($this->type == "serial") $this->type = "integer";
								return true;
							}
						}
					}
				}
			}
			return ExceptionTrigger::raise(new NotFoundException(Message::_("reference table [{1}.{2}]",$this->reference[0],$this->reference[1])));
		}
		return true;
	}
	function isTypeBoolean(){
		return ($this->type == "boolean");
	}
	function isTypeTime(){
		return ($this->type == "time");
	}	
	function isTypeTimestamp(){
		return ($this->type == "timestamp");
	}
	function isTypeDate(){
		return ($this->type == "date");
	}
	function isTypeBirthday(){
		return ($this->type == "birthday");
	}
	function isDepend(){
		return (sizeof($this->dependList) > 0);
	}
	function isChoices(){
		return !empty($this->choices);
	}
	function isReference(){
		return !empty($this->reference);
	}
	function isDescription(){
		return !empty($this->description);
	}	
	function getChoicesString(){
		$result = "";
		foreach($this->choices as $key => $value){
			$result .= sprintf("\"%s\"=>Message::_(\"%s\"),",$key,$value);
		}
		return "array(".$result.")";
	}

	function getConstractArg(){
		$result = "";
		if(!empty($this->name)) $result .= "column=".$this->name.",";
		if(!empty($this->var)) $result .= "variable=".$this->var.",";
		if(!empty($this->type)) $result .= "type=".$this->type.",";
		if($this->size !== null) $result .= "size=".$this->size.",";
		if($this->max !== null) $result .= "max=".$this->max.",";
		if($this->min !== null) $result .= "min=".$this->min.",";
		if($this->require) $result .= "require=true,";
		if($this->primary) $result .= "primary=true,";
		if($this->unique) $result .= "unique=true,";
		if($this->dbtype) $result .= "dbtype=true,";
		if(!empty($this->seq)) $result .= "seq=".$this->seq.",";
		if(!empty($this->chartype)) $result .= "chartype=".str_replace(",","\\,",$this->chartype).",";
		
		$arg = ($this->isReference()) ? $this->reference[0]->method."::".$this->reference[1]->method : "";
		if(!empty($arg)) $result .= "reference=".$arg.",";
		
		$arg = (!empty($this->requirewith) && !empty($this->tableModel)) ? ($this->tableModel->method."::".$this->requirewith->method) : "";
		if(!empty($arg)) $result .= "requireWith=".$arg.",";

		$arg = (!empty($this->uniquewith)&& !empty($this->tableModel)) ? ($this->tableModel->method."::".$this->uniquewith->method) : "";
		if(!empty($arg)) $result .= "uniqueWith=".$arg.",";
		
		return $result;
	}
	
	function getChoices(){
		return $this->choices;
	}
}
?>