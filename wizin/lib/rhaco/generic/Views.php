<?php
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.model.TemplateFormatter");
Rhaco::import("network.http.Header");
Rhaco::import("database.DbUtil");
Rhaco::import("database.model.Criteria");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("generic.util.ViewsUtil");
Rhaco::import("generic.Flow");
Rhaco::import("network.http.RequestLogin");
Rhaco::import("generic.filter.ViewsFilter");
Rhaco::import("generic.model.Paginator");
Rhaco::import("generic.model.SortOrder");
Rhaco::import("database.model.Criterion");
Rhaco::import("util.Logger");
Rhaco::import("io.FileUtil");
/**
 * CRUDを実現するクラス
 *
 * @author makoto tsuyuki
 * @author kazutaka tokushima
 * @license New BSD License
 * @copyright Copyright 2006- rhaco project. All rights reserved.
 */
class Views extends Flow{
	var $dbUtil = null;
	var $filters = array();
	var $params = array();

	function Views(){
		$args = func_get_args();
		$this->__init__($args);
	}
	function __init__($args=null){
		$args = ArrayUtil::arrays($args);
		foreach($args as $key => $arg){
			if(Variable::istype("DbUtil",$arg)){
				$this->dbUtil = $arg;
				unset($args[$key]);
				break;
			}
		}
		parent::__init__($args);
		$this->setFilter($args);
	}
	function setParam($params){
		$this->params = $params;
	}
	function getParam($key=null){
		return (isset($this->params[$key])) ? $this->params[$key] : null;
	}
	function setFilter($args){
		parent::setFilter($args);
		$this->filters = array_merge($this->filters,ObjectUtil::loadObjects($args,array("afterCreate","afterUpdate","afterDrop")));
	}
	function _addFitler($filters){
		$filters = ArrayUtil::arrays($filters);
		$this->setFilter($filters);
		if(empty($this->filters)) $this->setFilter("generic.filter.ViewsFilter");
	}
	function trans($bool){
		/*** #pass see database.DbUtil::trans */
		$this->dbUtil->trans($bool);
	}

	function _connection(&$tableObject){
		if(Variable::istype("TableObjectBase",$tableObject)){
			if($this->dbUtil == null) $this->dbUtil = new DbUtil(call_user_func_array(array($tableObject,"connection"),array()));
			return true;
		}
		return false;
	}

