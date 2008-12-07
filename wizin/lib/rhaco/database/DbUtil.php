<?php
Rhaco::import("resources.Message");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("util.Logger");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotSupportedException");
Rhaco::import("exception.model.ClassTypeException");
Rhaco::import("exception.model.IllegalStateException");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("exception.model.NotConnectionException");
Rhaco::import("exception.model.DuplicateException");
Rhaco::import("exception.model.MaxLengthException");
Rhaco::import("exception.model.RequireException");
Rhaco::import("exception.model.GenericException");
Rhaco::import("database.model.DbConnection");
Rhaco::import("database.TableObjectVerify");
Rhaco::import("abbr.C");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("database.model.Criteria");
Rhaco::import("database.model.Criterion");
Rhaco::import("database.TableObjectUtil");
/**
 * database操作クラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbUtil{
	var $connection		= false;
	var $resultset		= array();
	var $base			= null;
	var $selectObjects	= array();
	var $selectFact		= false;
	var $paginator		= null;
	
	/**
	 * DB接続を開始する
	 *
	 * @param DbConnection $dbConnection
	 * @return DbUtil
	 */
	function DbUtil($dbConnection=null){
		if(is_string($dbConnection)) $dbConnection = new DbConnection($dbConnection);
		if(Variable::istype("DbConnection",$dbConnection)){
			$this->base = $dbConnection->base();
			if($this->base == null) ExceptionTrigger::raise(new NotSupportedException(Message::_("database controler [{1}]",$dbConnection->type)));
			$this->_open($dbConnection);
		}
	}
	function _open($dbConnection){
		if(!$this->connection) $this->close();
		if($this->base !== null) $this->connection = $this->base->open($dbConnection);
		if($this->connection) return Rhaco::register_shutdown(array($this,'close'));
		return ExceptionTrigger::raise(new NotConnectionException(Message::_("database")));			
	}
	
	/**
	 * paginatorを取得
	 *
	 * @return unknown
	 */
	function getPaginator(){
		return $this->paginator;
	}
	
	/**
	 * コネクションをクローズする
	 *
	 */
	function close(){
		if($this->connection){
			$this->base->trans(false);
			$this->base->close();
			$this->connection = false;
		}
	}
	
	/**
	 * 最後に実行したSQL
	 *
	 * @param unknown_type $block
	 * @return unknown
	 */
	function sql($block=false){
		return $this->base->sql($block);
	}
	
	/**
	 * 最後に発生したエラー
	 *
	 * @return unknown
	 */
	function error(){
		return $this->base->error();
	}
	
	/**
	 * トランザクションをOn/Off
	 */
	function trans($bool){
		if($this->connection) $this->base->trans($bool);		
	}
	/**
	 * トランザクションをコミットする
	 *
	 */
	function commit(){
		/*** unit("database.DbUtilTest"); */
		if($this->connection) $this->base->commit();
	}
	
	/**
	 * トランザクションをロールバックする
	 */
	function rollback(){
		/*** unit("database.DbUtilTest"); */
		if($this->connection) $this->base->rollback();
	}
	
	/**
	 * queryを発行する
	 * @return boolean 
	 */
	function query($sql){
		return ($this->connection) ? $this->base->query($sql) : false;
	}
	
	/**
	 * csvとしてexport
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $criteria
	 * @return unknown
	 */
	function export($tableObject,$criteria=null){
		if(Variable::istype("TableObjectBase",$tableObject)){
			$objects = $this->select($tableObject,$criteria);			
			$result = "";
			foreach($tableObject->columns() as $column){
				$result .= $column->variable().",";
			}
			$result = substr($result,0,-1)."\n";
			foreach($objects as $object){
				$line = "";
				foreach($object->columns() as $column){
					$value = TableObjectUtil::getter($object,$column);
					$line .= ("\"".str_replace("\"","\"\"",$value)."\"").",";
				}
				$result .= substr($line,0,-1)."\n";
			}
			return $result;
		}
	}	
	/**
	 * CSVデータをimportする
	 *
	 * @param unknown_type $tableObject
	 * @param unknown_type $src
	 * @param unknown_type $commit
	 * @return unknown
	 */	
	function import($tableObject,$src,$commit=true){
		if(Variable::istype("TableObjectBase",$tableObject) && !empty($src)){
			list($header,$body) = explode("\n",trim(StringUtil::toULD($src)),2);
			$body = preg_replace("/\".+?\"/se",
							'str_replace(array(",","\n"),array("RHACO__COMMA","RHACO__ENTER"),"\\0")',
							str_replace(array("\"\"\"","\"\"","\"\"","\\","\$"),array("\"RHACO__DOUBLE","RHACO__DOUBLE","RHACO__ESCAPE","RHACO__DOLLAR"),$body)
					);
			$columns = array();
			$headers = array();

			foreach($tableObject->columns() as $column){
				$columns[strtolower($column->sqlname())] = $column;
				$columns[strtolower($column->variable())] = $column;
			}
			foreach(explode(",",trim($header)) as $key => $h){
				$h = preg_replace('/^"(.+)"$/s',"\\1",strtolower($h));
				$headers[$key] = (isset($columns[$h])) ? $columns[$h] : null;
			}
			foreach(explode("\n",$body) as $line){
				$obj = Variable::copy($tableObject);
				$bool = false;
				foreach(explode(",",trim($line)) as $key => $value){
					if(isset($headers[$key])){
						if($value == "RHACO__DOUBLE") $value = null;
						$value = str_replace(array("RHACO__COMMA","RHACO__ENTER","RHACO__DOUBLE","RHACO__ESCAPE","RHACO__DOLLAR"),
								array(",","\n","\"","\\","\$"),preg_replace('/^"(.+)"$/',"\\1",$value));
						TableObjectUtil::setter($obj,$headers[$key],$value);
						$bool = true;
					}
				}
				if($bool && false === $obj->save($this)){
					$this->rollback();
					return ExceptionTrigger::raise(new GenericException(Message::_("Invalid Data {1}",get_class($tableObject))));
				}
			}
			return true;
		}
	}
	
	function exportXml($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if(Variable::istype("TableObjectBase",$tableObject)){
			$objects = $this->select($tableObject,$criteria);
			$results = array();
			$root = new SimpleTag("default",null,array("class"=>get_class($tableObject)));
			
			$name = get_class($tableObject);
			foreach($objects as $object){
				$data = new SimpleTag("data");

				foreach($object->columns() as $column){
					$data->addValue(new SimpleTag("column",TableObjectUtil::getter($object,$column),array("var"=>$column->variable())));
				}
				$root->addValue($data->get()."\n");
			}
			return $root->get();
		}
		return "";
	}
	function importXml($tableObject,$src,$commit=true){
		/*** unit("database.DbUtilTest"); */
		if(Variable::istype("TableObjectBase",$tableObject) && !empty($src) && SimpleTag::setof($tag,"<import>".$src."</import>","import")){
			$classname = get_class($tableObject);
			$classname_table = $tableObject->table();

			foreach($tag->getIn("default") as $default){
				$defaultclassname = $default->param("class",$default->param("name"));

				if(Variable::iequal(StringUtil::regularizedName($defaultclassname),$classname)){
					foreach($default->getIn("data") as $dataTag){
						$obj = Variable::copy($tableObject);

						foreach($dataTag->getIn("column") as $columnTag){
							$xmlname = $columnTag->getParameter("var",$columnTag->getParameter("name"));
							$value = $columnTag->param("value",$columnTag->getValue());

							if(Variable::bool($columnTag->getParameter("trim",false)) && preg_match("/^([\s]+)(.+)[\s]*$/m",$value,$match)){
								$tab = preg_replace("/^[\n]+/","",$match[1]);
								$bool = true;

								foreach(ArrayUtil::arrays(explode("\n",$match[2]),1) as $key => $line){
									if(!empty($line) && strpos($line,$tab) !== 0){
										$bool = false;
										break;
									}
								}
								if($bool){
									$value = trim(preg_replace("/^".$tab."/m","",$value));
								}
							}

							$bool = false;
							foreach($tableObject->columns() as $column){
								if($column->equals($xmlname)){
									TableObjectUtil::setter($obj,$column,$value);
									$bool = true;
									break;
								}
							}
							if(!$bool){
								foreach($tableObject->extra() as $column){
									if(Variable::iequal($column->variable(),$xmlname) || Variable::iequal($column->sqlname(),$xmlname)){
										TableObjectUtil::setter($obj,$column,$value);
										break;
									}
								}
							}
						}
						if(false === $obj->save($this)){
							ExceptionTrigger::raise(new GenericException(Message::_("Invalid Data({1}){<xmp>\n{2}\n</xmp>}",$classname,$dataTag->get())));
							$this->rollback();
							return false;
						}
					}
				}
			}
			if(method_exists($this->base,"updateSerialMax")) call_user_func_array(array(&$this->base,"updateSerialMax"),array($tableObject));
			if($commit) $this->commit();
		}
		return true;
	}

	/**
	 * SELECTを発行しObjectリストを取得する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return array TableObjectBase
	 */
	function select($tableObjectList,$criterias=null){
		/*** unit("database.QTest"); */
		$objectList	= array();
		$dependbool = false;
		
		if($this->connection){
			if($this->executeSelect($tableObjectList,$criterias)){
				$criterias = ArrayUtil::arrays($criterias);
				$flatbool = (!empty($criterias) && Variable::istype("Criteria",$criterias[0]) && $criterias[0]->fact && $criterias[0]->flat);
				$dependbool = (!empty($criterias) && Variable::istype("Criteria",$criterias[0]) && $criterias[0]->depend);				

				while($this->nextObject($objects)){
					if(sizeof($objects) > 1){
						foreach(ArrayUtil::arrays($objects) as $key => $obj){
							$objectList[$key][] = $this->_factToFlat(Variable::copy($obj),$flatbool);
						}
					}else{
						$objectList[] = $this->_factToFlat(Variable::copy($objects),$flatbool);
					}
				}
			}
		}
		return $this->_afterSelect($tableObjectList,$this->_dependSet($objectList,$dependbool),$criterias);
	}
	function _afterSelect($tableObjectList,$objectList,$criterias){
		$isafter = false;
		$after = array();

		foreach(ArrayUtil::arrays($tableObjectList) as $key => $table){
			$after[$key] = ObjectUtil::isMethod($table,"afterSelect");
			if($after[$key]) $isafter = true;
		}
		if($isafter){
			$paginator = $this->paginator;
			foreach($objectList as $key => $obj){
				if(is_array($objectList[$key])){
					if($after[$key]){
						foreach($objectList[$key] as $ckey => $obj){
							$objectList[$key][$ckey]->afterSelect($this,$criterias);
						}
					}
				}else if($after[0]){
					$objectList[$key]->afterSelect($this,$criterias);
				}
			}
			$this->paginator = $paginator;
		}
		return $objectList;
	}
	function _factToFlat($tableObject,$bool){
		if($bool){
			$baseObject = Variable::copy($tableObject);
			$fact = false;
			foreach($tableObject->columns() as $column){
				if($column->isReference()){
					$fact = true;
					$factobj = $tableObject->{"fact".ucwords($column->variable())};
					if(is_object($factobj)) $tableObject = ObjectUtil::mixin($tableObject,$factobj);
				}
			}
			if($fact) $tableObject = ObjectUtil::mixin($tableObject,$baseObject);
		}
		return $tableObject;
	}
	function _dependSet($tableObjects,$bool){
		if($bool && !empty($tableObjects)){
			$depends = array();
			foreach($tableObjects[0]->columns() as $column){
				if($column->isDepend()){
					foreach($column->depend() as $dcolumn){
						$depends[] = array($column,$dcolumn);
					}
				}
			}
			foreach($depends as $depend){
				$ids = array();
				foreach($tableObjects as $obj){
					$ids[] = TableObjectUtil::getter($obj,$depend[0]);
				}
				$list = $this->select($depend[1]->tableObject(),new Criteria(Criterion::in($depend[1],$ids)));
				$setter = "setDepend".get_class($depend[1]->tableObject())."s";
				
				$sets = array();
				foreach($list as $obj){
					$sets[TableObjectUtil::getter($obj,$depend[1])][] = $obj;
				}
				foreach($tableObjects as $key => $obj){
					$id = TableObjectUtil::getter($obj,$depend[0]);
					if(array_key_exists($id,$sets) && method_exists($obj,$setter)){
						$tableObjects[$key]->$setter($sets[$id]);
					}
				}
			}
		}
		return $tableObjects;
	}
	function _argCheck($object,$typename,$argnum="first"){
		return Variable::istype($typename,$object) ? true : ExceptionTrigger::raise(new ClassTypeException(Message::_($argnum." argument"),$typename,get_class($object)));
	}

	/**
	 * SELECTを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return boolean
	 */
	function executeSelect($tableObjectList,$criterias=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			$criterias = ArrayUtil::arrays(empty($criterias) ? new Criteria() : $criterias);
			$tableObjectList = ArrayUtil::arrays($tableObjectList);
			$this->paginator = null;

			foreach($tableObjectList as $tableObject){
				if(!$this->_argCheck($tableObject,"TableObjectBase","first")) return array();
			}
			foreach($criterias as $criteria){
				if(!$this->_argCheck($criteria,"Criteria","second")) return array();
			}
			$this->selectObjects = $tableObjectList;
			$this->selectFact = $criterias[0]->fact;

			if($this->selectFact){
				$eqs = array();

				foreach($this->selectObjects as $object){
					foreach($object->columns() as $column){
						if($column->isReference()){
							$referencecolumn = $column->reference();
							$tableObjectList[] = $referencecolumn->tableObject();
							$eqs[] = Criterion::equal($column,$referencecolumn);
						}
					}
				}
				foreach($eqs as $eq){
					$criterias[0]->q($eq);
				}
				foreach($criterias[0]->criteriaList as $key => $criteriaPattern){
					foreach($eqs as $eq){
						$criterias[0]->criteriaList[$key]->argA->q($eq);
					}
				}
			}
			if($criterias[0]->isPaginator()){
				$this->paginator = Variable::copy($criterias[0]->paginator);
				$this->paginator->setTotal($this->count($tableObjectList[0],$criterias[0]));
				$criterias[0]->q(Criterion::pager($this->paginator));
			}
			return $this->base->select($tableObjectList,$criterias);
		}
		return false;
	}
	
	/**
	 * COUNTを発行する
	 *
	 * @param database.TableObjectBase $tableObject
	 * @param database.model.Criteria $criteria
	 * @return int 
	 */
	function count($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			if(!$this->_argCheck($tableObject,"TableObjectBase","first")) return 0;
			if($criteria !== null && !$this->_argCheck($criteria,"Criteria","second")) return 0;
			if(empty($criteria)) $criteria = new Criteria();

			if($criteria->fact){
				foreach($tableObject->columns() as $column){
					if($column->isReference()) $criteria->q(Criterion::equal($column,$column->reference()));
				}
			}
			$result = $this->base->count($tableObject,$criteria);
			return $result;
		}
		return 0;
	}
	
	/**
	 * DbUtil::countのエイリアス
	 *
	 * @param database.TableObjectBase $tableObject
	 * @param database.model.Criteria $criteria
	 * @return int 
	 * 
	 */
	function sizeof($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */		
		return $this->count($tableObject,$criteria);
	}

	/**
	 * SUMを発行する
	 */
	function sum($tableObject,$column,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			if(!$this->_argCheck($tableObject,"TableObjectBase","first")) return 0;
			if(!$this->_argCheck($column,"Column","second")) return false;
			if($criteria !== null && !$this->_argCheck($criteria,"Criteria","third")) return 0;
			if(empty($criteria)) $criteria = new Criteria();
			
			if($criteria->fact){
				foreach($tableObject->columns() as $column){
					if($column->isReference()) $criteria->q(Criterion::equal($column,$column->reference()));
				}
			}			
			return $this->base->sum($tableObject,$column,$criteria);
		}
		return 0;
	}
	
	/**
	 * SELECTを発行し１レコード取得する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return TableObjectBase
	 */
	function get($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			if(!$this->_argCheck($tableObject,"TableObjectBase","first")) return null;
			if($criteria !== null && !$this->_argCheck($criteria,"Criteria","second")) return null;
			$criteria = $this->_defaultCriteria($tableObject,$criteria);
			if(!$criteria->fact) $criteria->q(Q::pager(1));

			$list	= $this->select($tableObject,$criteria);
			$object	= (empty($list)) ? null : $list[0];
			$object	= (!Variable::istype("TableObjectBase",$object) && $criteria->getNoneNew) ? $this->insert($tableObject) : $object;

			if(Variable::istype("TableObjectBase",$object)){
				if(ObjectUtil::isMethod($object,"afterGet")) $object->afterGet($this,$criteria);
				return $object;
			}
		}
		return null;
	}
	/**
	 * INSERTを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return TableObjectBase / false
	 */	
	function insert($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			if(Variable::istype("TableObjectBase",$tableObject)){
				if(!ObjectUtil::isMethod($tableObject,"beforeInsert") || false !== $tableObject->beforeInsert($this,$criteria)){
					if(!TableObjectVerify::verify($this,$tableObject)) return false;
					if(!Variable::istype("Criteria",$criteria)) $criteria = new Criteria();
					if(!$this->_verifyFk($tableObject) || !$this->_verifyUnique($tableObject)) return false;

					$obj = $this->base->insert($tableObject,$criteria);
					if(Variable::istype("TableObjectBase",$obj)){
						if(ObjectUtil::isMethod($obj,"afterInsert")) $obj->afterInsert($this,$criteria);
						return $obj;
					}
				}
			}
		}
		return false;
	}

	/**
	 * DELETEを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return boolean
	 */
	function delete($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			if(Variable::istype("TableObjectBase",$tableObject)){
				$criteria = $this->_defaultCriteria($tableObject,$criteria);
				$boolean = true;
				
				if(false !== $tableObject->beforeDelete($this,$criteria)){
					foreach($tableObject->columns() as $column){
						foreach($column->depend() as $dependcolumn){
							if(0 < $this->count($dependcolumn->tableObject(),
								new Criteria(Criterion::equal($dependcolumn,TableObjectUtil::getter($tableObject,$column))))){
								$boolean = ExceptionTrigger::raise(new IllegalArgumentException($column->label()));
							}
						}
					}					
					if($boolean && $this->base->delete($tableObject,$criteria)){
						return (ObjectUtil::isMethod($tableObject,"afterDelete")) ? $tableObject->afterDelete($this,$criteria) : true;
					}
				}
			}
		}
		return false;
	}
	
	/**
	 * UPDATEを発行する
	 * Criteriaを指定する事で複数レコードの更新を行える
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return boolean
	 */
	function update($tableObject,$criteria=null){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			if(!$this->_argCheck($tableObject,"TableObjectBase","first")) return false;
			if($criteria !== null && !$this->_argCheck($criteria,"Criteria","second")) return false;
			$criteria = $this->_defaultCriteria($tableObject,$criteria);

			if(!ObjectUtil::isMethod($tableObject,"beforeUpdate") || false !== $tableObject->beforeUpdate($this,$criteria)){
				if(!TableObjectVerify::verify($this,$tableObject) || !$this->_verifyFk($tableObject) || !$this->_verifyUnique($tableObject,true)) return false;
				if($this->base->update($tableObject,$criteria)){
					if(ObjectUtil::isMethod($tableObject,"afterUpdate")) return $tableObject->afterUpdate($this,$criteria);
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * DELETEを発行し指定のテーブルを空にする
	 *
	 * @param database.TableObjectBase $tableObjectList
	 * @param database.model.Criteria $criteria
	 * @return array TableObjectBase
	 */
	function alldelete($tableObject){
		/*** unit("database.DbUtilTest"); */
		if($this->connection && Variable::istype("TableObjectBase",$tableObject) && $this->base->delete($tableObject,array())) return true;
		return false;
	}
	function droptable($tableObject){
		/*** unit("database.DbUtilTest"); */
		if($this->connection && Variable::istype("TableObjectBase",$tableObject) && $this->base->droptable($tableObject)) return true;
		return false;
	}
	
	/**
	 * analyzeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function analyze($tableObjectList){
		$this->base->analyze($tableObjectList);
	}
	
	/**
	 * optimizeを発行する
	 *
	 * @param database.TableObjectBase $tableObjectList
	 */
	function optimize($tableObjectList){
		$this->base->optimize($tableObjectList);
	}
	
	/**
	 * 次の結果セットをセットする
	 * 次がなければfalse
	 *
	 * @return boolean
	 */
	function next(){
		/*** unit("database.DbUtilTest"); */
		if($this->connection){
			$this->resultset = array();
			$resultset = $this->base->resultset();

			if(!$resultset){
				$this->_free();
				return false;
			}
			$this->resultset = $this->_parseResultset($resultset);
			return true;
		}
		return false;
	}
	function _parseResultset($resultset){
		$list = array();
		foreach($resultset as $key => $value){
			$list[strtoupper($key)] = StringUtil::getMagicQuotesOffValue($value);
		}
		return $list;
	}
	function _free(){
		$this->base->free();
	}
	
	/**
	 * 結果セットを取得する
	 *
	 * @return resultset
	 */
	function getResultset(){
		return $this->resultset;
	}
	
	/**
	 * 次の結果セットを該当のObjectにセットする
	 * 次がなければfalse
	 *
	 * @param unknown_type $obj
	 * @return boolean
	 */
	function nextObject(&$obj){
		/*** unit("database.DbUtilTest"); */
		if($this->next()){
			$objectList = array();
			
			$resultset = array_change_key_case($this->getResultset());
			foreach($this->selectObjects as $number => $tableObject){
				$newobject = Variable::copy($tableObject);
				$this->_setObject($newobject,$resultset);

				if($this->selectFact){
					foreach($newobject->columns() as $column){
						if($column->isReference()){
							$referencecolumn = $column->reference();
							$referenceobj = $referencecolumn->tableObject();
							$this->_setObject($referenceobj,$resultset);
							ObjectUtil::setter($newobject,"fact".$column->variable(),$referenceobj);
						}
					}
					unset($referencecolumn,$referenceobj);
				}
				$objectList[] = $newobject;
			}
			if(empty($objectList)){
				for($i=0;$i<sizeof($this->selectObjects);$i++) $objectList[] = array();
			}
			$obj = (sizeof($objectList) == 1) ? array_shift($objectList) : $objectList;
			unset($objectList,$resultset);
			return true;
		}
		return false;
	}
	function _setObject(&$object,$resultset){
		foreach($object->columns() as $column){
			$xx = $column->getXXColumn();
			if(array_key_exists($xx,$resultset)){
				TableObjectUtil::setter($object,$column,$resultset[$xx]);
			}
		}
	}
	function _defaultCriteria(&$tableObject,$criteria){
		$criteria = (Variable::istype("Criteria",$criteria)) ? $criteria : new Criteria();
		if(!$criteria->isCond()) return $criteria;

		if(sizeof($tableObject->primaryKey()) > 0){
			foreach($tableObject->primaryKey() as $column){
				$value = TableObjectUtil::getter($tableObject,$column);
				if(empty($value)) ExceptionTrigger::raise(new IllegalArgumentException($column->variable()));
				$criteria->q(Criterion::equal($column,$value));
			}
		}else{
			foreach($tableObject->columns() as $column){
				$criteria->q(Criterion::equal($column,TableObjectUtil::getter($tableObject,$column)));
			}
		}
		return $criteria;
	}
	function _verifyFk(&$tableObject){
		$boolean = true;
		foreach($tableObject->columns() as $column){
			if($column->isReference()){
				$referencecolumn = $column->reference();
				$referenceobject = $referencecolumn->tableObject();
				$value = TableObjectUtil::getter($tableObject,$column);

				if(!is_null($value) && $value === "NULL" && 0 >= $this->count($referenceobject,new Criteria($referencecolumn,$value))){
					$boolean = ExceptionTrigger::raise(new IllegalArgumentException(Message::_($name[1])));
				}
			}
		}
		return $boolean;
	}
	function _verifyUnique(&$tableObject,$update=false){
		/*** unit("database.DbUtilTest"); */
		$boolean = true;
		foreach($tableObject->columns() as $column){
			if($column->unique()){
				$criteria = new Criteria();
				$value = TableObjectUtil::getter($tableObject,$column);
				$criteria->q(((is_null($value) || $value === "NULL") ? Criterion::notEqual($column,"NULL") : Criterion::equal($column,$value)));
				if($column->isUniqueWith()){
					$withcolumn = $column->uniqueWith();
					$value = TableObjectUtil::getter($tableObject,$withcolumn);
					$criteria->q(((is_null($value) || $value === "NULL") ? Criterion::notEqual($withcolumn,"NULL") : Criterion::equal($withcolumn,$value)));
				}
				if($update){
					foreach($tableObject->primaryKey() as $pcolumn) $criteria->q(Criterion::notEqual($pcolumn,TableObjectUtil::getter($tableObject,$pcolumn)));
				}
				if(0 < $this->count($tableObject,$criteria)){
					$boolean = ExceptionTrigger::raise(new DuplicateException($column->label()));
				}
			}
		}
		return $boolean;
	}
}
?>