<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("tag.model.TemplateFormatter");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class TableObjectUtil{	
	/**
	 * TableObjectのcolumnを利用できるように定義する
	 *
	 * @static 
	 * @param TableObjectBase database.model.TableObjectBase $tableObject
	 * @return boolean
	 */
	function setaccessor($tableObject){
		return ObjectUtil::setaccessor($tableObject,array("TableObjectUtil","cast"));
	}
	
	/**
	 * TableObjectの指定のカラムの値を取り出す
	 *
	 * @static 
	 * @param TableObjectBase database.model.TableObjectBase $tableObject
	 * @param column database.model.Column $column
	 * @param boolean $formatter
	 * @return unknown
	 */
	function getter(&$tableObject,$column,$formatter=false){
		if(TableObjectUtil::setaccessor($tableObject)) return ObjectUtil::getter($tableObject,$column->variable(),$formatter,$column->type());
		return null;
	}
	/**
	 * TableObjectの指定のカラムに値をセットする
	 *
	 * @static 
	 * @param TableObjectBase database.model.TableObjectBase $tableObject
	 * @param column database.model.Column $column
	 * @param unknown_type $value
	 * @return $value
	 */
	function setter(&$tableObject,$column,$value){
		if(TableObjectUtil::setaccessor($tableObject)) return ObjectUtil::setter($tableObject,$column->variable(),$value,$column->type());
		return null;
	}
	
	/**
	 * 指定の型に適した値を返す
	 *
	 * @static 
	 * @param unknown_type $value
	 * @param string $type
	 * @return unknown
	 */	
	function cast($value,$type=null){
		if(Variable::istype("Column",$type)) $type = $type->type;
		switch($type){
			case "timestamp":
			case "date":
				return DateUtil::parseString(StringUtil::convertZenhan($value));
			case "boolean":
				return (Variable::bool($value)) ? 1 : 0;
			case "email":
			case "tel":
			case "zip":
				return StringUtil::convertZenhan($value);
			case "integer":
			case "serial":
				$value = StringUtil::convertZenhan($value);
				return (is_null($value) || !is_numeric($value)) ? null : intval(sprintf("%d",StringUtil::convertZenhan($value)));
			case "float":
				$value = StringUtil::convertZenhan($value);
				return (is_null($value) || !is_numeric($value)) ? null : floatval(sprintf("%f",StringUtil::convertZenhan($value)));
			case "time":
				return DateUtil::parseTime(StringUtil::convertZenhan($value));
			case "birthday":
				return DateUtil::parseIntDate(StringUtil::convertZenhan($value));
			default:
				return $value;
		}
	}
	
	/**
	 * 有効な型を返す
	 *
	 * @static 
	 * @param string $type
	 * @return unknown
	 */
	function type($type){
		$type = strtolower($type);
		if($type == "email" || $type == "text" || $type == "tel" || $type == "zip" || $type == "integer" || $type == "serial"
			|| $type == "time" || $type == "float" || $type == "date" || $type == "timestamp" || $type == "boolean"
			|| $type == "birthday"
		){
			return $type;
		}
		return "string";
	}
	
	/**
	 * 文字列型か
	 *
	 * @static 
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeString($column){
		return ($column->type == "email" || $column->type == "string" || $column->type == "text" || $column->type == "tel" || $column->type == "zip");
	}
	
	/**
	 * 数値型か
	 *
	 * @static 
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeInt($column){
		return ($column->type == "integer" || $column->type == "serial");
	}
	
	/**
	 * float型か
	 *
	 * @static
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeFloat($column){
		return ($column->type == "float");
	}
	
	/**
	 * 時間型か
	 * 
	 * @static 
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeTime($column){
		return ($column->type == "time");
	}
	
	/**
	 * 日付型か
	 *
	 * @static 
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeDate($column){
		return ($column->type == "date" || $column->type == "timestamp");
	}
	
	/**
	 * boolean型か
	 *
	 * @static 
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeBool($column){
		return ($column->type == "boolean");
	}
	
	/**
	 * birthday型か
	 *
	 * @static 
	 * @param unknown_type $column
	 * @return unknown
	 */
	function isTypeBirthday($column){
		return ($column->type == "birthday");		
	}
	
	
	/**
	 * TableObjectのリストを<select>に適した形式に変換する
	 * ex.
	 *  ::options(choiceをもつColumn)
	 *  ::options($tableObjectList,value値に使用するColumn,caption値に使用するColumn)
	 * 
	 * @static
	 * @return array
	 */
	function options(){
		$result = array();
		
		switch(func_num_args()){
			case 3:
				list($tableObjectList,$valueColumn,$captionColumn) = func_get_args();
				foreach(ArrayUtil::arrays($tableObjectList) as $object){
					$result[TableObjectUtil::getter($object,$valueColumn)] = TableObjectUtil::getter($object,$captionColumn);
				}
				break;
			case 1:
				list($column) = func_get_args();
				if(Variable::istype("Column",$column)){
					$result = $column->choices();
				}
				break;
		}
		return $result;
	}
	
	/**
	 * TableObjectの指定のColumnの値に対応したchoiceのcaptionを返す
	 *
	 * @param datbase.model.TableObjectBase $tableObject
	 * @param database.model.Column $column
	 * @return string
	 */
	function caption($tableObject,$column){
		$choices = $column->choices();
		$value = TableObjectUtil::getter($tableObject,$column);
		return (isset($choices[$value])) ? $choices[$value] : null;
	}
}
?>