	/**
	 * csvインポート
	 *
	 * @param unknown_type $tableObject
	 * @return unknown
	 */
	function import($tableObject){
		if($this->_connection($tableObject)){
			$this->dbUtil->import($tableObject,$this->readFile("importfile"));
			return $this->read($tableObject,null,null,null,null);
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_title",Message::_("import"));
		return $this->parser();
	}

	/**
	 * 一覧を取得する
	 *
	 * @param database.model.TableObject $tableObject
	 * @param database.model.Criteria $criteria
	 * @param string $confmethod
	 * @return HtmlParser tag.HtmlParser
	 */
	function read($tableObject,$criteria=null,$confmethod="views"){
		if($this->_connection($tableObject)){
			if(!Variable::istype("Criteria",$criteria)) $criteria = new Criteria();
			$this->_readCriteria($tableObject,$criteria,$confmethod);
			$criteria->q(Criterion::pager($criteria->getPaginator($this->getVariable("page",1),$this->getVariable())));
			$criteria->q(Criterion::fact());
			$this->setVariable("object_list",$this->dbUtil->select($tableObject,$criteria));
			$this->setVariable("pager",$this->dbUtil->getPaginator());
		}else{
			$this->_notFound();
		}
		$this->_setParser($tableObject,sprintf("generic/%s_list.html", strtolower(get_class($tableObject))),"list.html");
		$this->setVariable("generic_title",Message::_("list"));
		return $this->parser();
	}

	function _readCriteria(&$tableObject,&$criteria,$confmethod){
		$sortorder = new SortOrder($this->getVariable(),$this->getVariable("o"),$this->getVariable("po"));
		$criteria->q($sortorder->q($tableObject));

		if($this->getVariable("q","") != ""){
			if(strpos($this->getVariable("q"),"=") !== false){
				$qcriteria = new Criteria();
				$query = str_replace("　"," ",$this->getVariable("q"));
				$querys = array();
				$models = $tableObject->models($confmethod,"search_fields",true);

				foreach(explode(" ",$query) as $word){
					$bool = false;
					if(strpos($word,"=") !== false){
						$not = false;
						list($name,$q) = explode("=",$word);
						if(substr($name,-1) == "!"){
							$not = true;
							$name = substr($name,0,-1);
						}
						foreach($models as $column){
							if($column->equals($name)){
								$bool = true;

								if($q === ""){
									$criteria->q(
									($not) ? Q::andc(Q::neq($column,null),Q::neq($column,"")) :
									Q::andc(Q::eq($column,null),Q::orc(Q::eq($column,"")))
									);
								}else{
									$criteria->q(Q::andc(Q::contains($column,$q,$not)));
								}
							}
						}
					}
					if(!$bool) $criteria->q(Q::andc(Q::contains($models,$word)));
				}
				$criteria->addCriteria($qcriteria);
			}else{
				$criteria->q(Q::icontains($tableObject->models($confmethod,"search_fields",true),$this->getVariable("q")));
			}
		}
		if(method_exists($tableObject,$confmethod)){
			$list = ArrayUtil::arrays($tableObject->$confmethod());

			if(array_key_exists("ordering",$list)){
				foreach(ArrayUtil::arrays($list["ordering"]) as $value){
					foreach(explode(",",$value) as $name){
						$order = new SortOrder(null,$name);
						$criteria->q($order->q($tableObject));
					}
				}
			}
		}
		$this->setVariable("sortorder",$sortorder);
	}

	/**
	 * CSVエクスポート
	 *
	 * @param database.model.TableObject $tableObject
	 * @param database.model.Criteria $criteria
	 * @param string $confmethod
	 * @return HtmlParser tag.HtmlParser
	 */
	function export($tableObject,$criteria=null,$confmethod="views") {
		if($this->_connection($tableObject)){
			if(!Variable::istype("Criteria",$criteria)) $criteria = new Criteria();
			$this->_readCriteria($tableObject,$criteria,$confmethod);
			$criteria->q(Criterion::fact());

			Logger::disableDisplay();
			$result = $this->dbUtil->export($tableObject,$criteria);
			Header::attach($result,get_class($tableObject).".csv");
			Rhaco::end();
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_title",Message::_("export"));
		return $this->parser();
	}


	/**
	 * 詳細を表示する
	 *
	 * @param database.model.TableObject $tableObject
	 * @param database.model.Criteria $criteria
	 * @return HtmlParser tag.HtmlParser
	 */
	function detail($tableObject,$criteria=null){
		/*** unit("generic.ViewsTest"); */
		$object = null;
		$count = 0;

		if($this->_connection($tableObject)){
			$object	= $this->dbUtil->get($tableObject,$this->_primaryCriteria($tableObject,$criteria));
		}
		if(!Variable::istype("TableObjectBase",$object)){
			$this->_notFound();
		}else{
			$this->setVariable("object",$object);
			$this->_setParser($object,sprintf("generic/%s_detail.html",strtolower(get_class($object))),"detail.html");
		}
		$this->setVariable("generic_title",Message::_("detail"));
		return $this->parser();
	}

	/**
	 * 作成する
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $filterargs
	 * @param unknown_type $filters
	 * @return unknown
	 */
	function create($tableObject,$filterargs=array(),$filters=array()){
		$this->_addFitler($filters);
		if($this->_connection($tableObject)){
			$object = null;
			if($this->isPost()){
				$object = $this->dbUtil->insert($this->toObject($tableObject));
				if(Variable::istype("TableObjectBase",$object)){
					ObjectUtil::calls($this->filters,"afterCreate",array_merge(array($object),ArrayUtil::arrays($filterargs)));
				}
			}
			$this->setVariable(ObjectUtil::objectConvHash($object));
			$this->setVariable("object",$tableObject);
			$this->_setParser($tableObject,sprintf("generic/%s_form.html",strtolower(get_class($tableObject))),"form.html");
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_button",Message::_("create"));
		$this->setVariable("generic_title",Message::_("create"));
		return $this->parser();
	}

	/**
	 * 確認画面ありで作成する
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $filterargs
	 * @param unknown_type $filters
	 * @return unknown
	 */
	function confirmedCreate($tableObject,$filterargs=array(),$filters=array()){
		$title = Message::_("create");
		$this->_addFitler($filters);

		if($this->_connection($tableObject)){
			$template = "form.html";
			$object = null;

			if($this->isPost()){
				if($this->isState()){
					if($this->isVariable("_back")){
						$tableObject = $this->toObject($tableObject);
					}else{
						$object = $this->dbUtil->insert($this->toObject($tableObject));
						if(Variable::istype("TableObjectBase",$object)){
							ObjectUtil::calls($this->filters,"afterCreate",array_merge(array($object),ArrayUtil::arrays($filterargs)));
						}
					}
				}else{
					$this->setEnv();
					$tableObject = $this->toObject($tableObject);
					if(TableObjectVerify::verify($this->dbUtil,$tableObject)){
						$template = "confirm.html";
						$title = Message::_("confirm");
						$this->saveState();
					}
				}
			}
			$this->setVariable(ObjectUtil::objectConvHash($object));
			$this->setVariable("object",$tableObject);
			$this->_setParser($tableObject,sprintf("generic/%s_".$template,strtolower(get_class($tableObject))),$template);
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_button",Message::_("create"));
		$this->setVariable("generic_title",$title);
		return $this->parser();
	}

	/**
	 * 更新する
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $criteria
	 * @param unknown_type $filterargs
	 * @param unknown_type $filters
	 * @return unknown
	 */
	function update($tableObject,$criteria=null,$filterargs=array(),$filters=array()){
		$this->_addFitler($filters);
		if($this->_connection($tableObject)){
			$tableObject = $this->dbUtil->get($tableObject,$this->_primaryCriteria($tableObject,$criteria));
			if(!Variable::istype("TableObjectBase",$tableObject)){
				$this->_notFound();
			}else{
				if($this->isPost()){
					$tableObject = $this->toObject($tableObject);
					if(Variable::istype("TableObjectBase",$tableObject) && $this->dbUtil->update($tableObject)){
						ObjectUtil::calls($this->filters,"afterUpdate",array_merge(array($tableObject),ArrayUtil::arrays($filterargs)));
					}
				}
				$this->setVariable(ObjectUtil::objectConvHash($tableObject));
				$this->setVariable("object",$tableObject);
				$this->_setParser($tableObject,sprintf("generic/%s_form.html", strtolower(get_class($tableObject))),"form.html");
			}
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_button",Message::_("update"));
		$this->setVariable("generic_title",Message::_("update"));
		return $this->parser();
	}

	/**
	 * 確認画面ありで更新する
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $criteria
	 * @param unknown_type $filterargs
	 * @param unknown_type $filters
	 * @return unknown
	 */
	function confirmedUpdate($tableObject,$criteria=null,$filterargs=array(),$filters=array()){
		$title = Message::_("update");
		$this->_addFitler($filters);
		if($this->_connection($tableObject)){
			$template = "form.html";
			$tableObject = $this->dbUtil->get($tableObject,$this->_primaryCriteria($tableObject,$criteria));
			if(!Variable::istype("TableObjectBase",$tableObject)){
				$this->_notFound();
			}else{
				if($this->isPost()){
					if($this->isState()){
						if($this->isVariable("_back")){
							$tableObject = $this->toObject($tableObject);
						}else{
							if(Variable::istype("TableObjectBase",$tableObject) && $this->dbUtil->update($this->toObject($tableObject))){
								ObjectUtil::calls($this->filters,"afterUpdate",array_merge(array($tableObject),ArrayUtil::arrays($filterargs)));
							}
						}
					}else{
						$this->setEnv();
						$tableObject = $this->toObject($tableObject);
						if(TableObjectVerify::verify($this->dbUtil,$tableObject)){
							$template = "confirm.html";
							$title = Message::_("confirm");
							$this->saveState();
						}
					}
				}
				$this->setVariable(ObjectUtil::objectConvHash($tableObject));
				$this->setVariable("object",$tableObject);
				$this->_setParser($tableObject,sprintf("generic/%s_".$template, strtolower(get_class($tableObject))),$template);
			}
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_button",Message::_("update"));
		$this->setVariable("generic_title",$title);
		return $this->parser();
	}

	/**
	 * 削除する
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $criteria
	 * @param unknown_type $filterargs
	 * @param unknown_type $filters
	 * @return unknown
	 */
	function drop($tableObject,$criteria=null,$filterargs=array(),$filters=array()){
		$this->_addFitler($filters);
		if($this->_connection($tableObject)){
			$tableObject = $this->dbUtil->get($tableObject,$this->_primaryCriteria($tableObject,$criteria));
			if(!Variable::istype("TableObjectBase",$tableObject)){
				$this->_notFound();
			}else{
				if($this->isPost()){
					$object = $this->toObject($tableObject,$this->_primaryCriteria($tableObject,$criteria));
					if($this->dbUtil->delete($object)){
						ObjectUtil::calls($this->filters,"afterDrop",array_merge(array($object),ArrayUtil::arrays($filterargs)));
					}
				}
				$this->setVariable(ObjectUtil::objectConvHash($tableObject));
				$this->setVariable("object",$tableObject);
				$this->_setParser($tableObject,sprintf("generic/%s_confirm_delete.html", strtolower(get_class($tableObject))),"confirm.html");
			}
		}else{
			$this->_notFound();
		}
		$this->setVariable("generic_button",Message::_("drop"));
		$this->setVariable("generic_title",Message::_("drop"));
		return $this->parser();
	}
	function _primaryCriteria($tableObject,$criteria){
		$criteria = (Variable::istype("Criteria",$criteria)) ? $criteria : new Criteria();
		if(!empty($this->params)){
			foreach($tableObject->primaryKey() as $key => $column){
				if(!isset($this->params[$key])) break;
				TableObjectUtil::setter($tableObject,$column,$this->params[$key]);
			}
		}
		foreach($tableObject->primaryKey() as $column){
			$value = TableObjectUtil::getter($tableObject,$column);
			if(!empty($value)){
				$criteria->q(Criterion::equal($column,$value));
			}
		}
		return $criteria;
	}
	function _setParser(&$tableObject,$template,$defaulTemplate){
		if(!FileUtil::exist(Rhaco::templatepath($template))) $template = Rhaco::rhacoresource("templates/generic/views/".$defaulTemplate);
		$this->setVariable("viewutil",new ViewsUtil($this->dbUtil));
		$this->setVariable("tableObject",$tableObject);
		$this->setTemplate($template);
	}
	function _notFound(){
		$template = Rhaco::templatepath("generic/404.html");
		if(!FileUtil::exist($template)) $template = Rhaco::rhacoresource("templates/generic/404.html");
		$this->setTemplate($template);
		Http::status(404);
	}
}
?>