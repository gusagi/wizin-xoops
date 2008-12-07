<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("io.FileUtil");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("database.model.Table");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("exception.model.DataTypeException");
/**
 * database.model.Column用 データモデル
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Column{
	/** model.Tableオブジェクト */
	var $table = null;
	/** tableObject名 */
	var $tableclass = null;
	/** 実際のカラム名 */
	var $column = null;
	/** 型 */
	var $type = "string";
	/** プロパティ名 */
	var $variable = "";
	/** 表示名 */
	var $label = null;
	
	/** 一意か */
	var $primary = false;
	/** 必須か */
	var $require = false;
	/** ユニークか */
	var $unique = false;

	/** 最大length */
	var $size = null;
	/** 最小値 */
	var $min = null;
	/** 最大値 */	
	var $max = null;	
	/** 文字列パターン(正規表現) */
	var $chartype = null;
	/** 選択肢(hash) */
	var $choices = array();

	var $requireWith = null;
	var $uniqueWith = null;
	var $reference = null;
	var $depend = array();
	
	var $dbtype = false;
	var $seq = "";

	function Column($dictOrColumn,$class=null){
		if(Variable::istype("Column",$dictOrColumn)){
			ObjectUtil::copyProperties($dictOrColumn,$this,true);
		}else{
			$vals = ArrayUtil::dict($dictOrColumn,array("column","variable","type","size","require","requireWith","primary","unique","uniqueWith","chartype","min","max","label","reference","dbtype","seq","class"),true);
			$this->column	= $vals["column"];
			$this->variable	= empty($vals["variable"]) ? $this->column : $vals["variable"];
			$this->type		= TableObjectUtil::type($vals["type"]);
			$this->primary	= (Variable::bool($vals["primary"]) || $this->type == "serial") ? true : false;
			$this->require	= (Variable::bool($vals["require"]) || $this->type == "boolean" || $this->type == "serial") ? true : false;
			$this->unique	= Variable::bool($vals["unique"]);
			$this->dbtype = (Variable::bool($vals["dbtype"])) ? true : false;

			if($vals["min"] !== null) $this->min = intval($vals["min"]);
			if($vals["max"] !== null) $this->max = intval($vals["max"]);
			if($vals["size"] !== null) $this->size = intval($vals["size"]);

			if(!empty($vals["label"])) $this->label($vals["label"]);
			if(!empty($vals["chartype"])) $this->chartype($vals["chartype"]);
			if(!empty($vals["reference"])) $this->reference = $vals["reference"];
			if(!empty($vals["requireWith"])) $this->requireWith = $vals["requireWith"];
			if(!empty($vals["uniqueWith"])) $this->uniqueWith = $vals["uniqueWith"];
			if(!empty($vals["seq"])) $this->seq = $vals["seq"];
			
			$class = (empty($class)) ? $vals["class"] : $class;
		}
		if(!empty($class) && !Rhaco::isVariable("RHACO_CORE_DB_TABLE_OBJECTS",$class) && ObjectUtil::isMethod($class,"table")){
			$table = call_user_func(array($class,"table"));

			if(Variable::istype("Table",$table)){
				Rhaco::addVariable("RHACO_CORE_DB_TABLE_OBJECTS",$table,$class);
			}
		}
		$this->table = Rhaco::getVariable("RHACO_CORE_DB_TABLE_OBJECTS",null,$class);
		$this->tableclass = null;
	}
	function isRequire(){
		/***
		 * $column = new Column("column=id,variable=abc,type=serial,require=true");
		 * assert($column->isRequire());
		 * $column = new Column("column=id,variable=abc,type=integer,require=true");
		 * assert($column->isRequire());
		 * $column = new Column("column=id,variable=abc,type=string,");
		 * assert(!$column->isRequire());
		 */
		return $this->require;
	}
	function isChartype(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,chartype='^abc$'");
		 * assert($column->isChartype());
		 */
		return ($this->chartype != null);
	}
	function type(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string");
		 * eq("string",$column->type());
		 */
		return $this->type;
	}
	function variable(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string");
		 * eq("abc",$column->variable());
		 */
		return $this->variable;
	}
	function unique(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,unique=true,");
		 * assert($column->unique());
		 */
		return Variable::bool($this->unique);
	}
	function size(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,size=21,");
		 * eq(21,$column->size());
		 */
		return $this->size;
	}
	function min(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,min=3,");
		 * eq(3,$column->min());
		 */
		return $this->min;
	}
	function max(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,max=3,");
		 * eq(3,$column->max());
		 */
		return $this->max;
	}
	function seq(){
		return (empty($this->seq)) ? sprintf("%s_%s_seq",$this->sqltablename(),$this->sqlname()) : $this->seq;
	}

	function label(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,max=3,");
		 * $column->label("hoge");
		 * eq("hoge",$column->label());
		 */
		if(func_num_args() > 0) $this->label = func_get_arg(0);
		return (empty($this->label)) ? $this->column : $this->label;
	}
	function chartype(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,chartype='^abc$'");
		 * eq("^abc$",$column->chartype());
		 */
		if(func_num_args() > 0) $this->chartype = func_get_arg(0);
		return $this->chartype;
	}
	function choices(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string");
		 * $choice = array("a"=>"A","b"=>"B");
		 * $rchoice = array("a"=>"A","b"=>"B");
		 * $column->choices($choice);
		 * eq($rchoice,$column->choices());
		 * 
		 * $column = new Column("column=id,variable=abc,type=integer");
		 * $choice = array("1"=>"A","2"=>"B");
		 * $rchoice = array(1=>"A",2=>"B");
		 * $column->choices($choice);
		 * eq($rchoice,$column->choices());
		 * 
		 * $column = new Column("column=id,variable=abc,type=boolean");
		 * $choice = array("true"=>"A","false"=>"B");
		 * $rchoice = array(1=>"A",0=>"B");
		 * $column->choices($choice);
		 * eq($rchoice,$column->choices());
		 */		
		if(func_num_args() > 0 && is_array(func_get_arg(0))){
			foreach(func_get_arg(0) as $key => $value){
				$this->choices[TableObjectUtil::cast($key,$this)] = $value;
			}
		}
		return $this->choices;
	}
	
	function depend(){
		if(func_num_args() > 0) $this->depend = func_get_args();
		$depends = array();
		foreach($this->depend as $depend){
			if($this->_toColumn($depend) !== false) $depends[] = $depend;
		}
		return $depends;
	}
	function sqlname(){
		/***
		 * $column = new Column("column=id,variable=abc,type=string,");
		 * eq("id",$column->sqlname());
		 */
		return $this->column;
	}
	function sqltablename(){
		if(!is_object($this->table)) ExceptionTrigger::raise(new DataTypeException($this->variable));
		return $this->table->name;
	}
	function sqltablealias(){
		if(!is_object($this->table)) ExceptionTrigger::raise(new DataTypeException($this->variable));
		return $this->table->class;
	}
	function tableObject(){
		if(empty($this->tableclass)){
			$this->tableclass = $this->table->class;
			$table = substr($this->tableclass,0,-5);
			if(!empty($table) && class_exists($table)) $this->tableclass = $table;
			if(!class_exists($this->tableclass)) $this->tableclass = null;
		}
		$class = $this->tableclass;
		return (!empty($class)) ? new $class() : null;
	}
	function isSerial(){
		/***
		 * $column = new Column("column=id,variable=abc,type=serial,");
		 * assert($column->isSerial());
		 * $column = new Column("column=id,variable=abc,type=string,");
		 * assert(!$column->isSerial());
		 */
		return ($this->type == "serial");
	}
	function requireWith(){
		return $this->_toColumn($this->requireWith);
	}
	function reference(){
		return $this->_toColumn($this->reference);
	}
	function uniqueWith(){
		return $this->_toColumn($this->uniqueWith);
	}
	function isRequireWith(){
		return (!empty($this->requireWith));
	}
	function isUniqueWith(){
		return (!empty($this->uniqueWith));
	}
	function isDepend(){
		return (!empty($this->depend));
	}
	function isReference(){
		return (!empty($this->reference));
	}
	function isChoices(){
		return (!empty($this->choices));
	}
	
	function getXXColumn(){
		if(!Rhaco::isVariable("RHACO_CORE_DB_ALIAS_TABLE_NAME",$this->sqltablealias())){
			Rhaco::addVariable("RHACO_CORE_DB_ALIAS_TABLE_NAME","t".sizeof(Rhaco::getVariable("RHACO_CORE_DB_ALIAS_TABLE_NAME")),$this->sqltablealias());
		}
		if(!Rhaco::isVariable("RHACO_CORE_DB_ALIAS_COLUMN_NAME",$this->getColumnFullname())){
			Rhaco::addVariable("RHACO_CORE_DB_ALIAS_COLUMN_NAME","c".sizeof(Rhaco::getVariable("RHACO_CORE_DB_ALIAS_COLUMN_NAME")),$this->getColumnFullname());
		}
		return Rhaco::getVariable("RHACO_CORE_DB_ALIAS_TABLE_NAME",$this->sqltablealias(),$this->sqltablealias()).
					Rhaco::getVariable("RHACO_CORE_DB_ALIAS_COLUMN_NAME",$this->getColumnFullname(),$this->getColumnFullname());
	}	
	function getColumnFullname($format="%s"){
		return sprintf($format,$this->sqltablealias()).".".sprintf($format,$this->sqlname());
	}	
	function getTableNameAs($format="%s"){
		return sprintf($format,$this->sqltablename())." ".sprintf($format,$this->sqltablealias());		
	}
	function _toColumn(&$columnString){
		if($columnString == null || Variable::istype("Column",$columnString)) return $columnString;
		list($class,$variable) = explode("::",$columnString);
		if(!class_exists($class)) Rhaco::import("model.".$class);
		if(ObjectUtil::isMethod($class,"column".$variable)){
			$columnString = call_user_func(array($class,"column".$variable));
			return $columnString;
		}
		return ExceptionTrigger::raise(new IllegalArgumentException($columnString));
	}
	
	function equals($name){
		$name = strtolower(str_replace("_","",$name));
		return ($name == strtolower(str_replace("_","",$this->variable)) || $name == strtolower(str_replace("_","",$this->column)));
	}
}
?>