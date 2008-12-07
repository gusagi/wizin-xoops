<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("database.model.Criteria");
Rhaco::import("database.model.Criterion");
/**
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class ViewsUtil{
	var $dbUtil;	
	
	function ViewsUtil($dbUtil=null){
		$this->dbUtil = $dbUtil;
	}

	/**
	 * Column::typeに対応したHTML表現を返す
	 *
	 * @param unknown_type $table
	 * @param unknown_type $column
	 * @param unknown_type $select
	 * @param unknown_type $formatter
	 * @param unknown_type $confmethod
	 * @return unknown
	 */
	function columnString(&$table,&$column,$select=false,$formatter=true,$confmethod=null){
		if(empty($confmethod)) $confmethod = "views";
		if(method_exists($table,"toString".$column->variable())){
			return $table->{"toString".$column->variable()}($this->dbUtil);
		}
		if($select){
			if(sizeof($column->choices()) > 0){
				$value = TableObjectUtil::getter($table,$column);
				$result = sprintf('<select name="%s">',$column->variable());
				if(!$column->isRequire()){
					$result .= '<option value=""></option>';
				}
				foreach($column->choices() as $columnvalue => $caption){
					$selected = ($value == $columnvalue) ? " selected" : "";
					$result .= sprintf('<option value="%s"%s>%s</option>',$columnvalue,$selected,$caption);
				}
				$result .= "</select>";
				return $result;
			}
			if($column->isReference()){
				$criteria = null;

				if(method_exists($table,$confmethod)){
					$list = ArrayUtil::arrays($table->$confmethod());
			
					if(array_key_exists("reference_criteria",$list)){				
						foreach(ArrayUtil::arrays($list["reference_criteria"]) as $key => $criteria){
							if($column->equals($key)){
								$criteria = $list["reference_criteria"][$key];
								if(is_string($criteria) && method_exists($table,$criteria)) $criteria = $table->$criteria();
								break;
							}
						}
					}
				}
				$thisvalue = TableObjectUtil::getter($table,$column);
				$referencecolumn = $column->reference();
				$referenceobject = $referencecolumn->tableObject();
				$db = Variable::istype("DbUtil",$this->dbUtil) ? $this->dbUtil : new DbUtil($referenceobject->connection());				
				$result = sprintf('<select name="%s">',$column->variable());
				$options = $db->select($referenceobject,Variable::istype("Criteria",$criteria) ? $criteria : new Criteria());
				
				if(!empty($options)){
					$is = (get_class($options[0]) != $options[0]->toString());

					foreach($options as $obj){
						$value = TableObjectUtil::getter($obj,$referencecolumn);
						$label = ($is) ? $obj->toString() : $value;
						$selected = ($thisvalue === $value) ? " selected" : "";
						$result .= sprintf('<option value="%s"%s>%s</option>',
										TemplateFormatter::htmlencode($value),
										$selected,
										TemplateFormatter::htmlencode($label));
					}
				}
				$result .= "</select>";
				return $result;
			}
		}
		return ViewsUtil::form($table,$column,$formatter);
	}

	/**
	 * Column::typeに対応したHTML(form)表現を返す
	 *
	 * @param unknown_type $table
	 * @param unknown_type $column
	 * @param unknown_type $formatter
	 * @return unknown
	 */
	function form(&$table,$column,$formatter=false){
		switch($column->type()){
			case "email":
			case "string":
			case "float":
			case "integer":
			case "date":
			case "timestamp":
			case "time":
			case "birthday":
				$pattern = '<input class="'.$column->type().'" type="text" name="%s" value="%s" />';
				break;
			case "text":
				$pattern = '<textarea class="'.$column->type().'" name="%s" cols="70" rows="30">%s</textarea>';
				break;
			case "tel":
				$pattern = '<input class="'.$column->type().'" type="text" name="%s" value="%s" maxlength="11" size="15" />';
				break;
			case "zip":
				$pattern = '<input class="'.$column->type().'" type="text" name="%s" value="%s" maxlength="8" size="10" />';
				break;
			case "boolean":
				$pattern = '<select class="'.$column->type().'" name="%s">';
				$pattern .= '<option value="false">false</option>';
				$pattern .= '<option value="true"'.((TableObjectUtil::getter($table,$column)) ? " selected" : "").'>true</option>';
				$pattern .= '</select>';
				break;
			case "serial":
				$pattern = '<input class="'.$column->type().'" type="hidden" name="%s" value="%s" />';
				break;
			default:
				$pattern = '<input class="'.$column->type().'" type="hidden" name="%s" value="%s" />';
		}
		return sprintf($pattern,
					$column->variable(),
					TemplateFormatter::htmlencode(TableObjectUtil::getter($table,$column,$formatter)));
	}

}
?>