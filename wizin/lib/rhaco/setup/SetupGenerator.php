<?php
Rhaco::import("resources.Message");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("network.http.RequestLogin");
Rhaco::import("network.http.Header");
Rhaco::import("io.Stream");
Rhaco::import("io.FileUtil");
Rhaco::import("database.model.TableObjectBase");
Rhaco::import("database.model.DbConnection");
Rhaco::import("setup.util.ApplicationInstaller");
Rhaco::import("setup.util.SetupUtil");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("exception.model.IllegalArgumentException");
Rhaco::import("setup.database.DbUtilInitializer");
Rhaco::import("setup.database.model.DatabaseModel");
Rhaco::import("setup.model.ProjectModel");
Rhaco::import("setup.model.ProjectInputModel");
Rhaco::import("generic.Urls");
Rhaco::import("generic.Flow");
Rhaco::import("generic.InstallViews");
Rhaco::import("lang.Env");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.Variable");
Rhaco::import("network.http.model.RequestLoginConditionFile");
Rhaco::import("database.model.Criteria");
Rhaco::import("database.model.Criterion");
Rhaco::import("tag.feed.FeedParser");
Rhaco::import("setup.util.SetupCli");
/**
 * SetupGenerator
 * 
 * セットアップ処理を行います。
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class SetupGenerator{
	var $error;

	/**
	 * コンストラクタ
	 *
	 * @param string $rhacopath
	 * @return SetupGenerator
	 */
	function SetupGenerator($rhacopath){
		Message::loadRhacoMessages();
		Rhaco::constant("NOT_MAKE_CACHE",true);
		Rhaco::constant("CONTEXT_PATH",FileUtil::path(getcwd()));
		RequestLogin::setLoginSessionName("setup");

		$flow = new Flow();
		if(!empty($rhacopath)) $flow->setVariable("rhacopath",$rhacopath);

		$projectModel = new ProjectModel();
		$projectModel->start(Rhaco::setuppath("project.xml"));
		$projectModel->check($flow);

		if(empty($_SERVER["HTTP_USER_AGENT"])){
			new SetupCli($projectModel,$rhacopath);
		}
		if(ExceptionTrigger::invalid()){
			$flow->write(SetupUtil::template("setup/invalid.html"));
		}else{
			$flow->requestAttach("_at",SetupUtil::template());

			if($flow->map(0) == "logout"){
				RequestLogin::logout();
				Header::redirect(Env::called());
			}
			RequestLogin::loginRequired(new RequestLoginConditionFile(Rhaco::path("__settings__.php")));

			$exvariables = array();
			$tableObject = null;
			$adminredirect = null;

			if($flow->map(0) == "database"){
				$modelname = $flow->map(3);
				
				if(!empty($modelname)){
					$tableObject = Rhaco::obj("model.".$modelname);
					if(Variable::istype("TableObjectBase",$tableObject)){
						$adminredirect = Rhaco::page("setup")."/database/admin/list/".$modelname;
						foreach($flow->getVariable() as $name => $value){
							if(preg_match("/^save_(.+)$/",$name,$match)){
								$adminredirect = Rhaco::page("setup")."/database/admin/".$match[1]."/".$modelname;
								break;
							}
						}
					}
				}
				$asc = (Variable::iequal($flow->getVariable("so"),$flow->getVariable("o")) && $flow->getVariable("a") == "a") ? "d" : "a";	
				$exvariables = array_merge($exvariables,array("t"=>$flow->map(3),"o"=>$flow->getVariable("so"),"a"=>$asc));
			}
			$tableModels = $this->_dbConnections($projectModel);
			$parser = Urls::parser(array(
								"^info"=>array("class"=>"setup.util.SetupView","method"=>"info"),
								"^rhacodoc"=>array("class"=>"setup.util.SetupView","method"=>"doc","args"=>Rhaco::rhacopath()),
								"^member"=>array("class"=>"setup.util.SetupView","method"=>"member","args"=>array(Rhaco::path("__settings__.php"),false)),

								"^settings/generate"=>array("class"=>"setup.util.SetupView","method"=>"generate","args"=>$projectModel,"default"=>true),
								"^settings/member"=>array("class"=>"setup.util.SetupView","method"=>"member","args"=>Rhaco::resource("member_xml.php")),
								"^settings/install"=>array(
													"template"=>SetupUtil::template("setup/install.html"),
													"class"=>"setup.util.SetupView",
													"method"=>"install",
													"args"=>$projectModel->getInstallServer()
													),
								"^i18n/message"=>array("class"=>"setup.util.SetupView","method"=>"intltool"),
								"^test"=>array("class"=>"setup.util.SetupView","method"=>"test"),
								"^document/api"=>array("class"=>"setup.util.SetupView","method"=>"doc","args"=>Rhaco::path()),

								"^database/query/(.+)[/]*$"=>array("class"=>"setup.util.SetupView","method"=>"sql"),
								"^database[/]*$"=>array("class"=>"setup.util.SetupView","method"=>"db","var"=>array("connections"=>$tableModels),"args"=>array($projectModel)),
								"^database/admin/list/.+[/]*$"=>array(
													"template"=>SetupUtil::template("setup/database/list.html"),
													"method"=>"read",
													"args"=>array($tableObject,null,"admin")
												),
								"^database/admin/create/.+[/]*$"=>array(
													"template"=>SetupUtil::template("setup/database/create_form.html"),
													"method"=>"create",
													"args"=>array($tableObject,array($adminredirect,true))
												),
								"^database/admin/update/.+?/([^/]+)[/]*$"=>array(
													"template"=>SetupUtil::template("setup/database/update_form.html"),
													"method"=>"update",
													"args"=>array($tableObject,new Criteria(Criterion::fact()),array($adminredirect,true))
												),
								"^database/admin/drop/.+?/([^/]+)[/]*"=>array(
													"template"=>SetupUtil::template("setup/database/drop_form.html"),
													"method"=>"drop",
													"args"=>array($tableObject,new Criteria(Criterion::fact()),array($adminredirect))
												),
								"^database/admin/export/(.+)[/]*$"=>array(
													"template"=>SetupUtil::template("setup/database/list.html"),
													"method"=>"export",
													"args"=>array($tableObject,null,"admin")
												),
								"^database/admin/import/(.+)[/]*$"=>array(
													"template"=>SetupUtil::template("setup/database/list.html"),
													"method"=>"import",
													"args"=>array($tableObject)
												),
			
						));
			$submenu = array();
			$mainmenu = array("settings"=>"settings/generate","database"=>"database","i18n"=>"i18n/message","test"=>"test","document"=>"document/api");
			$generalmenu = array("phpinfo"=>"info","rhacodoc"=>"rhacodoc","setup member"=>"member",);

			switch($flow->map(0)){
				case "database":
				case "document":
					$submenu = array("api"=>"document/api",
					);
					break;
				case "test":
				case "i18n":
					break;
				case "settings":
				default:
					$submenu = array("generate"=>"settings/generate",
									"member"=>"settings/member",
									"install"=>"settings/install",
					);
			}
			if(RequestLogin::isLoginSession()) $generalmenu["logout"] = "logout";
			if(empty($tableModels)) unset($mainmenu["database"]);
			$parser->setVariable(ObjectUtil::objectConvHash($projectModel));
			$parser->setVariable("generalmenu",$generalmenu);			
			$parser->setVariable("mainmenu",$mainmenu);
			$parser->setVariable("submenu",$submenu);
			$parser->setVariable("dblink",array("index"=>"/database/admin/",
													"list"=>"/database/admin/list/",
													"create"=>"/database/admin/create/",
													"update"=>"/database/admin/update/",
													"drop"=>"/database/admin/drop/",
													"import"=>"/database/admin/import/",
													"export"=>"/database/admin/export/",
													"query"=>"/database/query/",
									));
			$parser->setVariable("projectname",$projectModel->name);
			$parser->setVariable("projectdesc",$projectModel->description);
			$parser->setVariable("projectver",$projectModel->version);
			$parser->setVariable("rhacopath",$rhacopath);
			$parser->setVariable($exvariables);
			$parser->setVariable("phpvar",phpversion());
			$parser->setFilter("setup.filter.SetupViewsFilter");
			$parser->write();
		}
	}
	function _dbConnections($projectModel){
		$results = array();		

		if(FileUtil::exist(Rhaco::lib("model/table"))){
			foreach(FileUtil::ls(Rhaco::lib("model/table")) as $file){
				$model = substr($file->getOriginalName(),0,-5);
				$obj = Rhaco::obj("model.".$model);
				if(Variable::istype("TableObjectBase",$obj)){
					$connection = $obj->connection();
					if(!isset($results[$connection->id])){
						$results[$connection->id] = array("description"=>"","default"=>false,"tables"=>array());				
						
						foreach($projectModel->databaseList as $code => $db){
							if(Variable::iequal($code,$connection->id)){
								$results[$connection->id]["description"] = $db->description;
								$results[$connection->id]["default"] = $db->isDefault();
								break;
							}
						}
					}
					$results[$connection->id]["tables"][$model] = $obj;					
				}
			}
		}
		return $results;
	}
}
?>