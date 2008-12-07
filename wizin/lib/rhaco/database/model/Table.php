<?php
Rhaco::import("lang.Variable");
/**
 * database.model.TableObjectBase用 データモデル
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Table{
	/** 実際のテーブル名 */
	var $name;
	/** model.TebleObjectクラス名 */
	var $class;

	function Table($nameOrTable,$class=null){
		$this->name	= (Variable::istype("Table",$nameOrTable)) ? $nameOrTable->name : $nameOrTable;
		$this->class = (empty($class)) ? $this->name : $class;
	}
}
?>