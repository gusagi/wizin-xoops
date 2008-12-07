<?php
class SortOrder{
	var $variables = array();
	var $desc = false;
	var $order;
	var $lowerorder;

	/**
	 * query用の変数をセットする
	 * @param array/string arrayOrKey
	 * @param unknown_type $value
	 */
	function setVariable($name,$value=null){
		if(!is_array($name)) $name = array($name=>$value);
		$this->variables = array_merge(ArrayUtil::arrays($this->variables),$name);
	}

	/**
	 * コンストラクタ
	 *
	 * @param unknown_type $variables queryで利用する変数
	 * @param unknown_type $order ソート対象のColumn名 (-Column名でdesc)
	 * @param unknown_type $prevorder 前回のソート対象のColumn名
	 * @return SortOrder
	 */
	function SortOrder($variables,$order,$prevorder=null){
		$this->variables = ArrayUtil::arrays($variables);
		$this->desc = (substr($order,0,1) == "-");
		$prevdesc = (substr($prevorder,0,1) == "-");
		$this->order = ($this->desc) ? substr($order,1) : $order;
		$prevorder = ($prevdesc) ? substr($prevorder,1) : $prevorder;

		if(strtolower($prevorder) == strtolower($this->order) && $prevdesc == $this->desc){
			$this->desc = !$this->desc;
		}
		$this->lowerorder = strtolower($this->order);
	}

	/**
	 * 現在のソート状態文字列
	 *
	 * @param unknown_type $columnname カラム名
	 * @return unknown
	 */
	function is($columnname){
		return (strtolower($columnname) == $this->lowerorder) ? (($this->desc) ? "desc" : "asc") : "";
	}

	/**
	 * リンクで使用するquery
	 *
	 * @param unknown_type $columnname カラム名
	 * @return unknown
	 */
	function query($columnname){
		$this->variables["po"] = (($this->desc) ? "-" : "").$this->order;
		$this->variables["o"] = (($this->lowerorder == strtolower($columnname) && !$this->desc) ? "-" : "").$columnname;
		return TemplateFormatter::httpBuildQuery($this->variables);
	}

	/**
	 * ソート用のCriterionを返す
	 *
	 * @param unknown_type $tableObject
	 * @return unknown
	 */
	function q($tableObject){
		foreach($tableObject->columns() as $column){
			if($column->equals($this->order)){
				return ($this->desc) ? Criterion::orderDesc($column) : Criterion::order($column);
			}
		}
		return null;
	}
}
?>