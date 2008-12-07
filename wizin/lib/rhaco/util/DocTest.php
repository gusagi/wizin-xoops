<?php
Rhaco::import("io.FileUtil");
Rhaco::import("lang.Assert");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("util.UnitTest");
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
class DocTest{
	function run($path){
		$src = StringUtil::toULD(FileUtil::read($path));

		if(preg_match("/class[\s]+([\w][\w\t\s\n]*)\{/s",$src,$match)){
			list($doc,$null) = explode($match[0],$src,2);

			if(strpos(StringUtil::comments($doc),"#ignore") === false && include_once($path)){
				$classname = trim(preg_replace("/[\t\s\n]+extends[\t\s\n]+[\w]+$/s","",$match[1]));
				$testclass = $classname."_".uniqid("")."Test";

				if(class_exists($classname)){
					$functions = $this->_parse($src);
					$testfuncs = "";
	
					foreach($functions as $line => $func){
						list($name,$code) = $func;
						
						$test = StringUtil::comments($code);
						if(!empty($test)){
							if(strpos($test,"#pass") !== false){
								$test = sprintf("\n\$this->setMethod('%s',%d);\n\$this->pass(<<< __COMMENT__\n%s\n__COMMENT__\n);",$name,$line,str_replace(array("#pass","\$"),array("","\\$"),$test));
							}else if(preg_match_all("/[\s](eq|neq|assert|unit)\(/",$code,$match,PREG_OFFSET_CAPTURE)){
								foreach($match[0] as $key => $cur){
									$aline = substr_count(substr($code,0,$cur[1]),"\n") + $line;
									switch($match[1][$key][0]){
										case "eq":
											$code = str_replace($cur[0],sprintf("\n\$this->setMethod('%s',%d);\n\$this->assertEquals(",$name,$aline),$code);
											break;
										case "neq":
											$code = str_replace($cur[0],sprintf("\n\$this->setMethod('%s',%d);\n\$this->assertNotEquals(",$name,$aline),$code);
											break;
										case "assert":
											$code = str_replace($cur[0],sprintf("\n\$this->setMethod('%s',%d);\n\$this->assertTrue(",$name,$aline),$code);
											break;
										case "unit":
											$code = str_replace($cur[0],sprintf("\nUnitTest::load(",$name,$aline),$code);
									}
								}
								$test = StringUtil::comments($code);
							}else if($this->_isNotDisFunction($name,$classname)){
								$test = sprintf("\n\$this->setMethod('%s',%d);\n\$this->none();",$classname,$name,$line);
							}
						}else if($this->_isNotDisFunction($name,$classname)){
							$test = sprintf("\n\$this->setMethod('%s',%d);\n\$this->none();",$name,$line);
						}
						$testfuncs .= sprintf("\nfunction test%s(){\n%s\n}",$name,$test);
					}
					if(!empty($testfuncs)){
						$classsrc = sprintf('
								class %s extends UnitTest{
									function %s(){
										$this->setClass("%s","%s");
										parent::UnitTest("%s");
									}
									%s
								}
								new %s();
						',$testclass,$testclass,$classname,$path,$path,$testfuncs,$testclass);
						Rhaco::phpexe($classsrc);
					}
				}
			}
		}
	}
	function _isNotDisFunction($name,$class){
		return (!preg_match("/^(_|is|set|get)[\w\_\d]*$/",$name) && !Variable::equal($class,$name,false));
	}
	function _parse($src){
		$result = array();
		$src = preg_replace("/^\/\/.+/m","",$src);
		if(preg_match_all("/\/[\*]+.+?\*\//s",$src,$comments)){
			foreach($comments[0] as $value){
				if(substr($value,0,4) != "/***"){
					$src = str_replace($value,str_repeat("\n",substr_count($value,"\n")),$src);
				}
			}
		}
		if(preg_match_all("/[\s]function[\s]+([\w]+)\(/",$src,$match,PREG_OFFSET_CAPTURE)){
			$size = sizeof($match[0]) - 1;
			foreach($match[0] as $key => $m){
				$cur = $m[1];
				$next = ($size > $key) ? $match[0][$key+1][1] - $match[0][$key][1] : strlen($src);
				$tmp = substr($src,$cur,$next);
				$result[substr_count(substr($src,0,$cur),"\n") + 1] = array($match[1][$key][0],$tmp);
			}
		}
		return $result;
	}

	
	/**
	 * テストの実行/出力
	 *
	 * @static 
	 * @param string $dirs
	 * @param string $projectpath
	 * @param boolean $html
	 */
	function execute($dirs,$projectpath=""){
		DocTest::load($dirs,$projectpath);
		DocTest::flush();
	}
	
	/**
	 * テストの実行
	 *
	 * @static 
	 * @param string $dirs
	 * @param string $projectpath
	 */
	function load($dirs){
		$doc = new DocTest();
		Rhaco::setVariable("RHACO_CORE_IMPORT_TESTS",true);
		$list = array(Rhaco::rhacopath()."setup",Rhaco::setuppath());
		foreach(ArrayUtil::arrays($dirs) as $dir){
			if(is_dir($dir)){
				foreach(FileUtil::find("/\.php$/i",$dir,true) as $file){
					$bool = true;

					foreach($list as $e){
						if($bool && strpos($file->getFullname(),$e) !== false){
							$bool = false;
							break;
						}
					}
					if($bool) DocTest::load($file->getFullname());
				}
			}else if(is_file($dir)){
				$doc->run($dir);
			}else{
				ExceptionTrigger::raise(new PermissionException($dir));
			}
		}
		Rhaco::setVariable("RHACO_CORE_IMPORT_TESTS",false);
	}
	function flush(){
		Assert::flush();
	}
}
?>