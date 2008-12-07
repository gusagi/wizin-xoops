<?php
Rhaco::import("io.FileUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
Rhaco::import("setup.util.ApplicationInstaller");
Rhaco::import("setup.database.model.DatabaseModel");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("setup.model.ProjectInputModel");
Rhaco::import("tag.TemplateParser");
Rhaco::import("util.Logger");
Rhaco::import("exception.model.GenericException");
Rhaco::import("setup.util.SetupUtil");
/**
 * setup.php用　data model
 * 
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class ProjectModel{
	var $version = "1.0";
	var $name = "rhaco application";
	var $description = "";
	var $projectxml = "";

	var $rhacopath = "";
	var $url = "";
	var $templateUrl = "";
	var $templatePath = "";
	var $projectPath = "";	
	
	var $defineLogHtml = "";
	var $defineLogDispLevel = "";	
	var $defineLogFileLevel = "";
	var $defineLogFilePath = "";

	var $defineCachePath = "";
	var $defineTemplateCache = false;
	var $defineTemplateCacheTime = 0;
	var $defineFeedCache = false;
	var	$defineFeedCacheTime = 0;

	var $rhacover = 0;
	var $pathinfo = "";
	var $isinit = false;
	var $installserver = array("http://d.rhaco.org/");

	var $sessionExpire = 0;
	var $sessionCache = "nocache";
	var $cookieExpire = 0;	

	var $formList = array();
	var $defineList = array();
	var $databaseList = array();
	var $applicationList = array();
	var $dbconnectionTypeList = array();

	function start($projectxml){
		$this->__init__($projectxml);

		if(FileUtil::exist($this->projectxml)){
			$parser = new TemplateParser();
			$parser->setStatics("file","FileUtil");
			
			if(SimpleTag::setof($projectTag,SimpleTag::uncomment($parser->read($this->projectxml)),"project")){
				ObjectUtil::copyProperties($projectTag,$this,false);
				
				foreach($projectTag->getIn("session",true) as $inputTag){
					$this->sessionExpire	= $inputTag->isParameter("expire") ? $inputTag->getParameter("expire") : $this->sessionExpire;
					$this->sessionCache	= $inputTag->isParameter("cache") ? $inputTag->getParameter("cache") : $this->sessionCache;
					if(!empty($this->sessionExpire) && (empty($this->sessionCache) || $this->sessionCache == "nocache")) $this->sessionCache = "private";
				}
				foreach($projectTag->getIn("cookie",true) as $inputTag){
					$this->cookieExpire	= $inputTag->isParameter("expire") ? $inputTag->getParameter("expire") : $this->sessionExpire;			
				}
				if(!empty($this->sessionExpire) && empty($this->cookieExpire)){
					$this->cookieExpire = $this->sessionExpire;
				}
				foreach($projectTag->getIn("database",true) as $dbTag){
					$model = new DatabaseModel($dbTag);
					$this->databaseList[$model->recognition_code] = $model;
				}
				foreach($projectTag->getIn("define",true) as $inputTag){
					$input				= ObjectUtil::copyProperties($inputTag,new ProjectInputModel());
					$input->value		= $inputTag->getParameter("value",$inputTag->getValue());
					$this->defineList[$input->name]	= $input;
				}
				foreach($projectTag->getIn(array("input","password","text","select"),true) as $inputTag){
					$input = ProjectInputModel::toInstance($inputTag);
					$this->formList[$input->name] = $input;
				}
				foreach($projectTag->getIn("install",true) as $inputTag){
					foreach($inputTag->getIn("site",true) as $siteTag){					
						$this->installserver[] = trim($siteTag->getParameter("url",$siteTag->getValue()));
					}
				}
				$this->setDescription($projectTag->getInValue("description"));						
			}
		}
		if(version_compare($this->rhacover,Rhaco::rhacoversion()) == 1){
			ExceptionTrigger::raise(new GenericException(Message::_("{1} or above version is required.",$this->rhacover)));
		}
	}
	
	/**
	 * @static
	 *
	 * @param unknown_type $src
	 */
	function toSimpleProjectModel($src){
		$model = new ProjectModel();
		
		if(SimpleTag::setof($projectTag,SimpleTag::uncomment($src),"project")){
			ObjectUtil::copyProperties($projectTag,$model,false);
			$projectTag->getIn("session",true);
			$projectTag->getIn("cookie",true);
			$projectTag->getIn("database",true);
			$projectTag->getIn("define",true);
			$projectTag->getIn(array("input","password","text","select"),true);
			$projectTag->getIn("install",true);

			$model->setDescription($projectTag->getInValue("description"));						
		}
		return $model;
	}
	function __init__($projectxml){
		$this->projectxml = $projectxml;
		$this->url = "http://localhost";

		if(Rhaco::constant("CONTEXT_URL") != null){
			$this->url = Rhaco::constant("CONTEXT_URL");
		}else if(isset($_SERVER["HTTP_USER_AGENT"])){
			$this->url = preg_replace("/^(.+)\/setup\.php.*$/","\\1",
								isset($_SERVER["SCRIPT_URI"]) ? 
									$_SERVER["SCRIPT_URI"] : 
									(isset($_SERVER["SERVER_NAME"]) ? "http://".$_SERVER["SERVER_NAME"].(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "") : "")
							);
		}
		$this->defineCachePath = (Rhaco::constant("CACHE_PATH") != null) ? Rhaco::constant("CACHE_PATH") : FileUtil::path(Rhaco::path(),"work/cache");
		$this->projectPath = (Rhaco::constant("PROJECT_PATH") != null) ? Rhaco::constant("PROJECT_PATH") : Rhaco::path();

		$this->templatePath = (Rhaco::constant("TEMPLATE_PATH") != null) ? Rhaco::constant("TEMPLATE_PATH") : FileUtil::path(Rhaco::path("resources/templates"));
		$this->templateUrl = (Rhaco::constant("TEMPLATE_URL") != null) ? Rhaco::constant("TEMPLATE_URL") : $this->url."/resources/templates";

		$this->defineTemplateCache = Variable::bool(((Rhaco::constant("TEMPLATE_CACHE") != null) ? Rhaco::constant("TEMPLATE_CACHE") : false));
		$this->defineTemplateCacheTime = (Rhaco::constant("TEMPLATE_CACHE_TIME") != null) ? Rhaco::constant("TEMPLATE_CACHE_TIME") : 86400;

		$this->defineFeedCache = Variable::bool(((Rhaco::constant("FEED_CACHE") != null) ? Rhaco::constant("FEED_CACHE") : false));
		$this->defineFeedCacheTime = (Rhaco::constant("FEED_CACHE_TIME") != null) ? Rhaco::constant("FEED_CACHE_TIME") : 10800;

		$this->defineLogHtml = Variable::bool(((Rhaco::constant("LOG_DISP_HTML") != null) ? Rhaco::constant("LOG_DISP_HTML") : true));
		$this->defineLogDispLevel = (Rhaco::constant("LOG_DISP_LEVEL") != null) ? Rhaco::constant("LOG_DISP_LEVEL") : "none";
		$this->defineLogFileLevel = (Rhaco::constant("LOG_FILE_LEVEL") != null) ? Rhaco::constant("LOG_FILE_LEVEL") : "none";
		$this->defineLogFilePath = (Rhaco::constant("LOG_FILE_PATH") != null) ? Rhaco::constant("LOG_FILE_PATH") : FileUtil::path(Rhaco::path(),"work/log");

		foreach(FileUtil::ls(FileUtil::path(Rhaco::rhacopath(),"database/controller")) as $file){
			$path = "database.controller.".$file->originalName;
			$obj = Rhaco::obj($path);
			if(Variable::istype("DbUtilBase",$obj) && $obj->valid()){
				$this->dbconnectionTypeList[$path] = empty($obj->name) ? $file->originalName : $obj->name;
			}
		}
		$this->isinit = (FileUtil::exist(Rhaco::setuppath("Init.php")) && @include_once(Rhaco::setuppath("Init.php")));

		Rhaco::constant("TEMPLATE_PATH",$this->templatePath);
		Rhaco::constant("TEMPLATE_URL",$this->templateUrl);
		Rhaco::constant("PROJECT_PATH",$this->projectPath);
		Rhaco::constant("CONTEXT_URL",$this->url);
	}
	
	/**
	 * アプリケーションファイルのチェック
	 *
	 * @param unknown_type $flow
	 * @return unknown
	 */
	function check($flow){
		if(Rhaco::constant("APPLICATION_ID") == null){
			$this->rhacopath = FileUtil::path($flow->getVariable("rhacopath"));
			$this->generate($flow,false);
		}
		if(!FileUtil::exist($this->projectxml)){
			$this->create();
			ApplicationInstaller::danyFile(Rhaco::setuppath());
		}
		return !ExceptionTrigger::isException();
	}
	
	/**
	 * __settings__.phpとテーブルモデルの生成
	 *
	 * @param unknown_type $request
	 * @param unknown_type $bool
	 * @return unknown
	 */
	function generate($request,$bool=true){
		/*** #pass */
		$parser = new TemplateParser();

		if($request != null){
			ObjectUtil::copyProperties($request->toObject($this),$this,true);

			foreach($this->formList as $key => $formObj){
				$this->defineList[$formObj->name] = $formObj;
			}
			foreach(ArrayUtil::arrays($request->getVariable("formList")) as $key => $hash){
				if(!isset($hash["value"])) $hash["value"] = "";
				$this->defineList[$key]->setValue(StringUtil::getMagicQuotesOffValue($hash["value"]));
			}
			foreach(ArrayUtil::arrays($request->getVariable("databaseList")) as $key => $value){
				$this->databaseList[$key] = ObjectUtil::hashConvObject($value,$this->databaseList[$key],false);
			}
		}
		$parser->setVariable("project",$this);
		$settings = ApplicationInstaller::getPhp($parser->read(SetupUtil::template("settings.php.inc")));

		if(FileUtil::exist(Rhaco::path("__settings__.php"))){
			$replace = "";
			$src = FileUtil::read(Rhaco::path("__settings__.php"));
			if(preg_match_all("/<\?php.+?\?>/ms",$src,$match)){
				foreach($match[0] as $value){
					if(strpos($value,"Rhaco.php") === false){
						$replace .= $value;
					}else{
						$replace .= $settings;
					}
				}
				$settings = $replace;
			}
		}
		if(FileUtil::write(Rhaco::path("__settings__.php"),$settings)) include_once(Rhaco::path("__settings__.php"));

		if($bool){
			$tablefiles = array();
			foreach($this->databaseList as $key => $database){
				$parser->setVariable("database",$database);
	
				foreach($database->tableList as $table){
					$parser->setVariable("table",$table);
					$template = ApplicationInstaller::getPhp($parser->read(SetupUtil::template(
						(Variable::istype("ExTableModel",$table) ? "library/model/table/ExtTableObject.php.inc" : "library/model/table/TableObject.php.inc")
						)));
					$tablepath = FileUtil::path($this->projectPath,sprintf("library/model/table/%sTable.php",$table->method));
	
					if(!FileUtil::exist($tablepath) || $template !== FileUtil::read($tablepath)) FileUtil::write($tablepath,$template);
					$tablefiles[$tablepath] = $tablepath;
				}
				foreach(FileUtil::ls(FileUtil::path($this->projectPath,sprintf("library/model/table/"))) as $file){
					if(!array_key_exists($file->getFullname(),$tablefiles)) FileUtil::rm($file->getPath());
				}
				foreach($database->tableList as $table){
					if(!FileUtil::exist(FileUtil::path($this->projectPath,sprintf("library/model/%s.php",$table->method)))){
						$parser->setVariable("table",$table);
						$src = ApplicationInstaller::getPhp($parser->read(SetupUtil::template("library/model/DataObject.php.inc")));
						FileUtil::write(FileUtil::path($this->projectPath,sprintf("library/model/%s.php",$table->method)),$src);
					}
				}
			}
		}
		return !ExceptionTrigger::isException();
	}
	
	/**
	 * 処理生成ファイルの作成
	 *
	 * @return unknown
	 */
	function create(){
		/*** #pass */
		if(!FileUtil::exist($this->projectxml)){
			$tag = new SimpleTag("project");
			$tag->setParameter("rhacover",Rhaco::rhacoversion());
			$tag->setParameter("version","0.0.1");
			$tag->setParameter("name",basename(getcwd()));
			$tag->setParameter("xmlns","http://rhaco.org");							
			$tag->setParameter("xmlns:xsi","http://www.w3.org/2001/XMLSchema-instance");
			$tag->setParameter("xsi:schemaLocation","http://rhaco.org http://m.rhaco.org/xsd/project_".str_replace(".","_",Rhaco::rhacoversion()).".xsd");							
			$tag->setValue("\n");
			FileUtil::write($this->projectxml,$tag->get());

			ApplicationInstaller::writeInitFile(Rhaco::path());
			FileUtil::mkdir($this->templatePath);
			FileUtil::mkdir(Rhaco::lib("model"));
		}
		return !ExceptionTrigger::isException();
	}
	
	/**
	 * アプリケーション処理初期の実行
	 *
	 * @return unknown
	 */
	function appInit(){
		/*** #pass */
		if(is_file(Rhaco::setuppath("Init.php")) && @include_once(Rhaco::setuppath("Init.php"))){
			if(class_exists("Init")){
				$init = new Init();
				if(method_exists($init,"run")){
					$init->run();
					Logger::debug(Message::_("initializing of project is executed"));
				}
			}
		}
		if(!empty($this->pathinfo)){
			ApplicationInstaller::rewriteFile($this->pathinfo);
		}
	}
	
	/**
	 * テーブルの作成
	 *
	 * @param unknown_type $con
	 * @return unknown
	 */
	function createTable($con){
		/*** #pass */
		foreach($this->databaseList as $key => $database){
			if(Variable::iequal($key,$con)){
				return $database->create($con);
			}
		}
		return false;
	}
	
	function setUrl($value){
		$this->url = $value;
	}	
	function setRhacopath($value){
		$this->rhacopath = str_replace("\\","/",$value);
	}
	function setDefineLogHtml($value){
		$this->defineLogHtml	= $value;
	}
	function setDefineLogDispLevel($value){
		$this->defineLogDispLevel	= $value;
	}
	function setDefineLogFileLevel($value){
		$this->defineLogFileLevel	= $value;
	}
	function setDefineLogFilePath($value){
		$this->defineLogFilePath	= $value;
	}
	function setDefineTemplateCache($value){
		$this->defineTemplateCache	= Variable::bool($value);
	}
	function setDefineCachePath($value){
		$this->defineCachePath	= $value;
	}
	function setDefineTemplateCacheTime($value){
		$this->defineTemplateCacheTime	= $value;
	}
	function setDefineFeedCache($value){
		$this->defineFeedCache	= Variable::bool($value);
	}
	function setDefineFeedCacheTime($value){
		$this->defineFeedCacheTime	= $value;
	}
	function setRhacover($value){
		$this->rhacover = $value;
	}
	function setTemplatePath($value){
		$this->templatePath = $value;
	}
	function setTemplateUrl($value){
		$this->templateUrl = $value;
	}
	function setProjectPath($value){
		$this->projectPath = $value;
	}
	function setName($value){
		$this->name = $value;
	}
	function setDescription($value){
		$this->description = StringUtil::toULD(trim($value));
	}
	function setVersion($value){
		$this->version = strval($value);
	}
	function defineTemplateCache(){
		/*** #pass */
		return Variable::bool($this->defineTemplateCache,true);
	}
	function defineFeedCache(){
		/*** #pass */		
		return Variable::bool($this->defineFeedCache,true);
	}
	function defineLogHtml(){
		/*** #pass */
		return Variable::bool($this->defineLogHtml,true);
	}
	function setPathinfo($value){
		if(!empty($value)){
			$this->pathinfo = $value;
			$this->isinit = true;
		}
	}
	function getInstallServer(){
		return ArrayUtil::arrays($this->installserver);
	}
}
?>