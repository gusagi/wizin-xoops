<?php
Rhaco::import("abbr.V");
Rhaco::import("abbr.Q");
Rhaco::import("database.model.CriteriaPattern");
Rhaco::import("database.model.Criterion");
Rhaco::import("generic.model.Paginator");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.ClassTypeException");
Rhaco::import("lang.Variable");
/**
 * database.DbUtilで利用する条件設定クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Criteria{
	var $criteriaList					= array();	
	var $criteriaPatternColumnValueList	= array();
	var $criteriaPatternColumnColumnList = array();
	var $orderList						= array();
	var $joinList						= array();
	var $distinctList					= array();
	var $whichList						= array();
	var $lock		= false;
	var $getNoneNew	= false;
	var $unionAll	= false;
	var $fact		= false;
	var $flat		= false;
	var $depend		= false;
	var $paginator	= null;
	
	/**
	 * 条件式を定義する
	 * 引数が２つでどちらもdatabase.model.Columnだった場合はaddEqualをしてセット
	 * 
	 * @return database.model.Criteria
	 */
	function Criteria(){
		$args = func_get_args();

		if(sizeof($args) == 2 && Variable::istype("Column",$args[0])){
			$this->q(Criterion::equal($args[0],$args[1]));
		}else{
			foreach(func_get_args() as $criteriaPattern) $this->_add($criteriaPattern);
		}
	}

	/**
	 * 条件式を定義する
	 * 
	 * @return database.model.Criteria
	 */
	function q(){
		/*** unit("database.QTest"); */
		foreach(func_get_args() as $criteriaPattern) $this->_add($criteriaPattern);
		return $this;
	}
	
	/**
	 * ANDで条件を結合する
	 */
	function addCriteria($criteria){
		/*** unit("database.CriteriaTest"); */
		return $this->_addCriteria($criteria,1);
	}
	/**
	 * ORで条件を結合する
	 */
	function addCriteriaOr($criteria){
		/*** unit("database.CriteriaTest"); */
		return $this->_addCriteria($criteria,2);
	}

	/**
	 * ロックを定義する
	 */
	function setLock($value){
		$this->lock = Variable::bool($value);
		return $this;
	}
	
	/**
	 * UNION時にUNION ALLとして発行するかを決める
	 */
	function setUnionAll($boolean=false){
		$this->unionAll = $boolean;
		return $this;
	}
	
	function _addCriteria($criteria,$pattern=1){
		if(!Variable::istype("Criteria",$criteria)){		
			ExceptionTrigger::raise(new ClassTypeException(__FUNCTION__));
		}else{
			if(empty($this->orderList)) $this->orderList = $criteria->orderList;
			if(is_null($this->paginator)) $this->paginator = $criteria->paginator;
			$this->criteriaList[] = new CriteriaPattern($criteria,null,$pattern);
		}
		return $this;
	}
	function _add($criteriaPattern){
		if(Variable::istype("CriteriaPattern",$criteriaPattern)){
			if($criteriaPattern->pattern < 100){
				if($criteriaPattern->isColumn()){
					$this->criteriaPatternColumnColumnList[] = $criteriaPattern;
				}else{
					$this->criteriaPatternColumnValueList[] = $criteriaPattern;
				}
			}else if($criteriaPattern->pattern == 100){
				$this->paginator = $criteriaPattern->argA;
			}else if($criteriaPattern->pattern == 101 || $criteriaPattern->pattern == 102){
				$this->orderList[] = $criteriaPattern;
			}else if($criteriaPattern->pattern == 103){
				$this->distinctList[] = $criteriaPattern->argA;
			}else if($criteriaPattern->pattern == 104){
				$this->joinList[] = $criteriaPattern;
			}else if($criteriaPattern->pattern == 105){
				$this->whichList[] = $criteriaPattern->argA;
			}else if($criteriaPattern->pattern == 1000){
				$this->getNoneNew = true;
			}else if($criteriaPattern->pattern == 1001){
				$this->fact = true;
			}else if($criteriaPattern->pattern == 1002){
				$this->lock = true;
			}else if($criteriaPattern->pattern == 1003){
				$this->flat = true;
				$this->fact = true;
			}else if($criteriaPattern->pattern == 1004){
				$this->depend = true;
			}else if($criteriaPattern->pattern == 10001){
				$this->addCriteria($criteriaPattern->argA);
			}else if($criteriaPattern->pattern == 10002){
				$this->addCriteriaOr($criteriaPattern->argA);
			}
		}else if(Variable::istype("Criteria",$criteriaPattern)){
			$this->addCriteria($criteriaPattern);
		}
	}
	
	function isPaginator(){
		return ($this->paginator !== null);
	}
	function getPaginator($page=1,$variables=array()){
		$paginator = (Variable::istype("Paginator",$this->paginator)) ? $this->paginator : new Paginator();		
		$paginator->setPage($page);
		$paginator->setVariable($variables);
		return $paginator;
	}
	function getLimit(){
		return $this->paginator->limit;
	}
	function getOffset(){
		return $this->paginator->offset;
	}
	function isLock(){
		return $this->lock;
	}
	function isDistinct(){
		return (sizeof($this->distinctList) > 0);
	}	
	function isUnionAll(){
		return Variable::bool($this->unionAll);
	}
	

	/**
	 * 条件を空にする
	 */
	function clear(){
		/*** unit("database.QTest"); */
		$this->criteriaList						= array();	
		$this->criteriaPatternColumnValueList	= array();
		$this->criteriaPatternColumnColumnList	= array();
		$this->orderList						= array();
		$this->joinList							= array();
		$this->whichList						= array();
		$this->paginator						= null;
		$this->lock								= false;
		$this->distinctList						= array();		
		$this->unionAll							= false;
		$this->getNoneNew						= false;
		$this->fact								= false;
		$this->flat								= false;
		$this->depend							= false;
	}

	/**
	 * 条件をもつか
	 * fact/flat/depend は例外
	 */
	function isCond(){
		/*** unit("database.QTest"); */
		return (empty($this->criteriaList) &&
				empty($this->criteriaPatternColumnValueList) &&
				empty($this->criteriaPatternColumnColumnList) &&
				empty($this->orderList) &&
				empty($this->joinList) &&
				empty($this->whichList) &&	
				$this->paginator == null &&
				empty($this->distinctList) &&
				$this->unionAll == false &&
				$this->getNoneNew == false
		);
		
	}
}
?>