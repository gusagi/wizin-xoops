<?php
Rhaco::import("io.FileUtil");
Rhaco::import("lang.Variable");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("lang.Env");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("util.model.LogRecord");
/**
 * #ignore
 * ロギングクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class Logger{
	/**
	 * 出力する
	 * @static 
	 */
	function flush(){
		$logpath = (Rhaco::constant("LOG_FILE_PATH") == null) ? Rhaco::path("work") : Rhaco::constant("LOG_FILE_PATH");
		$dipshtml = (Rhaco::constant("LOG_DISP_HTML") == null) ? false : Rhaco::constant("LOG_DISP_HTML");

		if(Logger::isLevel() >= 2){
			foreach(ExceptionTrigger::get() as $name => $exception){
				Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(2,$exception->getDetail()));
			}
		}
		if(Logger::isLevel() >= 4){
			if((version_compare(phpversion(),strval("5.2.1")) >= 0)) Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(4,"use memory: ".number_format(memory_get_usage())."byte / ".number_format(memory_get_peak_usage())."byte"));
			Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(4,sprintf("------- end logger ( %f sec ) ------- ",microtime(true) - (float)Rhaco::getVariable("RHACO_CORE_LOGGER_START_TIME"))));
		}
		if(Rhaco::isVariable("RHACO_CORE_LOGGER")){
			$disp = Logger::level(Rhaco::constant("LOG_DISP_LEVEL"));
			$file = Logger::level(Rhaco::constant("LOG_FILE_LEVEL"));
			$levels = Logger::getLevelValues();
			$colors = Logger::getLevelColors();

			foreach(Rhaco::getVariable("RHACO_CORE_LOGGER") as $log){
				$value = sprintf("[%s %s]:[%s:%d] %s\n",$levels[$log->getLevel()],$log->getTime(),$log->getFile(),$log->getLine(),$log->getValue());

				if($disp >= $log->getLevel() && Rhaco::getVariable("RHACO_CORE_LOGGER_DISP_OFF",false) !== true){
					print(($dipshtml) ? "<span style='color:".$colors[$log->getLevel()]."'>".nl2br(htmlspecialchars($value,ENT_QUOTES))."</span>" : $value);
				}				
				if($file >= $log->getLevel()){
					FileUtil::append(sprintf("%s/%s.log",FileUtil::path($logpath),date("Ymd")),$value);
				}
				if(Rhaco::isVariable("RHACO_CORE_LOGGER_PUBLISHER")){
					foreach(Rhaco::getVariable("RHACO_CORE_LOGGER_PUBLISHER") as $key => $publish){
						if($publish[1][$log->getLevel()]) call_user_func_array(array(&$publish[0],$levels[$log->getLevel()]),array($log));
					}
				}
			}
			if(Rhaco::isVariable("RHACO_CORE_LOGGER_PUBLISHER")){
				foreach(Rhaco::getVariable("RHACO_CORE_LOGGER_PUBLISHER") as $publish){
					if($publish[2]) call_user_func_array(array(&$publish[0],"flush"),array(Rhaco::getVariable("RHACO_CORE_LOGGER")));
				}
			}
		}
		Rhaco::clearVariable("RHACO_CORE_LOGGER");
	}
	
	/**
	 * @static 
	 * @return unknown
	 */
	function isLevel(){
		if(Rhaco::isVariable("RHACO_CORE_LOGGER_LEVEL")) return Rhaco::getVariable("RHACO_CORE_LOGGER_LEVEL");
		$level = array(Logger::level(Rhaco::constant("LOG_DISP_LEVEL")),Logger::level(Rhaco::constant("LOG_FILE_LEVEL")),Logger::level(Rhaco::constant("LOG_SYS_LEVEL")));
		rsort($level);
		Rhaco::setVariable("RHACO_CORE_LOGGER_LEVEL",$level[0]);
		return $level[0];
	}
	
	/**
	 * @static 
	 * @param unknown_type $level_str
	 * @return unknown
	 */
	function level($level_str){
		$level = Logger::getLevelIds();
		return ($level_str == null) ? 0 : isset($level[$level_str]) ? $level[$level_str] : 0;	
	}
	
	/**
	 * @static 
	 * @return unknown
	 */
	function getLevelValues(){
		return array("none","error","warning","info","debug","deep_debug");
	}
	
	/**
	 * @static 
	 * @return unknown
	 */
	function getLevelIds(){
		return array("none"=>0,"error"=>1,"warning"=>2,"info"=>3,"debug"=>4,"deep_debug"=>5);
	}
	function getLevelColors(){
		return array("#000000","#ff8888","#8888ff","#cc8888","#888888","#cccccc");
	}
	
	/**
	 * パブリッシュフィルターを定義する
	 * @static 
	 * @param object,object,........
	 */
	function setPublisher(){
		$args = func_get_args();
		$levels = Logger::getLevelValues();

		foreach($args as $arg){
			foreach(ArrayUtil::arrays($arg) as $obj){
				if(is_string($obj)) $obj = Rhaco::obj($obj);
				if(is_object($obj)){
					$class = get_class($obj);
					$methods = array();
					
					foreach($levels as $key => $level){
						$methods[] = method_exists($obj,$level);
					}
					Rhaco::addVariable("RHACO_CORE_LOGGER_PUBLISHER",array($obj,$methods,method_exists($obj,"publish")),$class);
				}
			}
		}
	}
	
	/**
	 * 一時的に無効にされた標準出力へのログ出力を有効にする
	 * ログのモードに依存する
	 * 
	 * @static 
	 */
	function enableDisplay(){
		Logger::deep_debug("log display on");
		Rhaco::setVariable("RHACO_CORE_LOGGER_DISP_OFF",false);
	}

	/**
	 * 標準出力へのログ出力を一時的に無効にする
	 * 
	 * @static 
	 */
	function disableDisplay(){
		Logger::deep_debug("log display off");
		Rhaco::setVariable("RHACO_CORE_LOGGER_DISP_OFF",true);
	}
	
	/**
	 * errorを生成
	 * @static 
	 * @param string $value
	 */
	function error(){
		if(Logger::isLevel() >= 1){
			foreach(func_get_args() as $value){
				Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(1,$value));
				if(Rhaco::constant("LOG_FLUSH_IMMEDIATELY") === true) Logger::flush();
			}
		}
	}
	
	/**
	 * warningを生成
	 * @static 
	 * @param string $value
	 */
	function warning($value){
		if(Logger::isLevel() >= 2){
			foreach(func_get_args() as $value){
				Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(2,$value));
				if(Rhaco::constant("LOG_FLUSH_IMMEDIATELY") === true) Logger::flush();
			}
		}
	}

	/**
	 * infoを生成
	 * @static 
	 * @param string $value
	 */
	function info($value){
		if(Logger::isLevel() >= 3){
			foreach(func_get_args() as $value){
				Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(3,$value));
				if(Rhaco::constant("LOG_FLUSH_IMMEDIATELY") === true) Logger::flush();
			}
		}
	}
	
	/**
	 * debugを生成
	 * @static 
	 * @param string $value
	 */
	function debug($value){
		if(Logger::isLevel() >= 4){
			foreach(func_get_args() as $value){
				Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(4,$value));
				if(Rhaco::constant("LOG_FLUSH_IMMEDIATELY") === true) Logger::flush();
			}
		}
	}
	
	/**
	 * deep debugを生成
	 *
	 * @param string $value
	 */
	function deep_debug($value){
		if(Logger::isLevel() >= 5){
			foreach(func_get_args() as $value){
				Rhaco::addVariable("RHACO_CORE_LOGGER",new LogRecord(5,$value));
				if(Rhaco::constant("LOG_FLUSH_IMMEDIATELY") === true) Logger::flush();
			}
		}
	}
	
	/**
	 * deprecatedを生成
	 * @static 
	 * @param string $value
	 */
	function deprecated($value=""){
		list($debug) = debug_backtrace();
		Logger::warning(sprintf("deprecated [%s:%s] %s",$debug["file"],$debug["line"],$value));
	}
	
	/**
	 * print_rで出力する
	 * @static 
	 * @param unknown_type $value
	 */
	function p(){
		list($debug_backtrace) = debug_backtrace();
		$args = func_get_args();
		print_r(array_merge(array($debug_backtrace["file"].":".$debug_backtrace["line"]),$args));
	}
	
	/**
	 * var_exportで出力する
	 * @static 
	 * @param unknown_type $value
	 */
	function v(){
		list($debug_backtrace) = debug_backtrace();
		$args = func_get_args();
		var_export(array_merge(array($debug_backtrace["file"].":".$debug_backtrace["line"]),$args));
	}
	
	/**
	 * var_dumpで出力する
	 * @static 
	 * @param unknown_type $value
	 */
	function d(){
		list($debug_backtrace) = debug_backtrace();
		$args = func_get_args();
		var_dump(array_merge(array($debug_backtrace["file"].":".$debug_backtrace["line"]),$args));
	}
	
	/**
	 * 呼び出し元を出力／生成
	 * @static 
	 * @param boolean $flush
	 */
	function called($flush=false){
		$debug = debug_backtrace();
		$call = (isset($debug[1])) ? $debug[1] : array("file"=>"","line"=>"","function"=>"");
		$invited = (isset($debug[2])) ? $debug[2] : array("file"=>"","line"=>"","function"=>"");
		$msg = Message::_("[{1}:{2} {3}] called [{4}:{5} {6}]",$call["file"],$call["line"],$call["function"],$invited["file"],$invited["line"],$invited["function"]);
		if($flush){
			print($msg."\n");
		}else{
			Logger::debug($msg);
		}
	}
	
	/**
	 * ボーダーを出力／生成
	 * @static 
	 * @param boolean $flush
	 * @param int $size
	 */
	function line($flush=false,$size=100){
		if($flush){
			print(str_repeat("-",$size)."\n");
		}else{
			Logger::debug(str_repeat("-",$size));
		}
	}
}
?>
<?php
if(!Rhaco::constant("RHACO_LOGGER_INITIALIZED")){
	Rhaco::register_shutdown(array("Logger","flush"));
	Rhaco::constant("RHACO_LOGGER_INITIALIZED",true);
	Rhaco::setVariable("RHACO_CORE_LOGGER_START_TIME", microtime(true));
}
?>