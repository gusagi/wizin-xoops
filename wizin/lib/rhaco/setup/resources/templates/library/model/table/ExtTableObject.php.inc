Rhaco::import("resources.Message");
Rhaco::import("database.model.TableObjectBase");
Rhaco::import("database.model.DbConnection");
Rhaco::import("model.{$table.table.method}");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("database.model.Table");
Rhaco::import("database.model.Column");
/**
 * #ignore
 * {$table.description}
 */
class {$table.method}Table extends {$table.table.method}{
<rt:include href="TableObjectVariableDef.inc" />
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","{$table.method}")){
			Rhaco::addVariable("_R_D_T_",new Table(parent::table(),__CLASS__),"{$table.method}");
		}
		return Rhaco::getVariable("_R_D_T_",null,"{$table.method}");
	}
	function columns(){
		return array(<rt:loop param="{$table.columnList}" var="column">{$table.method}::column{$column.method}(),</rt:loop>);
	}
<rt:loop param="{$table.columnList}" var="column">
	function column{$column.method}(){
		if(!Rhaco::isVariable("_R_D_C_","{$table.method}::{$column.method}")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::column{$column.method}(),__CLASS__),"{$table.method}::{$column.method}");
		}
		return Rhaco::getVariable("_R_D_C_",null,"{$table.method}::{$column.method}");			
	}
</rt:loop>
<rt:if param="{$table.change}">
	function __init__(){
		parent::__init__();
<rt:loop param="{$table.columnList}" var="column">
		$this->{$column.var} = {$column.default};
</rt:loop>
<rt:loop param="{$table.extraList}" var="column">
		$this->{$column.var} = {$column.default};
</rt:loop>
	}
<rt:loop param="{$table.columnList}" var="column">
<rt:if param="{$column.isReference()}">
	function getFact{$column.method}(){
		return $this->fact{$column.method};
	}
	function setFact{$column.method}($obj){
		$this->fact{$column.method} = $obj;
	}
</rt:if>
</rt:loop>
</rt:if>
<rt:include href="TableObjectReference.inc" />
}