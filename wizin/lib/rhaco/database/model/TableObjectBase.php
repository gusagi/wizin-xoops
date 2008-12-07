<?php
Rhaco::import("database.DbUtil");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.DateUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.Variable");
Rhaco::import("database.model.Table");
Rhaco::import("database.model.Column");
Rhaco::import("database.model.Criteria");
Rhaco::import("database.model.Criterion");
/**
 * 各種TableObjectのベース
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class TableObjectBase{
	function TableObjectBase(){
		$args = func_get_args();

		if(sizeof($args) > 0){
			$vars = get_class_vars(get_class($this));
			$i = 0;

			foreach ($this->columns() as $column){
				if($column->primary || strtolower($column->type) == "serial"){
					foreach($vars as $key => $value){
						if(strcasecmp($key,$column->variable) == 0){
							$this->$key = $args[$i++];
							break;
						}
					}
				}
			}
		}
	}

	/**
	 * DELETEを発行する前の処理
	 *
	 * @param database.DbUTil $db
	 */
	function beforeDelete(&$db,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		$boolean = true;
		if(Variable::istype("DbUtil",$db)){
			foreach($this->columns() as $column){
				foreach($column->depend() as $dependcolumn){
					foreach($db->select($dependcolumn->tableObject(),
						new Criteria(Criterion::equal($dependcolumn,TableObjectUtil::getter($this,$column)))) as $obj){
							$obj->drop($db);
					}
				}
			}
		}
		return $boolean;
	}

	/**
	 * このオブジェクトをデータベースに保存する
	 * このデータを存在すればUPDATE,存在しなければINSERT
	 *
	 * @param database.DbUTil $db
	 * @return database.model.TableObjectBase
	 */
	function save($db=null){
		/*** unit("database.DbUtilTest"); */
		if(!Variable::istype("DbUtil",$db)) $db = new DbUtil($this->connection());
		if(TableObjectUtil::setaccessor($this)){
			foreach($this->primaryKey() as $column){
				$value = TableObjectUtil::getter($this,$column);
				if(is_null($value)){
					$obj = $db->insert($this);
					return ($obj !== false) ? ObjectUtil::copyProperties($obj,$this,true) : false;
				}
			}
			$obj = $db->get($this);

			if($obj === null || $obj === false){
				$obj = $db->insert($this);
				if($obj !== false) return ObjectUtil::copyProperties($obj,$this,true);
			}else{
				if($db->update($this)) return $db->get($this);
			}
		}
		return false;
	}
	
	/**
	 * このオブジェクトをデータベースから削除する
	 *
	 * @return boolean
	 */
	function drop($db=null){
		/*** unit("database.DbUtilTest"); */
		if(!Variable::istype("DbUtil",$db)) $db = new DbUtil($this->connection());
		return $db->delete($this);		
	}
	/**
	 * このテーブル用のdatabase.model.DbConnectionBaseを返す
	 *
	 * @return database.model.DbConnectionBase
	 */
	function connection(){
		/*** unit("database.model.TableObjectBaseTest"); */
		return null;
	}

	/**
	 * このテーブルのprimaryKeyを返す
	 *
	 * @return array databse.model.Column
	 */	
	function primaryKey(){
		/*** unit("database.model.TableObjectBaseTest"); */
		$keys = array();
		foreach($this->columns() as $column){
			if($column->primary || $column->type == "serial") $keys[] = $column;
		}
		return $keys;
	}
	/**
	 * このテーブルのprimaryKeyの値を返す
	 */
	function primaryKeyValue(){
		/*** unit("database.model.TableObjectBaseTest"); */
		$list = array();
		foreach($this->primaryKey() as $column) $list[$column->variable] = TableObjectUtil::getter($this,$column);
		return $list;
	}
	
	/**
	 * このテーブルのcolumを返す
	 *
	 * @return array databse.model.Column
	 */
	function columns(){
		/*** unit("database.model.TableObjectBaseTest"); */
		$name = get_class($this);
		if(!Rhaco::isVariable("RHACO_CORE_DB_TABLE_COLUMNS",$name)){
			$columns = array();
			foreach(get_class_methods($this) as $method){
				if($method != "columns" && strpos($method,"column") === 0) $columns[] = $this->$method();
			}
			Rhaco::addVariable("RHACO_CORE_DB_TABLE_COLUMNS",$columns,$name);
		}
		return Rhaco::getVariable("RHACO_CORE_DB_TABLE_COLUMNS",array(),$name);
	}
	/**
	 * このテーブルのextraカラムを返す
	 *
	 * @return unknown
	 */
	function extra(){
		/*** unit("database.model.TableObjectBaseTest"); */
		$name = get_class($this);
		if(!Rhaco::isVariable("RHACO_CORE_DB_TABLE_EXTRAS",$name)){
			$columns = array();
			foreach(get_class_methods($this) as $method){
				if($method != "extra" && strpos($method,"extra") === 0) $columns[] = $this->$method();
			}
			Rhaco::addVariable("RHACO_CORE_DB_TABLE_EXTRAS",$columns,$name);
		}
		return Rhaco::getVariable("RHACO_CORE_DB_TABLE_EXTRAS",array(),$name);
	}
	/**
	 * テーブル情報を返す
	 *
	 * @return unknown
	 */
	function table(){
		/*** unit("database.model.TableObjectBaseTest"); */
		return new Table();
	}
	/**
	 * このオブジェクトの文字列表現
	 * @return string
	 */
	function toString(){
		/*** unit("database.model.TableObjectBaseTest"); */
		return get_class($this);
	}

	function isSerial(){
		/*** unit("database.model.TableObjectBaseTest"); */
		if(sizeof($this->primaryKey()) == 1){
			list($column) = $this->primaryKey();
			return $column->isSerial();
		}
		return false;
	}
	
	/**
	 * columnにひもづく値を取得
	 *
	 * @param database.model.Column $column
	 * @param bookaen $formatter
	 * @return unknown
	 */
	function value($column,$formatter=false){
		/*** unit("database.model.TableObjectBaseTest"); */
		if(is_string($column)) $column = $this->_getColumn($column);		
		if(Variable::istype("Column",$column)){
			if($formatter){
				$value = TableObjectUtil::getter($this,$column);

				if($column->isReference()){
					$rcolumn = $column->reference();
					$ref = ObjectUtil::getter($this,"fact".$column->variable());
					if(Variable::istype($rcolumn->tableObject(),$ref) && get_class($ref) != $ref->toString()){
						return $ref->toString();
					}
				}else if($column->isChoices()){
					return TableObjectUtil::caption($this,$column);
				}
			}
			return TableObjectUtil::getter($this,$column,$formatter);
		}
		return null;
	}
	/**
	 * columnのラベルを取得
	 *
	 * @param database.model.Column $column
	 * @return unknown
	 */
	function label($column=null){
		/*** unit("database.model.TableObjectBaseTest"); */
		if($column == null) return null;
		if(is_string($column)) $column = $this->_getColumn($column);
		if(Variable::istype("Column",$column)) return $column->label;
		return null;
	}
	
	/**
	 * 対象のcolumn配列を返す
	 * 主にgeneric.ViewsUtil,generic.Viewsのテンプレートで利用される
	 *
	 * @param string $confmethod 定義メソッド名
	 * @param string $confkey 対象の定義キー名 自由定義(search_fields,ordering,form_display,list_display....)
	 * @param boolean $onlyDbModel columnのみを対象にするか。 extraを含まない場合はfalse
	 * @return array array(database.model.Column)
	 */
	function models($confmethod,$confkey,$onlyDbModel=false){
		/*** unit("database.model.TableObjectBaseTest"); */
		$objects = array();
		foreach(get_class_methods($this) as $method){
			if((strpos($method,"column") === 0 && $method != "columns") ||
				(!$onlyDbModel && strpos($method,"extra") !== false && $method != "extra")
			){
				$obj = $this->{$method}();
				if(Variable::istype("Column",$obj)) $objects[$obj->variable()] = $obj;
			}
		}
		if(!empty($confmethod) && method_exists($this,$confmethod)){
			$methodresult = ArrayUtil::arrays($this->{$confmethod}());

			if(!empty($confkey) && !empty($methodresult) && array_key_exists($confkey,$methodresult)){
				$sortresult = array();

				foreach(explode(",",$methodresult[$confkey]) as $disp){
					foreach($objects as $var => $column){
						if($column->equals($disp)){
							$sortresult[$var] = $column;
							break;
						}
					}
				}
				return $sortresult;
			}
		}
		return $objects;
	}
	
	/**
	 * verifyで定義する名前を取得
	 *
	 * @param database.model.Column $column
	 * @return unknown
	 */
	function validName($column){
		/*** unit("database.model.TableObjectBaseTest"); */
		return strtolower(get_class($this))."_".$column->variable;
	}
	
	function _getColumn($variableName){
		foreach($this->columns() as $column){
			if($column->equals($variableName)) return $column;
		}
		return null;
	}
}
?>