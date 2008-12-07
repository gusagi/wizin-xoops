<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("generic.model.Paginator");
Rhaco::import("database.model.CriteriaPattern");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.IllegalArgumentException");
/**
 * database.model.Criterionに定義する条件式
 * 全て静的利用
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Criterion{
	/**
	 * ==
	 *
	 * @param Column $column
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function equal($column,$argB){
		/*** unit("database.QTest");  */
		return Criterion::add($column,$argB,1);
	}
	
	/**
	 * !=
	 *
	 * @param Column $column
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function notEqual($column,$argB){
		/*** unit("database.QTest");  */
		return Criterion::add($column,$argB,2);
	}
	
	/**
	 * <
	 *
	 * @param Column $column
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function greater($column,$argB){
		/*** unit("database.QTest");  */
		return Criterion::add($column,$argB,3);
	}
	
	/**
	 * <=
	 *
	 * @param Column $column
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function greaterEquals($column,$argB){
		/*** unit("database.QTest");  */
		return Criterion::add($column,$argB,4);
	}
	
	/**
	 * >
	 *
	 * @param Column $column
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function less($column,$argB){
		/*** unit("database.QTest");  */
		return Criterion::add($column,$argB,5);
	}
	
	/**
	 * >=
	 *
	 * @param Column $column
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function lessEquals($column,$argB){
		/*** unit("database.QTest");  */
		return Criterion::add($column,$argB,6);
	}
	
	/**
	 * like
	 *
	 * @param Column $column
	 * @param string $value
	 * @param string $agreement f:前方一致 r:後方一致 p:部分一致
	 * @return CriteriaPattern
	 */
	function like($column,$value,$agreement=""){
		/*** unit("database.QTest");  */
		if(StringUtil::isBlank($value)) return ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
		return Criterion::add($column,Criterion::likeword($value,$agreement),7);
	}
	
	/**
	 * not like
	 *
	 * @param Column $column
	 * @param string $value
	 * @param string $agreement f:前方一致 r:後方一致 p:部分一致
	 * @return CriteriaPattern
	 */
	function notLike($column,$value,$agreement=""){
		/*** unit("database.QTest");  */
		if(StringUtil::isBlank($value)) return ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
		return Criterion::add($column,Criterion::likeword($value,$agreement),8);
	}
	
	/**
	 * like 大文字小文字の区別なし
	 *
	 * @param Column $column
	 * @param string $value
	 * @param string $agreement f:前方一致 r:後方一致 p:部分一致
	 * @return CriteriaPattern
	 */
	function ilike($column,$value,$agreement=""){
		/*** unit("database.QTest");  */
		if(StringUtil::isBlank($value)) return ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
		return Criterion::add($column,Criterion::likeword($value,$agreement),9);
	}
	
	/**
	 * not like 大文字小文字の区別なし
	 *
	 * @param Column $column
	 * @param string $value
	 * @param string $agreement f:前方一致 r:後方一致 p:部分一致
	 * @return CriteriaPattern
	 */
	function notiLike($column,$value,$agreement=""){
		/*** unit("database.QTest");  */
		if(StringUtil::isBlank($value)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));			
			return null;			
		}
		return Criterion::add($column,Criterion::likeword($value,$agreement),10);
	}	
	
	/**
	 * in
	 *
	 * @param Column $column
	 * @param array $argB
	 * @param boolean $eq ==式に展開するか
	 * @return CriteriaPattern
	 */
	function in($column,$argB,$eq=false){
		/*** unit("database.QTest");  */
		if(!is_array($argB) || sizeof($argB) <= 0){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));			
			return null;
		}
		if($eq){
			$criteria = new Criteria();
			foreach($argB as $value){
				$criteria->addCriteria(new Criteria(Criterion::equal($column,$value)));
			}
			return $criteria;
		}
		return Criterion::add($column,$argB,11);
	}
	
	/**
	 * not in
	 *
	 * @param Column $column
	 * @param array $argB
	 * @param boolean $eq !=式に展開するか
	 * @return CriteriaPattern
	 */
	function notIn($column,$argB,$neq=false){
		/*** unit("database.QTest");  */
		if(!is_array($argB) || sizeof($argB) <= 0){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));			
			return null;
		}
		if($neq){
			$criteria = new Criteria();
			foreach($argB as $value){
				$criteria->addCriteria(new Criteria(Criterion::notEqual($column,$value)));
			}
			return $criteria;
		}
		return Criterion::add($column,$argB,12);
	}
	
	/**
	 * $column = (select $columnB from 〜)
	 *
	 * @param Column $column
	 * @param Column $columnB
	 * @param Criteria $criteria
	 * @return CriteriaPattern
	 */
	function getEqual($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column) || !Variable::istype("Column",$columnB)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		if($criteria === null) $criteria = new Criteria();
		return Criterion::add($column,array($columnB,$criteria),21);
	}

	/**
	 * $column <> (select $columnB from 〜)
	 *
	 * @param Column $column
	 * @param Column $columnB
	 * @param Criteria $criteria
	 * @return CriteriaPattern
	 */
	function getNotEqual($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column) || !Variable::istype("Column",$columnB)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		if($criteria === null) $criteria = new Criteria();		
		return Criterion::add($column,array($columnB,$criteria),22);
	}
	
	/**
	 * $column in (select $columnB from 〜)
	 *
	 * @param Column $column
	 * @param Column $columnB
	 * @param Criteria $criteria
	 * @return CriteriaPattern
	 */
	function selectIn($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column) || !Variable::istype("Column",$columnB)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		if($criteria === null) $criteria = new Criteria();		
		return Criterion::add($column,array($columnB,$criteria),23);
	}
	
	/**
	 * $column not in (select $columnB from 〜)
	 *
	 * @param Column $column
	 * @param Column $columnB
	 * @param Criteria $criteria
	 * @return CriteriaPattern
	 */
	function selectNotIn($column,$columnB,$criteria=null){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column) || !Variable::istype("Column",$columnB)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		if($criteria === null) $criteria = new Criteria();
		return Criterion::add($column,array($columnB,$criteria),24);
	}
	
	/**
	 * CriteriaPatternを返す
	 * Criterionから利用する
	 *
	 * @param Column $argA
	 * @param unknown_type $argB
	 * @param int $pattern
	 * @return CriteriaPattern
	 */
	function add($argA,$argB,$pattern=1){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$argA)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		return new CriteriaPattern($argA,$argB,$pattern);
	}
	
	/**
	 * offset,limitを定義する
	 *
	 * @param unknown_type $pagerOrLimit genereic.Paginator または integer 1 - 
	 * @param integer $offset 0 - 
	 * @return CriteriaPattern
	 */
	function pager($pagerOrLimit,$offset=0){
		/*** unit("database.QTest"); */
		if(!Variable::istype("Paginator",$pagerOrLimit)){
			$limit = intval($pagerOrLimit);
			if($pagerOrLimit > 0){
				$pagerOrLimit = new Paginator();
				$pagerOrLimit->setLimit($limit);
				$pagerOrLimit->setOffset($offset);
			}else{
				ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
				return null;
			}
		}
		return new CriteriaPattern($pagerOrLimit,null,100);
	}
	
	/**
	 * ソートオーダーを指定する (昇順)
	 *
	 * @param Column $column
	 * @return CriteriaPattern
	 */
	function order($column){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		return new CriteriaPattern($column,null,101);
	}
	
	/**
	 * ソートオーダーを指定する (降順)
	 *
	 * @param Column $column
	 * @return CriteriaPattern
	 */
	function orderDesc($column){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));	
			return null;
		}
		return new CriteriaPattern($column,null,102);
	}
	
	/**
	 * 結果をまとめる
	 *
	 * @param Column $column
	 * @return CriteriaPattern
	 */
	function distinct($column){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$column)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));			
			return null;
		}
		return new CriteriaPattern($column,null,103);
	}
	
	/**
	 * join
	 *
	 * @param Column $columnA
	 * @param Column $columnB
	 * @return CriteriaPattern
	 */
	function join($columnA,$columnB){
		/*** unit("database.QTest");  */
		if(!Variable::istype("Column",$columnA) || !Variable::istype("Column",$columnB)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		return new CriteriaPattern($columnA,$columnB,104);
	}
	
	/**
	 * 対象を指定する
	 * DbUtil::updateで使用する
	 *
	 * @param Column $column
	 * @return CriteriaPattern
	 */
	function which($column){
		/*** unit("database.DbUtilTest"); */
		if(!Variable::istype("Column",$column)){
			ExceptionTrigger::raise(new IllegalArgumentException(__FUNCTION__));
			return null;
		}
		return new CriteriaPattern($column,null,105);
	}
	
	/**
	 * get時に存在しなければ作成する
	 *
	 * @return CriteriaPattern
	 */
	function getNoneNew(){
		/*** unit("database.DbUtilTest"); */		
		return new CriteriaPattern(null,null,1000);
	}
	
	/**
	 * reference先を取得する
	 * TableObject::fact***にセットされる
	 *
	 * @return CriteriaPattern
	 */
	function fact(){
		/*** unit("database.DbUtilTest"); */
		return new CriteriaPattern(null,null,1001);
	}
	
	/**
	 * reference先を取得し結果をObjectUtil::mixinで拡張／マージする
	 *
	 * @return CriteriaPattern
	 */
	function flat(){
		/*** unit("database.QTest");  */
		return new CriteriaPattern(null,null,1003);
	}
	
	/**
	 * reference元を取得する
	 * TableObject::*****sにセットされる
	 *
	 * @return CriteriaPattern
	 */
	function depend(){
		/*** unit("database.QTest");  */
		return new CriteriaPattern(null,null,1004);
	}
	
	/**
	 * 行ロックをかける
	 *
	 * @return CriteriaPattern
	 */
	function lock(){
		/*** #pass */
		return new CriteriaPattern(null,null,1002);
	}

	/**
	 * AND式
	 * @param Criterion $criterion
	 * @param ....
	 *
	 * @return CriteriaPattern
	 */
	function andc(){
		/*** unit("database.QTest");  */
		$criteria = null;
		$args = func_get_args();
		if(sizeof($args) > 0 && Variable::istype("Criteria",$args[0])){
			$criteria = $args[0];
		}else{
			$criteria = new Criteria();
			foreach($args as $arg){
				$criteria->q($arg);
			}
		}
		return new CriteriaPattern($criteria,null,10001);
	}
	
	/**
	 * OR式
	 * @param Criterion $criterion
	 * @param ....
	 *
	 * @return CriteriaPattern
	 */
	function orc(){
		/*** unit("database.QTest");  */
		$criteria = null;
		$args = func_get_args();
		if(sizeof($args) > 0 && Variable::istype("Criteria",$args[0])){
			$criteria = $args[0];
		}else{
			$criteria = new Criteria();
			foreach($args as $arg){
				$criteria->q($arg);
			}
		}
		return new CriteriaPattern($criteria,null,10002);
	}
	
	/**
	 * likeに使用される
	 *
	 * @param string $word
	 * @param string $agreement
	 * @return string
	 */
	function likeword($word,$agreement=""){
		/***
		 * eq("hoge.*",Criterion::likeword("hoge","w*"));
		 * eq(".*hoge",Criterion::likeword("hoge","*w"));
		 * eq(".*hoge.*",Criterion::likeword("hoge","*"));
		 * eq("hoge",Criterion::likeword("hoge"));
		 */
		switch($agreement){
			case "*w": $word = ".*".$word; break;			
			case "w*": $word = $word.".*"; break;
			case "*": $word = ".*".$word.".*"; break;

			case "f":
				Logger::deprecated();
				$word = ".*".$word;
				break;
			case "r":
				Logger::deprecated();
				$word = $word.".*";
				break;
			case "p":
				Logger::deprecated();
				$word = ".*".$word.".*";
				break;
		}
		return $word;
	}
	

	/**
	 * 比較演算子によるエイリアス
	 *
	 * @param Column $argA
	 * @param string $cond
	 * @param unknown_type $argB
	 * @return CriteriaPattern
	 */
	function comp($argA,$cond,$argB){
		/*** unit("database.QTest"); */		
		switch($cond){
			case "=":
			case "==": return Criterion::equal($argA,$argB);
			case "!=":
			case "<>": return Criterion::notEqual($argA,$argB);
			case ">": return Criterion::greater($argA,$argB);
			case "<": return Criterion::less($argA,$argB);
			case "<=": return Criterion::lessEquals($argA,$argB);
			case ">=": return Criterion::greaterEquals($argA,$argB);
		}
		ExceptionTrigger::raise(new IllegalArgumentException($cond));
	}
	
	/**
	 * 前方一致
	 *
	 * @param unknown_type $columnList
	 * @param unknown_type $wordList
	 * @param unknown_type $not
	 * @return unknown
	 */
	function startswith($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,false,"w*");
	}
	
	/**
	 * 前方一致
	 *
	 * @param unknown_type $columnList
	 * @param unknown_type $wordList
	 * @param unknown_type $not
	 * @return unknown
	 */
	function istartswith($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,true,"w*");
	}
	
	/**
	 * 後方一致
	 *
	 * @param unknown_type $columnList
	 * @param unknown_type $wordList
	 * @param unknown_type $not
	 * @return unknown
	 */
	function endswith($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,false,"*w");
	}
	
	/**
	 * 後方一致
	 *
	 * @param unknown_type $columnList
	 * @param unknown_type $wordList
	 * @param unknown_type $not
	 * @return unknown
	 */
	function iendswith($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,true,"*w");
	}
	
	/**
	 * 部分一致
	 *
	 * @param unknown_type $columnList
	 * @param unknown_type $wordList
	 * @param unknown_type $not
	 * @return unknown
	 */
	function contains($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,false,"*");
	}
	
	/**
	 * 部分一致
	 *
	 * @param unknown_type $columnList
	 * @param unknown_type $wordList
	 * @param unknown_type $not
	 * @return unknown
	 */
	function icontains($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,true,"*");
	}
	function pattern($columnList,$wordList,$not=false,$ignore=false,$pattern=""){
		$criteria = new Criteria();
		if(!is_array($wordList)){
			$wordList = explode(" ",str_replace("　"," ",$wordList));
		}
		foreach(ArrayUtil::arrays($wordList) as $word){
			if(trim($word) != ""){
				$criteriaor = new Criteria();
				foreach(ArrayUtil::arrays($columnList) as $column){
					$criteriaor->addCriteriaOr(new Criteria(
													($not) ? 
														(($ignore) ? Criterion::notiLike($column,$word,$pattern) : Criterion::notLike($column,$word,$pattern)) :
														(($ignore) ? Criterion::ilike($column,$word,$pattern) : Criterion::like($column,$word,$pattern))
													));
				}
				$criteria->addCriteria($criteriaor);
			}
		}
		return $criteria;
	}
	function ipattern($columnList,$wordList,$not=false){
		return Q::pattern($columnList,$wordList,$not,true,"");
	}
}
?>