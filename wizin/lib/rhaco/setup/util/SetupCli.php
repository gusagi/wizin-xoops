<?php
Rhaco::import("io.Stream");
Rhaco::import("util.DocTest");
Rhaco::import("util.DocUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Variable");
Rhaco::import("network.http.Http");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.FileUtil");
/**
 * CLI用のセットアップ処理を行います。
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright rhaco project. All rights reserved.
 */
class SetupCli{
	var $projectModel;
	var $rhacopath;

	function SetupCli($projectModel,$rhacopath){
		Logger::disableDisplay();		
		$this->projectModel = $projectModel;
		$this->rhacopath = $rhacopath;
		$multi = 0;
		$store = "";

		while(true){
			print(">> ");
			$in = Stream::stdin();

			if($in != ""){
				if($in == "." || $in == "q"){
					break;
				}else if(preg_match("/^test (.+)$/",$in,$match)){
					$this->_doctest(trim($match[1]));
				}else if(preg_match("/^doc (.+)$/",$in,$match)){
					$this->_doc(trim($match[1]));
				}else if(preg_match("/^ls (.+)$/",$in,$match)){
					$this->_ls(trim($match[1]));
				}else if(preg_match("/create[\s]+(.+)$/",$in,$match)){
					$this->_create($match[1]);
				}else if($in == "generate"){
					$this->_generate();
				}else if($in == "init"){
					$this->_init();
				}else if($in == "install"){
					$this->_install_dir();
				}else if(preg_match("/install[\s]+([^\s]+)[\s]+([^\s]+)$/",$in,$match)){
					$this->_install($match[1],$match[2]);
				}else if(preg_match("/install[\s]+(.+)$/",$in,$match)){
					$this->_install_list($match[1]);
				}else{
					$check = preg_replace("/([\"\']).+?\\1/","",$in);
					$multi = $multi + substr_count($check,"{") - substr_count($check,"}");
					$store .= $in;	
				
					if($multi == 0){
						if(trim($store) != ""){
							ob_start();
							eval($store);
							$store = "";
							$print = ob_get_clean();
							if($print !== ""){
								print($print."\n");
							}
						}
					}
				}
			}
			if(ExceptionTrigger::isException()){
				foreach(ExceptionTrigger::get() as $name => $exception){
					print("!!warning!! ".$exception->getDetail()."\n");
				}
				ExceptionTrigger::clear();
			}
		}
		Rhaco::end();
	}
	
	function _ls($path){
		foreach(FileUtil::ls($path) as $file){
			print($file->getFullname()."\n");
		}
	}

	function _doctest($path){
		if(!is_file($path) && !is_dir($path)){
			if(Rhaco::import($path)){
				$fullpath = Rhaco::importpath($path);
				if($fullpath !== null) $path = $fullpath;
			}
		}
		DocTest::execute($path,Rhaco::path());
	}
	function _doc($path){
		$method = "";
		
		if(strpos($path,"::") !== false){
			list($path,$method) = explode("::",$path);
		}
		if(Rhaco::import($path)){
			$result = DocUtil::parse(Rhaco::importpath($path));
			foreach($result as $d){
				if(!empty($method)){
					$bool = false;
					foreach($d->function as $f){
						if(Variable::iequal($f->name,$method)){
							print("name:\n\t".$d->name."::".$f->name."\n");
							print("\n");
							print("description:\n\t".str_replace("\n","\n\t",$f->description));
							print("\n");
							$bool = true;
							break;
						}
					}
					if(!$bool){
						print("undefined method\n");
					}
				}else{
					print("name:\n\t".$d->name."\n");
					print("\n");
					if($d->isSubclass()){
						print("extends:\n\t".$d->extends."\n");
						print("\n");
					}
					print("description:\n\t".str_replace("\n","\n\t",$d->description));
					print("\n");
		
					print("functions:\n");
					foreach($d->function as $f){
						print("\t".$f->name."\n");
					}				
				}
			}
		}else{
			$func = str_replace("_","-",strtolower($path));
			
			$result = array();
			$body = Http::body("http://jp2.php.net/manual/ja/function.".$func.".php");
			if(SimpleTag::setof($tag,$body,"body")){
				foreach($tag->getIn("div") as $div){
					if($div->param("id") == "layout_2"){
						foreach($div->getIn("div") as $di){
							if($di->param("id") == "content"){
								foreach($di->getIn("div") as $d){
									if($d->param("id") == "function.".$func){
										$result["verinfo"] = $d->f("div[0].p[0].value()");
										$result["refpurpose"] = $d->f("div[0].p[1].value()");
					
										$description = $d->f("div[1]");
										$description->f("h3.save()",null);
										$result["desc"] = trim(strip_tags($description->getValue()));
										$result["desc"] = preg_replace("/\n[\s]*\n/","\n\n",$result["desc"]);
										$result["desc"] = str_replace(array("\n\n","\n","__ENTER__"),array("__ENTER__","","\n"),$result["desc"]);
										
										print("verinfo:\n\t".$result["verinfo"]."\n");
										print("\n");
										print("refpurpose:\n\t".$result["refpurpose"]."\n");
										print("\n");
										print("description:\n\t".str_replace("\n","\n\t",$result["desc"]));
										print("\n");
									}
								}
							}
						}
					}
				}
			}
			if(empty($result)) print("undefined\n");
		}
	}
	
	function _create($dbname){
		print((($this->projectModel->createTable($dbname)) ? "creata table ".$dbname : "failure")."\n");
	}
	
	function _generate(){
		$request = new Request();
		$request->setVariable("rhacopath",$this->rhacopath);
		print((($this->projectModel->generate($request)) ? "setting completion" : "failure")."\n");
	}
	
	function _init(){
		print((($projectModel->appInit()) ? "initializing of project is executed" : "failure")."\n");
	}
	
	function _install_dir(){
		foreach(FeedParser::getItem($projectModel->getInstallServer()) as $item){
			print($item->getTitle()."\n");
		}
	}
	
	function _install($server,$name){
		$bool = false;
		foreach(FeedParser::getItem($projectModel->getInstallServer()) as $item){
			if(strtolower($item->getTitle()) == strtolower($server)){
				$items = FeedParser::getItem($item->getLink());

				foreach($items as $i){
					if(strtolower($i->getTitle()) == strtolower($name)){
						list($category,$installpath) = InstallViews::installPath($url,$servers[$this->getVariable("server")]);
						if(FileUtil::unpack(Http::body($i->getLink()),$installpath)){
							print("installed\n");
							$bool = true;
						}
						break;
					}
				}
				break;
			}
		}
		if(!$bool) print("fail\n");
	}
	function _install_list($name){
		$query = "";
		if(preg_match("/^([\d\w_-]+)[\s]+\-s[\s]+(.+)$/",$name,$match)){
			list($null,$name,$query) = $match;
		}
		$bool = false;
		foreach(FeedParser::getItem($projectModel->getInstallServer()) as $item){
			if(strtolower($item->getTitle()) == strtolower($name)){
				$items = FeedParser::getItem($item->getLink()."?q=".$query);
				$items = ObjectUtil::sort($items,"getTitle()");

				foreach($items as $i){
					print("cmd. install ".$item->getTitle()." ".$i->getTitle()."\n");
				}
				$bool = true;
				break;
			}
		}	
		if(!$bool) print("not found\n");
	}
}
?>