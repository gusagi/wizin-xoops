<?php
Rhaco::import("database.model.Criterion");
/**
 * database.model.Criterionのエイリアス
 * 全て静的利用
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class Q extends Criterion{
	function geq($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		return Criterion::getEqual($column,$columnB,$criteria);
	}
	function gneq($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */		
		return Criterion::getNotEqual($column,$columnB,$criteria);
	}
	function sin($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		return Criterion::selectIn($column,$columnB,$criteria);		
	}
	function snin($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		return Criterion::selectNotIn($column,$columnB,$criteria);		
	}
	function goc(){
		/*** unit("database.QTest");  */		
		return Criterion::getNoneNew();
	}	
	function eq($argA,$argB){
		/*** unit("database.QTest");  */
		return Criterion::equal($argA,$argB);
	}
	function neq($argA,$argB){
		/*** unit("database.QTest");  */
		return Criterion::notEqual($argA,$argB);
	}
	function gt($argA,$argB){
		/*** unit("database.QTest");  */		
		return Criterion::greater($argA,$argB);
	}
	function gte($argA,$argB){
		/*** unit("database.QTest");  */		
		return Criterion::greaterEquals($argA,$argB);
	}	
	function lt($argA,$argB){
		/*** unit("database.QTest");  */		
		return Criterion::less($argA,$argB);
	}
	function lte($argA,$argB){
		/*** unit("database.QTest");  */
		return Criterion::lessEquals($argA,$argB);
	}
}
?>