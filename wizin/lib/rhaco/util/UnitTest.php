<?php
Rhaco::import("lang.Assert");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("io.FileUtil");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.PermissionException");
/**
 * #ignore
 * 単体テストクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class UnitTest extends Assert{
	var $testtarget	= array();

	function UnitTest($path){
		$this->path = $path;
		if(empty($this->class)) $this->class = get_class($this);

		$this->begin();
		$this->testtarget = array_flip(ArrayUtil::arrays($this->testtarget));
		$this->_test();
		$this->finish();
	}
	
	/**
	 * UnitTestクラスの前処理
	 */
	function begin(){
	}
	
	/**
	 * UnitTestクラスの後処理
	 */
	function finish(){
	}
	
	/**
	 * テストメソッドの前処理
	 */
	function setUp(){
	}

	/**
	 * テストメソッドの後処理
	 */
    function tearDown(){
	}
	function _test(){
		$lines = array();

		if(empty($this->class)) $this->class = get_class($this);
		if(!empty($this->path)){
			$src = FileUtil::read($this->path);

			if(!empty($src) && preg_match_all("/[\s]function[\s]+(test[\w]+)\(/",$src,$match,PREG_OFFSET_CAPTURE)){
				foreach($match[1] as $key => $m){
					$lines[strtolower($m[0])] = substr_count(substr($src,0,$m[1]),"\n") + 1;
				}
			}
		}
		foreach(get_class_methods($this) as $methodName){
			if(preg_match("/^test(.+)$/",$methodName)){
				if(!Variable::iequal($this->class,$methodName)){
					if($this->_isTarget($methodName)){
						$lmethod = strtolower($methodName);
//						$this->method = $methodName;
//						if($this->line == 0 && isset($lines[$lmethod])) $this->line = $lines[$lmethod];
						
						$this->setUp();
							$this->$methodName();
						$this->tearDown();
					}
				}
			}
		}
	}
	function _isTarget($name){
		if(!is_array($this->testtarget) || empty($this->testtarget)){
			return true;
		}
		return array_key_exists(strtolower($name),$this->testtarget);
	}
	function setTarget($name){
		$list	= ArrayUtil::arrays($name);
		$new	= array();
		foreach($list as $value){
			$new[] = strtolower($value);
		}
		$this->testtarget = array_merge(ArrayUtil::arrays($this->testtarget),$new);
	}

	

	/**
	 * テストの実行/出力
	 *
	 * @param string $path
	 * @param boolean $html
	 */
	function execute($path,$html=false){
		UnitTest::load($path);
		UnitTest::flush($html);
	}
	
	/**
	 * テストの実行
	 *
	 * @param string $path
	 */
	function load($path){
		Rhaco::setVariable("RHACO_CORE_IMPORT_TESTS",true);
		if(is_dir($path)){
			foreach(FileUtil::find("/\.php$/i",$path,true) as $file){
				UnitTest::load($file->getFullname());
			}
		}else if(is_file($path) || is_file($path.".php")){
			if(!is_file($path)) $path = $path.".php";
			$src = FileUtil::read($path);

			if(preg_match("/class[\s]+([\w]+)[\s]+extends[\s]+UnitTest[\s]*\{/s",$src,$match)){
				if(!Rhaco::isVariable("RHACO_CORE_DOC_IN_UNIT",$path) && include_once($path)){
					Rhaco::addVariable("RHACO_CORE_DOC_IN_UNIT",$path,$path);
					new $match[1]($path);
				}
			}
		}else if(Rhaco::import($path)){
			UnitTest::load(Rhaco::importpath($path));
		}else{
			ExceptionTrigger::raise(new PermissionException($path));
		}
		Rhaco::setVariable("RHACO_CORE_IMPORT_TESTS",false);
	}
}
?>