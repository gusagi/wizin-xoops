<?php
Rhaco::import("util.DocTest");
Rhaco::import("lang.model.AssertResult");
Rhaco::import("setup.util.PotGenerator");
Rhaco::import("util.DocUtil");
Rhaco::import("setup.util.AuthFileManager");
Rhaco::import("lang.Env");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.Variable");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("util.Logger");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("exception.model.PermissionException");
Rhaco::import("exception.model.SqlException");
Rhaco::import("io.FileUtil");
Rhaco::import("generic.Flow");
Rhaco::import("setup.util.SetupUtil");
Rhaco::import("network.http.Header");
Rhaco::import("tag.feed.FeedParser");
Rhaco::import("generic.InstallViews");
/**
 * セットアップで使用するViews
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class SetupView extends Flow{
	
	function info(){
		$snap = new Snapshot();
			phpinfo();
		$info = $snap->get();
		$snap->close();

		if(SimpleTag::setof($tag,$info,"body")){
			$this->setVariable("info",$tag->getValue());
		}
		return $this->parser(SetupUtil::template("setup/info.html"));	
	}	
	
	function intltool(){
		$flow = new PotGenerator();
		return $flow->parser();
	}

	function test(){
		$ex = Rhaco::constant("EX_TESTS_DIR");
		if($this->isPost()){
			AssertResult::clear();
			$paths = $this->getVariable("path");
			if(empty($paths)) $paths = (!empty($ex) ? array(Rhaco::path(),$ex) : Rhaco::path());
			DocTest::load($paths,Rhaco::path());
		}
		$paths = $this->_ls();
		if(!empty($ex)){
			$paths[$ex] = $ex;
			foreach(FileUtil::find("/\.php$/",$ex,true) as $file){
				if(!preg_match("/\/_[\w]+/",$file->getDirectory())){
					$paths[$file->getDirectory()] = $file->getDirectory();
					$paths[$file->getFullname()] = $file->getFullname();
				}
			}
		}
		ksort($paths);
		$this->setVariable("paths",$paths);
		$this->setVariable("results",AssertResult::results());
		$this->setVariable("count_success",AssertResult::count(AssertResult::typeSuccess()));
		$this->setVariable("count_fail",AssertResult::count(AssertResult::typeFail()));
		$this->setVariable("count_none",AssertResult::count(AssertResult::typeNone()));
		$this->setVariable("count_pass",AssertResult::count(AssertResult::typePass()));
		$this->setVariable("count_all",AssertResult::count());		

		ExceptionTrigger::clear();
		AssertResult::clear();
		return $this->parser(SetupUtil::template("setup/doctest.html"));
	}
	function _ls($path=""){
		$paths = array();
		if(empty($path)) $path = Rhaco::path();
		foreach(FileUtil::find("/\.php$/",$path,true) as $file){
			if(
				$file->getName() != "setup.php"
				&& $file->getName() != "member_xml.php"				
				&& strpos($file->getDirectory(),"tests") === false
				&& !preg_match("/\/_[\w]+/",$file->getFullname())
				&& strpos($file->getDirectory(),"model/table") === false
			){
				$paths[$file->getDirectory()] = $file->getDirectory();
				$paths[$file->getFullname()] = $file->getFullname();				
			}
		}
		return $paths;
	}
	function doc($path){
		/*** #pass */
		$this->setVariable("docs",DocUtil::parse($this->getVariable("path",$path)));
		$this->setVariable("paths",$this->_ls($path));		
		return $this->parser(SetupUtil::template("setup/docview.html"));
	}	
	function member($path,$ismsg=true){
		/*** #pass */
		$auth = new AuthFileManager($path);
		$flow = $auth->template();
		$flow->setVariable("ismsg",$ismsg);
		return $flow->parser(SetupUtil::template("setup/member.html"));
	}

	function generate($projectModel){
		/*** #pass */
		if($this->isPost()){
			if($this->isVariable("setting_generate")){
				if($projectModel->generate($this)){
					Header::redirect(Env::called());
				}
			}else if($this->isVariable("setting_init")){
				$projectModel->appInit();
			}else if($this->isVariable("setting_cache_delete")){
				foreach(FileUtil::ls(Rhaco::constant("CACHE_PATH",Rhaco::path("work/cache/"))) as $path) FileUtil::rm($path->getPath());
				Logger::debug(Message::_("clear cache"));
			}
		}else{
			if(!is_writable(Rhaco::path("__settings__.php")) && !is_writable(Rhaco::path())){
				$exception = new PermissionException(Rhaco::path("__settings__.php"));
				ExceptionTrigger::raise($exception);
				$this->error = $exception->getMessage();
			}
		}
		return $this->parser(SetupUtil::template("setup/setup.html"));
	}

	function db($projectModel){
		/*** #pass */
		if($this->isPost() && $this->isVariable("connection")){
			$con = $this->getVariable("connection");

			if($this->isVariable("database_create_sql")){
				foreach($projectModel->databaseList as $key => $database){
					if(Variable::iequal($key,$con)){
						$database->createSql($con);
						break;
					}
				}
			}else if($this->isVariable("database_create")){
				$bool = false;
				foreach($projectModel->databaseList as $key => $database){
					if(Variable::iequal($key,$con)){
						$database->create($con);
						$database->import($con);
						$bool = true;
						break;
					}
				}
				if(!$bool) ExceptionTrigger::raise(new NotFoundException(Message::_("database {1}",$con)));
			}else if($this->isVariable("database_import")){
				$bool = false;
				foreach($projectModel->databaseList as $key => $database){
					if(Variable::iequal($key,$con)){
						$database->import($con);
						$bool = true;
						break;
					}
				}
				if(!$bool) ExceptionTrigger::raise(new NotFoundException(Message::_("database {1}",$con)));
			}else if($this->isVariable("database_drop")){
				$bool = false;
				foreach($projectModel->databaseList as $key => $database){
					if(Variable::iequal($key,$con)){
						$database->droptable($con);
						$bool = true;
						break;
					}
				}
				if(!$bool) ExceptionTrigger::raise(new NotFoundException(Message::_("database {1}",$con)));
			}
		}
		return $this->parser(SetupUtil::template("setup/database/index.html"));
	}
	
	function sql($table){
		/*** #pass */
		$results = array();
		$con = null;

		if($this->isPost()){
			$object = Rhaco::obj("model.".$table);

			if(Variable::istype("TableObjectBase",$object)){
				$con = call_user_func(array(get_class($object),"connection"));
				$db = new DbUtil($con);
				if($db->query($this->getVariable("sql"))){
					while($db->next()){
						$results[] = $db->getResultset();
					}
				}else{
					ExceptionTrigger::raise(new SqlException($db->error()));
				}
			}
		}
		if(!empty($results)) $this->setVariable("keys",array_keys($results[0]));
		if(!empty($con)) $this->setVariable("connection",$con->id);
		$this->setVariable("results",$results);
		$this->setVariable("isdata",!empty($results));
		return $this->parser(SetupUtil::template("setup/database/sql.html"));
	}
	function install(){
		$servers = array();
		$items = array();
		$message = "";
		$url = func_get_args();

		FeedParser::disableCache();
		foreach(FeedParser::getItem($url) as $item){
			$servers[$item->getTitle()] = $item->getLink();
		}
		$servers = array_flip($servers);
		asort($servers);
		
		if($this->isVariable("server")){
			$items = FeedParser::getItem($this->getVariable("server")."?q=".$this->getVariable("q"));
			$items = ObjectUtil::sort($items,"getTitle()");
		}
		FeedParser::enableCache();
		
		if($this->isVariable("install") && $this->isVariable("urls")){
			set_time_limit(0);
			foreach($this->getVariable("urls") as $url){
				list($category,$installpath) = InstallViews::installPath($url,$servers[$this->getVariable("server")]);
				if(!FileUtil::unpack(Http::body($url),$installpath)
				){
					$message = "install fail [".$url."]";
				}
			}
			if(empty($message)){
				$message = "installed";
				if($category == "application"){
					FileUtil::rm(Rhaco::path("__settings__.php"));
					Header::redirect(Rhaco::url("setup.php"));
				}
			}
		}
		$this->setVariable("server_list",$servers);
		$this->setVariable("item_list",$items);
		$this->setVariable("message",$message);
		return $this->parser();
	}
}
?>