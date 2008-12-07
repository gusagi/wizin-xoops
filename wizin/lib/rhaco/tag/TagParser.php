<?php
Rhaco::import("resources.Message");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("network.Url");
Rhaco::import("network.http.Http");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("io.FileUtil");
Rhaco::import("io.Cache");
Rhaco::import("io.Snapshot");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("util.Logger");
Rhaco::import("lang.Env");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.Variable");
/**
 * テンプレートをフォーマットする
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class TagParser{
	var $variables = array();
	var $statics = array();
	var $filename = "";
	var $tmpurl = "";
	
	var $url = "";
	var $path = "";
	var $encodeType	= "UTF-8";

	var $filters = array();

	function TagParser($template=null,$path=null,$url=null){
		if($template != null) $this->filename = $template;
		$this->url = empty($url) ? Rhaco::templateurl() : $url;
		$this->path = empty($path) ? Rhaco::templatepath() : $path;
	}

	/**
	 * TagParser用のキャッシュ定義を行う
	 *
	 * @param unknown_type $cachePath
	 * @param unknown_type $cacheTime
	 */
	function setCache($cachePath="",$cacheTime=86400){
		Rhaco::constant("TEMPLATE_CACHE",true);
		Rhaco::constant("TEMPLATE_CACHE_TIME",$cacheTime);
		Rhaco::constant("CACHE_PATH",(empty($cachePath) ? Rhaco::path("work") : $cachePath));
	}		

	/**
	 * エンコードタイプを設定する
	 *
	 * @param string $encodeType
	 */
	function setEncodeType($encodeType){
		if(!empty($encodeType)) $this->encodeType = $encodeType;
	}
	/**
	 * 出力する
	 * @param string $templateFileName テンプレートファイルパス(resources/templates)からの相対
	 * @param string $remotePath 相対パス変換用のルートパス
	 * @param array $variables テンプレートで利用する変数(hash)
	 */
	function write($templateFileName="",$variables=array(),$remotePath=""){
		/*** unit("tag.TagParserTest"); */
		print($this->read($templateFileName,$variables,$remotePath));
	}
	
	/**
	 * テンプレートをフォーマットし取得する
	 * @param string $templateFileName テンプレートファイルパス(resources/templates)からの相対
	 * @param string $remotePath 相対パス変換用のルートパス
	 * @param array $variables テンプレートで利用する変数(hash)
	 * @return string
	 */
	function read($filename="",$variables=array(),$remotePath=""){
		/*** unit("tag.TagParserTest"); */
		$this->filename	= empty($filename) ? $this->filename : $filename;
		if(empty($this->filename)) return ExceptionTrigger::raise(new NotFoundException("template"));
		$filename = Url::parseAbsolute($this->path,$this->filename);
		$variables = $this->_setSpecialVariables(array_merge(ArrayUtil::arrays($variables),ArrayUtil::arrays($this->variables)));
		$cacheurl = $this->_getCacheUrl($filename);
		$this->tmpurl = empty($remotePath) ? $this->url : $remotePath;
		$rhaco_tag_parse_src = null;

		if(!Variable::bool(Rhaco::constant("NOT_MAKE_CACHE")) && Variable::bool(Rhaco::constant("TEMPLATE_CACHE")) && 
			!Cache::isExpiry($cacheurl,Rhaco::constant("TEMPLATE_CACHE_TIME",86400)) && (FileUtil::time($filename) < Cache::time($cacheurl))
		){
			$rhaco_tag_parse_src = Cache::execute($cacheurl,$variables);
		}else{
			$rhaco_tag_parser_read_src = $this->_parse($this->_getTemplateSource($filename));
			if(Variable::bool(Rhaco::constant("TEMPLATE_CACHE")) && !Variable::bool(Rhaco::constant("NOT_MAKE_CACHE"))){
				Cache::set($cacheurl,$rhaco_tag_parser_read_src);
				$rhaco_tag_parse_src = Cache::execute($cacheurl,$variables);
			}
			if($rhaco_tag_parse_src === null){
				$rhaco_snapshot = new Snapshot();
				Rhaco::execute($rhaco_tag_parser_read_src,$variables);
				$rhaco_tag_parse_src = $rhaco_snapshot->get();
			}
			unset($rhaco_snapshot,$rhaco_tag_parser_read_src);
		}
		unset($filename,$variables,$cacheurl);
		return $this->_syntaxCheck(StringUtil::encode($this->_callFilter("publish",$this->_call($rhaco_tag_parse_src,"_doRead")),$this->encodeType));
	}
	function _syntaxCheck($src){
		return $src;
	}
	function parse($src,$variables=array(),$remotePath=""){
		/*** unit("tag.TagParserTest"); */
		$this->tmpurl = empty($remotePath) ? $this->url : $remotePath;
		$rhaco_snapshot = new Snapshot();
		Rhaco::execute($this->_parse($src),$this->_setSpecialVariables(array_merge(ArrayUtil::arrays($variables),ArrayUtil::arrays($this->variables))));
		$rhaco_tag_parse_src = $rhaco_snapshot->get();
		unset($rhaco_snapshot);
		return $this->_callFilter("publish",$this->_call($rhaco_tag_parse_src,"_doRead"));
	}

	/**
	 * 変数をセットする
	 * @param array/string arrayOrKey
	 * @param unknown_type $value
	 */
	function setVariable($name,$value=null){
		if(is_object($name) && Variable::istype("Request",$name)) $name = $name->getVariable();
		if(!is_array($name)) $name = array($name=>$value);
		$this->variables = array_merge(ArrayUtil::arrays($this->variables),$name);
	}
	
	/**
	 * staticで利用する変数を宣言する
	 *
	 * @param string $$varname
	 * @param string $$classname
	 */
	function setStatics($varname,$classname){
		$this->statics[$varname] = $classname;
	}
	
	/**
	 * テンプレートをセット
	 * @param string value テンプレートファイルパス(resources/templates)からの相対
	 */
	function setTemplate($filename){
		$this->filename = $filename;
	}

	function setUrl($url){
		$this->url = $url;
	}
	function setPath($path){
		$this->path = $path;
	}
	function setFilter(){
		$args = func_get_args();
		foreach(ArrayUtil::arrays($args) as $arg){
			$this->filters = array_merge($this->filters,
										ObjectUtil::loadObjects(ArrayUtil::arrays($arg),array("init","before","after","publish"),false)
								);
		}
	}
	function _callFilter($name,$src){
		return ObjectUtil::calls($this->filters,$name,array($src,$this),0);
	}	
	function _parse($src){
		$src = StringUtil::toULD($src);
		$src = preg_replace("/([\w])\->/","\\1__PHP_ARROW__",$src);
		$src = $this->_callFilter("init",$src);
		$src = $this->_call($src,"_cs");
		$src = $this->_callFilter("before",$src);
		$src = $this->_call($src,"_exec");
		$src = $this->_callFilter("after",$src);
		$src = str_replace("__PHP_ARROW__","->",$src);		
		$src = $this->_parserPluralMessage($src);
		$src = $this->_parsePrintVariable($src);
		$src = $this->_parseMessage($src);
		$src = $this->_replaceSpecialVariables($src);
		$src = $this->_escapeSource($this->_parseUrl($src));
		return $src;
	}
	function _parseUrl($src){
		$this->tmpurl = Url::parseAbsolute($this->url,Url::parseRelative($this->path,$this->filename));
		$php = array($this->_pte(),$this->_pts(),"->");
		$str = array("PHP_TAG_END","PHP_TAG_START","PHP_ARROW");
		return str_replace($str,$php,Url::parse(str_replace($php,$str,$src),$this->tmpurl));
	}
	function _parserPluralMessage($src){
		if(preg_match_all("/.{0,2}(_p\(("."([\"\']).+?\\3".","."([\"\']).+?\\4".","."(([\d]+)|(\{\\\$.+\}))".")\))/",$src,$match)){
			$stringList = array();

			foreach($match[0] as $key => $value){
				$chkstring = substr($value,0,2);
				$match[2][$key] = str_replace($match[7][$key],$this->_parsePlainVariable($match[7][$key]),$match[2][$key]);
				if($chkstring != "::" && $chkstring != "->"){
					$stringList[$match[1][$key]] = $this->_pts().sprintf("print(Message::_p(%s));",$match[2][$key]).$this->_pte();
				}
			}
			foreach($stringList as $baseString => $string){
				$src = str_replace($baseString,$string,$src);
			}
			unset($stringList,$match);
		}
		return $src;
	}
	function _parseMessage($src){
		if(preg_match_all("/.{0,2}(_\((([\"\']).+?\\3)\))/",$src,$match)){
			$stringList = array();

			foreach($match[0] as $key => $value){
				$chkstring = substr($value,0,2);
				if($chkstring != "::" && $chkstring != "->"){
					$stringList[$match[1][$key]] = $this->_pts().sprintf("print(Message::_(%s));",$match[2][$key]).$this->_pte();
				}
			}
			foreach($stringList as $baseString => $string){
				$src = str_replace($baseString,$string,$src);
			}
			unset($stringList);
		}
		return $src;
	}
	function _escapeSource($src){
		if(preg_match_all("/<\?(?!php[\s\n])[\w]+ .*?\?>/s",$src,$null)){
			foreach($null[0] as $value){
				$src = str_replace($value,"__PHP_TAG_ESCAPE_START__".substr($value,2,-2)."__PHP_TAG_ESCAPE_END__",$src);
			}
		}
		return $src;
	}
	function _doRead0001_UnescapeSource($src){
		return str_replace(array("__PHP_TAG_ESCAPE_START__","__PHP_TAG_ESCAPE_END__"),array("<?","?>"),$src);
	}
	function _pts(){
		return "<?php ";
	}
	function _pte(){
		return " ?>";
	}
	function _call($src,$runmethod){
		$list = array();
		foreach(get_class_methods($this) as $methodName){
			if(preg_match("/^".$runmethod.".+/i",$methodName)) $list[] = $methodName;
		}
		sort($list);
		foreach($list as $methodName) $src = $this->$methodName($src);
		return $src;
	}
	function _parsePrintVariable($src){
		foreach($this->_matchVariable($src) as $variable){
			$name = $this->_parsePlainVariable($variable);
			$check = $this->_variableCheckValue($name);
			$value = $check[0].$this->_pts()."print(".$name.");".$this->_pte().$check[1];
			$src = str_replace(array($variable."\n",$variable),array($value."\n\n",$value),$src);
		}
		return $src;
	}
	function _variableCheckValue($name){
		$checks = "";
		$checke = "";

		if(!empty($name) && $name[0] == "\$" && preg_match_all("/\\$[\\$\w][\w]*/",$name,$match)){
			foreach($match[0] as $arg){
				$checks .= " && isset(".$arg.")";
			}
			$checks	= ($checks != "") ? ($this->_pts()."if(".substr($checks,3)."):".$this->_pte()) : "";
			$checke	= ($checks != "") ? ($this->_pts()."endif;".$this->_pte()) : "";
		}
		return array($checks,$checke);
	}
	function _parsePlainVariable($src){
		while(true){
			$array = $this->_matchVariable($src);
			
			if(sizeof($array) <= 0)	break;
			foreach($array as $variable){
				$variable_tmp = $variable;
				
				if(preg_match_all("/([\"\'])([^\\1]+)\\1/",$variable,$match)){
					foreach($match[2] as $value){
						$variable_tmp = str_replace($value,str_replace(".","__PERIOD__",$value),$variable_tmp);
					}
				}
				$src = str_replace($variable,str_replace(".","->",substr($variable_tmp,1,-1)),$src);
				unset($variable_tmp);
			}
		}
		return str_replace("[]","",str_replace("__PERIOD__",".",$src));
	}
	function _matchVariable($src){
		$value			= "";
		$position		= 0;
		$length			= 0;
		$variableHash	= array();
		$variables		= array();

		while(preg_match("/({(\\$[\$\w][^\t]*)})/s",$src,$variables,PREG_OFFSET_CAPTURE)){
			$value		= $variables[1][0];
			$position	= $variables[1][1];

			if($value == "") break;
			if(substr_count($value,"}") > 1){
				for($i=0,$start=0,$end=0;$i<strlen($value);$i++){			
					if($value[$i] == "{"){
						$start++;
					}else if($value[$i] == "}"){
						if($start == ++$end){
							$value = substr($value,0,$i+1);
							break;
						}
					}
				}
			}
			$length	= strlen($value);			
			$src = substr($src,$position + $length);
			$variableHash[sprintf("%03d_%s",$length,$value)] = $value;
			unset($variables);
		}
		krsort($variableHash);
		return $variableHash;
	}
	function _variableQuote($src){
		return preg_replace("/[^\w]/","",$src);
	}
	function _getVariableString($src){
		return (substr($src,0,1) == "$") ? $src : "\$".$src;
	}
	function _setSpecialVariables($variables){
		return array_merge($variables,array("variables"=>Variable::copy($variables)));
	}
	function _replaceSpecialVariables($src){
		foreach($this->statics as $key => $value){
			$src = $this->_toStaticVariable($value,$key,$src);
		}
		$src = $this->_toStaticVariable("Rhaco","rhaco",$src);
		$src = $this->_toStaticVariable("Env","env",$src);

		return $src;
	}
	function _toStaticVariable($class,$var,$src){
		return str_replace(array("isset(\$".$var.")","\$".$var."->"),array("true",$class."::"),$src);
	}
	function _getTemplateSource($templateFileName){
		$src = preg_match("/[\w]+:\/\/[\w]+/",$templateFileName) ? Http::body($templateFileName) : FileUtil::read($templateFileName);
		return StringUtil::encode($src);
	}
	function _getCacheUrl($filename){
		return FileUtil::path(Rhaco::constant("CACHE_PATH",Rhaco::path("work/cache/")),str_replace(":","/",$filename));
	}
}
?>