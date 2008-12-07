<?php
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
Rhaco::import("lang.Env");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("tag.model.SimpleTag");
/**
 * オブジェクトを扱うユーティリティ
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ObjectUtil{
	function setaccessor($object,$castfunc=null){
		$classname = get_class($object);

		if(!Rhaco::isVariable("RHACO_CORE_OBJECT_ACCESSOR",$classname)){
			$results = array();
			$names = array();			
			$methods = get_class_methods($object);
			$vars = get_object_vars($object);			
			foreach(ArrayUtil::arrays($methods) as $key => $method){
				if(preg_match("/^(get|set|format)([A-Za-z].+)$/",$method,$match)){
					$names[strtolower($match[2])] = $match[2];
				}else{
					unset($methods[$key]);
				}
			}
			$vars = ArrayUtil::arrays($vars); 
			foreach($vars as $var => $value){
				$names[strtolower($var)] = $var;
			}
			$methods = ArrayUtil::lowerflip($methods);
			array_walk($vars,create_function('&$v,$k','$v = $k;'));

			foreach($names as $name => $var){
				$results[$name] = array(
										(array_key_exists($var,$vars) ? $vars[$var] : null),
										(isset($methods["set".$name]) ? "set".$name : null),
										(isset($methods["get".$name]) ? "get".$name : null),
										(isset($methods["format".$name]) ? "format".$name : null),
										$castfunc,
									);
			}
			if(!empty($results)) Rhaco::addVariable("RHACO_CORE_OBJECT_ACCESSOR",$results,$classname);
			return Rhaco::isVariable("RHACO_CORE_OBJECT_ACCESSOR",$classname);
		}
		return true;
	}
	
	/**
	 * getter
	 *
	 * @param database.model.TableObject $tableObject
	 * @param string $column
	 * @param string $classname
	 * @return unknown
	 */
	function getter(&$object,$varname,$formatter=false,$args=null){
		if(ObjectUtil::setaccessor($object)){
			$varname = strtolower($varname);
			$accessor = Rhaco::getVariable("RHACO_CORE_OBJECT_ACCESSOR",null,get_class($object));
	
			if(isset($accessor[$varname])){
				if($formatter){
					if($accessor[$varname][3] != null) return $object->{$accessor[$varname][3]}();
				}
				if($accessor[$varname][2] !== null) return $object->{$accessor[$varname][2]}();
				if($accessor[$varname][0] !== null){
					$value = $object->{$accessor[$varname][0]};
					if($accessor[$varname][4] !== null){
						$args = ArrayUtil::arrays($args);
						array_unshift($args,$value);
						$value = call_user_func_array($accessor[$varname][4],$args);
					}
					return $value;
				}
			}
		}
		ExceptionTrigger::raise(new NotFoundException($varname));
		return null;
	}
	
	/**
	 * setter
	 *
	 * @param unknown_type $object
	 * @param unknown_type $varname
	 * @param unknown_type $value
	 * @param unknown_type $args
	 * @return unknown
	 */
	function setter(&$object,$varname,$value,$args=null){
		if(ObjectUtil::setaccessor($object)){
			$varname = strtolower($varname);
			$accessor = Rhaco::getVariable("RHACO_CORE_OBJECT_ACCESSOR",null,get_class($object));

			if(isset($accessor[$varname])){
				if($accessor[$varname][1] != null){
					return $object->{$accessor[$varname][1]}($value);
				}else if($accessor[$varname][0] != null){
					if($accessor[$varname][4] !== null){
						$args = ArrayUtil::arrays($args);
						array_unshift($args,$value);
						$value = call_user_func_array($accessor[$varname][4],$args);
					}
					$object->{$accessor[$varname][0]} = $value;
					return $value;
				}
			}
		}
		ExceptionTrigger::raise(new NotFoundException($varname));
		return null;
	}
	
	/**
	 * アクセサが存在するか
	 *
	 * @param Object $object
	 * @param string $varname
	 */
	function isAccessor($object,$varname){
		if(ObjectUtil::setaccessor($object)){
			$varname = strtolower($varname);
			$accessor = Rhaco::getVariable("RHACO_CORE_OBJECT_ACCESSOR",null,get_class($object));
			return isset($accessor[$varname]);
		}		
	}

	
	
	/**
	 * 匿名クラスを生成する
	 *
	 * @return object
	 */
	function anonym($src,$object=null){
		/*** unit("lang.ObjectUtilTest"); */
		/***
			$obj = ObjectUtil::anonym('
						var $abc;
						var $def;
						
						function setAbc($value){
							return $this->abc = $value;
						}
					');
			if(assert(is_object($obj))){
				$obj->abc = "hoge";
				eq("hoge",$obj->abc);
				$obj->setAbc("kekeke");
				eq("kekeke",$obj->abc);
			}
		 * 
		 * 
		 */
		if(func_num_args() <= 2 && is_string($src) && ($object == null || is_object($object))){
			if(!Rhaco::isVariable("RHACO_LANG_OBJECTUTIL_ANONYM")) Rhaco::setVariable("RHACO_LANG_OBJECTUTIL_ANONYM",0);
			Rhaco::setVariable("RHACO_LANG_OBJECTUTIL_ANONYM",Rhaco::getVariable("RHACO_LANG_OBJECTUTIL_ANONYM") + 1);
			$name = "RhacoAnonym".Rhaco::getVariable("RHACO_LANG_OBJECTUTIL_ANONYM");
			Rhaco::phpexe("class ".$name.(is_object($object) ? " extends ".get_class($object) : "")."{\n".$src."\n}");
			return new $name();
		}
		return null;
	}
	
	/**
	 * 先頭のオブジェクトに２番からのオブジェクトのメソッドを追加した新しいクラスのオブジェクトを取得
	 * 先頭のオブジェクトをextendsしたクラスとなる
	 *
	 * @return object
	 */
	function mixin(){
		/*** unit("lang.VariableTest"); */

		$objects = func_get_args();
		$methods = $vars = $references = array();
		$constract = "";

		if(!is_object($objects[0])){
			ExceptionTrigger::raise(new IllegalArgumentException(var_export($objects[0],true)));
			return null;
		}
		foreach(ArrayUtil::arrays($objects,1) as $arg){
			if(is_object($arg)){
				$class = get_class($arg);
				$constract .= sprintf('$this->_CLASS["%s"] = new %s();',$class,$class);

				foreach(get_class_methods($arg) as $method){
					$methods[$method] = sprintf('
											function %s(){
												if($this->_invalidReference("%s")) return null;
												$args = func_get_args();
												return call_user_func_array(array(&$this->_CLASS["%s"],"%s"),$args);
											}
											',$method,$class,$class,$method);
				}
				foreach(get_object_vars($arg) as $key => $value){
					$vars[$key] = 'var $'.$key.';';
					$references[$key] = sprintf('$this->%s = &$this->_CLASS["%s"]->%s;',$key,$class,$key);
				}
			}else if(is_array($arg)){
				$methods[$arg[0]] = sprintf("
										function %s(%s){
											%s
										}",$arg[0],$arg[1],$arg[2]);
			}
		}
		if(isset($methods["__init__"])) unset($methods["__init__"]);
		if(isset($methods["istype"])) unset($methods["istype"]);		
		if(isset($methods["_invalidReference"])) unset($methods["_invalidReference"]);		
		if(isset($vars["_CLASS"])) unset($vars["_CLASS"]);
		Rhaco::setVariable("RHACO_LANG_OBJECTUTIL_MIXIN",Rhaco::getVariable("RHACO_LANG_OBJECTUTIL_MIXIN",0) + 1);
		$name = "RhacoMixin".Rhaco::getVariable("RHACO_LANG_OBJECTUTIL_MIXIN");
		$extends = get_class($objects[0]);
		$code = sprintf('
						class %s extends %s{
							var $_CLASS = array();
							%s
							function %s(){
								%s
								%s
								if(method_exists($this,"%s")) $this->%s();
							}
							%s
							function istype($class){
								if(Variable::istype($class,$this)) return true;
								foreach($this->_CLASS as $obj){
									if(Variable::istype($class,$obj)) return true;
									if(ObjectUtil::isSubclass($obj) && method_exists($obj,"istype") && $obj->istype($class)) return true;
								}
								return false;
							}
							function _invalidReference($name){
								if(!isset($this->_CLASS[$name]) || !is_object($this->_CLASS[$name])){
									return true;
								}
								return false;
							}
						}
						',
						$name,$extends,implode(" ",$vars),$name,$constract,implode(" ",$references),$extends,$extends,implode("",$methods));
		Rhaco::phpexe($code);
		$obj = new $name();

		foreach($objects as $object){
			$obj = ObjectUtil::copyProperties($object,$obj,true,"_CLASS");
		}
		unset($objects,$methods,$vars,$references,$constract);
		return $obj;
	}
	
	/**
	 * プロパティーをコピーする
	 * fromobjectがtag.model.SimpleTagの場合はXMLからコピーする
	 * propertyOnly=falseの場合はsetter,getterでコピーする
	 * return値 = $toobjectとなり$toobjectは変更される
	 *
	 * @param object $fromobject
	 * @param object $toobject
	 * @param boolean $propertyOnly
	 * @return unknown
	 */
	function copyProperties($fromobject,&$toobject,$propertyOnly=false,$excludeProperty=array()){
		/*** unit("lang.ObjectUtilTest"); */
		if(!is_object($toobject)) return null;
		if(!is_object($fromobject)) return $toobject;		

		if($propertyOnly){
			$property = array();
			$excludeProperty = ArrayUtil::arrays($excludeProperty);
			
			if(Variable::istype("SimpleTag",$fromobject)){
				foreach($fromobject->getParameter() as $parameterId => $simpleTagParameter){
					$value = $simpleTagParameter->getValue();
					$property[strtolower($parameterId)] = StringUtil::isInt($value) ? intval($value) : (StringUtil::isFloat($value) ? floatval($value) : $value);
				}
			}else{
				foreach(get_object_vars($fromobject) as $key => $value) $property[strtolower($key)] = $value;
			}
			foreach(get_object_vars($toobject) as $key => $value){
				$check = strtolower($key);
				if(isset($property[$check]) && !in_array($key,$excludeProperty)) $toobject->$key = $property[$check];
			}
		}else{
			$setterList	= array();
			foreach(get_class_methods($toobject) as $methodName){
				if(preg_match("/^set(.+)$/i",$methodName,$setter))	$setterList[strtolower($setter[1])] = $methodName;
			}
			if(Variable::istype("SimpleTag",$fromobject)){
				foreach($fromobject->getParameter() as $parameterId => $simpleTagParameter){
					if(array_key_exists(strtolower($parameterId),$setterList)){
						$value = $simpleTagParameter->getValue();
						$toobject->$setterList[strtolower($parameterId)](StringUtil::isInt($value) ? intval($value) : (StringUtil::isFloat($value) ? floatval($value) : $value));
					}
				}
			}else{
				foreach(get_class_methods($fromobject) as $methodName){
					if(preg_match("/^get(.+)$/i",$methodName,$match) || preg_match("/^is(.+)$/i",$methodName,$match)){
						if(array_key_exists(strtolower($match[1]),$setterList)){
							$toobject->$setterList[strtolower($match[1])]($fromobject->$methodName());
						}
					}
				}
			}
		}
		return $toobject;
	}
	
	/**
	 * ハッシュからオブジェクトにコピー
	 *
	 * @param array $hash
	 * @param object $object
	 * @param boolean $isMethod true setter利用/false プロパティ直
	 * @return object
	 */
	function hashConvObject($hash,&$object,$isMethod=true){
		/*** unit("lang.VariableTest"); */
		if(is_object($object) && is_array($hash)){
			$hash = array_change_key_case($hash);
			$list = array_change_key_case(($isMethod) ? array_flip(get_class_methods($object)) : get_object_vars($object));

			foreach($hash as $key => $value){
				if($isMethod){
					$varname = str_replace("_","",$key);

					if(array_key_exists("set".$key,$list)){
						ObjectUtil::setter($object,$key,$value);
					}else if(array_key_exists("set".$varname,$list)){
						ObjectUtil::setter($object,$varname,$value);
					}
				}else if(array_key_exists($key,$list)){
					$object->$key = $value;
				}
			}
			return $object;
		}
		return false;
	}
	/**
	 * オブジェクトからハッシュにコピー
	 * オブジェクトのプロパティーをハッシュに変換する
	 * $isMethodの場合はメソッドもハッシュに変換する
	 *
	 * @param object $object
	 * @param array $hash
	 * @param boolean $isMethod
	 * @return array
	 */
	function objectConvHash($object,$hash=array(),$isMethod=false){
		/*** unit("lang.VariableTest"); */
		$hash = ArrayUtil::arrays($hash);

		if(is_object($object)){
			$hash = array_merge($hash,get_object_vars($object));

			if($isMethod){
				foreach(array_change_key_case(array_flip(get_class_methods($object))) as $key => $value){
					if(strpos($key,"get") === 0 || strpos($key,"format") === 0) $hash[$key] = $object->$key(null,null,null,null,null);
				}
			}
		}
		return $hash;
	}
	
	/**
	 * メソッドが存在するか
	 *
	 * @param unknown_type $obj
	 * @param unknown_type $method
	 * @return unknown
	 */
	function isMethod($obj,$method){
		if(!empty($obj) && !empty($method)){
			if(is_object($obj)){
				return method_exists($obj,$method);
			}else if(is_string($obj)){
				$methods = ArrayUtil::lowerflip(get_class_methods($obj));
				return isset($methods[strtolower($method)]);
			}
		}
		return false;
	}
	
	/**
	 * 指定のプロパティ値でソート
	 *
	 * @param array(Object) $objecs
	 * @param string $property
	 * @param boolean true 昇順 / false 降順
	 */
	function sort(&$objecs,$property,$order=true){
		/*** unit("lang.ObjectUtilTest"); */
		$ostr = ($order) ? ">=" : "<=";
		uasort($objecs,create_function('$a,$b','return ($a->'.$property.' '.$ostr.' $b->'.$property.') ? 1 : -1;'));
		return $objecs;
	}

	/**
	 * クラスパスの一覧から対象のメソッドをもつオブジェクトの一覧を返す
	 *
	 * @param array(string) $args object、またはclass path
	 * @param array(string) $methods メソッド名
	 * @param boolean $isall true: すべての指定メソッドがある false: 指定メソッドのいずれかがある
	 * @return array(Ojbect)
	 */
	function loadObjects($args,$methods,$isall=false){
		/***
		 * $objects = ObjectUtil::loadObjects("lang.ObjectUtil",array("loadObjects","sort"));
		 * eq(1,sizeof($objects));
		 * assert(Variable::istype("ObjectUtil",$objects[0]));
		 * 
		 * $objects = ObjectUtil::loadObjects("lang.ObjectUtil",array("loadObjects","sort"),false);
		 * eq(1,sizeof($objects));
		 * assert(Variable::istype("ObjectUtil",$objects[0]));
		 * 
		 * $objects = ObjectUtil::loadObjects("lang.ObjectUtil",array("hogehoge","loadObjects","sort"),false);
		 * eq(1,sizeof($objects));
		 * assert(Variable::istype("ObjectUtil",$objects[0]));
		 * 
		 * $objects = ObjectUtil::loadObjects("lang.ObjectUtil",array("loadObjects","sort","hogehoge"),true);
		 * eq(0,sizeof($objects));
		 * 
		 * $objects = ObjectUtil::loadObjects(array("lang.ObjectUtil","util.DocTest"),array("hogehoge","loadObjects","sort"),false);
		 * eq(1,sizeof($objects));
		 * assert(Variable::istype("ObjectUtil",$objects[0]));
		 * 
		 */
		$result = array();
		foreach(ArrayUtil::arrays($args) as $obj){
			if(!empty($obj)){
				if(is_string($obj)) $obj = Rhaco::obj($obj);
				if(is_object($obj)){
					$bool = $isall;

					foreach(ArrayUtil::arrays($methods) as $method){
						if(method_exists($obj,$method) != $isall){
							$bool = !$isall;
							break;
						}
					}
					if($bool) $result[] = $obj;
				}
			}
		}
		return $result;
	}
	
	/**
	 * $objectsに$nameメソッドがある場合に$varsを引数としてメソッドを実行する
	 * $resultが指定された場合、$vars[$result]を結果として返す
	 *
	 * @param array(object) $objects
	 * @param string $name
	 * @param unknown_type $vars
	 * @param unknown_type $result
	 * @return unknown
	 */
	function calls($objects,$name,$vars=array(),$result=null){
		/*** unit("lang.ObjectUtilTest"); */
		$vars = ArrayUtil::arrays($vars);
		$return = ($result !== null && isset($vars[$result])) ? true : false;

		foreach(ArrayUtil::arrays($objects) as $obj){
			if(is_string($obj)) $obj = Rhaco::obj($obj);
			if(is_object($obj) && method_exists($obj,$name)){
				if($return){
					$vars[$result] = call_user_func_array(array($obj,$name),$vars);
				}else{
					call_user_func_array(array($obj,$name),$vars);
				}
			}
		}
		return ($return) ? $vars[$result] : null;
	}

	/**
	 * $objがサブクラスか
	 *
	 * @param Object $obj
	 * @return boolean
	 */
	function isSubclass($obj){
		return (get_parent_class($obj) !== false);
	}
}
?>