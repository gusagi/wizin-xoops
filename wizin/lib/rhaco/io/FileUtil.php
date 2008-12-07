<?php
Rhaco::import("network.Url");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
Rhaco::import("lang.Env");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.PermissionException");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("resources.Message");
Rhaco::import("io.model.File");
/**
 * ファイル操作を行うクラス
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class FileUtil{
	var $resource		= array();
	var $source			= array();
	var $regClose		= true;
	var $transaction	= false;

	function FileUtil($transaction=false){
		$this->transaction = $transaction;
	}
	/**
	 * リソースをクローズする
	 *
	 * @param string $filename
	 */
	function close($filename=""){
		/*** unit("io.FileUtilTest"); */
		$list = array();			
		
		if(empty($filename)){
			$list = $this->resource;
		}else if(isset($this->resource[$filename])){
			$list[$filename] = $this->resource[$filename];
		}
		foreach($list as $name => $fp){
			if(is_resource($this->resource[$name]) && (Env::w() || flock($this->resource[$name],LOCK_UN))){
				$this->commit($name);
				fclose($this->resource[$name]);
			}
			unset($this->resource[$name]);
			unset($this->source[$name]);
		}
	}

	/**
	 * ファイルから取得する
	 * トランザクションを利用する事ができる
	 *
	 * @param string $filename
	 * @param string $enc
	 * @return string
	 */
	function fgets($filename,$enc=""){
		/*** unit("io.FileUtilTest"); */
		if(FileUtil::isFile($filename) && $this->_open($filename) && $this->_seekhome($filename)){
			if($this->transaction){
				return StringUtil::encode($this->source[$filename],$enc);
			}else{
				$this->_seekhome($filename);
				$buffer = "";
				while(!feof($this->resource[$filename])){
					$buffer .= fgets($this->resource[$filename],4096);
				}
			}
			return empty($enc) ? $buffer : StringUtil::encode($buffer,$enc);
		}
		return ExceptionTrigger::raise(new NotFoundException($filename));
	}

	/**
	 * ファイルに追記する
	 * トランザクションを利用する事ができる
	 *
	 * @param string $filename
	 * @param string $src
	 * @param string $enc
	 * @return boolean
	 */
	function fputs($filename,$src,$enc=""){
		/*** unit("io.FileUtilTest"); */
		if($this->_open($filename) && $this->_seekend($filename)){
			if($this->transaction){
				$this->source[$filename] = $this->source[$filename].(empty($enc) ? $src : StringUtil::encode($src,$enc));
				return true;
			}else{
				if(false !== fwrite($this->resource[$filename],(empty($enc) ? $src : StringUtil::encode($src,$enc)))) return true;
				ExceptionTrigger::raise(new PermissionException($filename));
			}
		}
		return false;
	}
	
	/**
	 * ファイルを上書きする
	 * トランザクションが利用できる
	 *
	 * @param string $filename
	 * @param string $src
	 * @param string $enc
	 * @return boolean
	 */
	function fwrite($filename,$src,$enc=""){
		/*** unit("io.FileUtilTest"); */
		if($this->_open($filename) && $this->_seekhome($filename)){
			if($this->transaction){
				$this->source[$filename] = (empty($enc) ? $src : StringUtil::encode($src,$enc));
				return true;
			}else{
				if(ftruncate($this->resource[$filename],0) && false !== fwrite($this->resource[$filename],(empty($enc) ? $src : StringUtil::encode($src,$enc)))) return true;
				ExceptionTrigger::raise(new PermissionException($filename));
			}
		}
		return false;
	}

	/**
	 * トランザクションを利用するか
	 *
	 * @param boolean $bool
	 */
	function setTransaction($bool){
		/*** unit("io.FileUtilTest"); */
		$this->transaction = Variable::bool($bool);
	}

	/**
	 * トランザクションをコミットする
	 *
	 * @param string $filename
	 * @return boolean
	 */
	function commit($filename){
		/*** unit("io.FileUtilTest"); */
		if($this->transaction){
			if(isset($this->resource[$filename]) && is_resource($this->resource[$filename]) && $this->_seekhome($filename) && ftruncate($this->resource[$filename],0)){
				if(false === fwrite($this->resource[$filename],$this->source[$filename])) return false;
			}
		}
		return true;
	}
	
	/**
	 * トランザクションをロールバックする
	 *
	 * @param string $filename
	 * @return boolean
	 */
	function rollback($filename){
		/*** unit("io.FileUtilTest"); */
		if($this->transaction) $this->_setSource($filename);
	}
	function _open($filename){
		if(!isset($this->resource[$filename])) $this->resource[$filename] = false;
		if(!is_resource($this->resource[$filename])){
			if($this->mkdir(dirname($filename))){
				$this->resource[$filename] = (!FileUtil::exist($filename) || is_writable($filename)) ? @fopen($filename,"ab+") : @fopen($filename,"r");

				if(!is_resource($this->resource[$filename])){
					unset($this->resource[$filename]);
					return ExceptionTrigger::raise(new PermissionException($filename));
				}
				if($this->transaction) $this->_setSource($filename);
				// Windows does not lock 
				if(Env::w() || flock($this->resource[$filename],LOCK_SH)){
					if(!$this->regClose){
						Rhaco::register_shutdown(array($this,'close'));
						$this->regClose = true;
					}
					return true;
				}
			}
			return ExceptionTrigger::raise(new PermissionException($filename));
		}
		return true;
	}
	function _setSource($filename){
		if($this->_seekhome($filename)){
			$this->source[$filename] = "";
			while(!feof($this->resource[$filename])){
				$this->source[$filename] .= fgets($this->resource[$filename],4096);
			}
		}
	}
	function _seekend($filename){
		return (!preg_match("/\:\/\//",$filename) && isset($this->resource[$filename]) && 
				is_resource($this->resource[$filename]) && fseek($this->resource[$filename],0,SEEK_END) >= 0);
	}
	function _seekhome($filename){
		return (isset($this->resource[$filename]) && is_resource($this->resource[$filename]) &&
					!preg_match("/\:\/\//",$filename) && fseek($this->resource[$filename],0,SEEK_SET) >= 0);
	}
	function _isResource($filename){
		return (isset($this->resource[$filename]) && is_resource($this->resource[$filename]));
	}


	
	/**
	 * ファイルから読み込む
	 *
	 * @static
	 * @param string $filename
	 * @param string $enc
	 * @return string
	 */
	function read($filename,$enc=""){
		/*** unit("io.FileUtilTest"); */
		if(is_array($filename)){
			foreach($filename as $f){
				$buffer .= FileUtil::read($f,$enc);
			}
			return $buffer;
		}
		if(!is_readable($filename) || !is_file($filename)){
			ExceptionTrigger::raise(new PermissionException($filename));
			return null;
		} 
		return (!empty($enc)) ? StringUtil::encode(file_get_contents($filename),$enc) : file_get_contents($filename);
	}
	
	/**
	 * ファイルに書き込む
	 * 
	 * @static
	 * @param string $filename
	 * @param string $src
	 * @param string $enc
	 * @return string
	 */
	function write($filename,$src="",$enc=""){
		/*** unit("io.FileUtilTest"); */
		$io = new FileUtil();
		if($io->fwrite($filename,$src,$enc)){
			$io->close($filename);
			unset($io);
			return true;
		}
		unset($io);
		return ExceptionTrigger::raise(new PermissionException($filename));
	}
	
	/**
	 * ファイルに追記する
	 * 
	 * @static
	 * @param string $filename
	 * @param string $src
	 * @param string $enc
	 * @return string
	 */
	function append($filename,$src="",$enc=""){
		/*** unit("io.FileUtilTest"); */
		$io = new FileUtil();
		if($io->fputs($filename,$src,$enc)){
			$io->close($filename);
			unset($io);
			return true;
		}
		unset($io);
		return false;
	}
	/**
	 * ファイルパスを生成する
	 *
	 * @static 
	 * @param string $base
	 * @param string $path
	 * @return string
	 */
	function path($base,$path=""){
		/***
		 * eq("/abc/def/hig.php",FileUtil::path("/abc/def","hig.php"));
		 * eq("/xyz/abc/hig.php",FileUtil::path("/xyz/","/abc/hig.php"));
		 */
		if(!empty($path)){
			$path = FileUtil::parseFilename($path);
			if(preg_match("/^[\/]/",$path,$null)){
				$path = substr($path,1);
			}
		}
		return Url::parseAbsolute(FileUtil::parseFilename($base),FileUtil::parseFilename($path));
	}
	
	/**
	 * ファイル名をそれっぽくする
	 *
	 * @static 
	 * @param string $filename
	 * @return string
	 */
	function parseFilename($filename){
		/***
		 * eq("/Users/kaz/Sites/rhacotest/test/io/FileUtilTest.php",FileUtil::parseFilename("/Users/kaz/Sites/rhacotest/test/io/FileUtilTest.php"));
		 * eq("/Users/kaz/Sites/rhacotest/test/io",FileUtil::parseFilename("/Users/kaz/Sites/rhacotest/test/io"));
		 * eq("/Users/kaz/Sites/rhacotest/test/io",FileUtil::parseFilename("/Users/kaz/Sites/rhacotest/test/io/"));
		 * eq("/Users/kaz/Sites/rhacotest/test/io",FileUtil::parseFilename("\\Users\\kaz\\Sites\\rhacotest\\test\\io"));
		 * eq("C:/Users/kaz/Sites/rhacotest/test/io",FileUtil::parseFilename("C:\\Users\\kaz\\Sites\\rhacotest\\test\\io"));
		 */
		$filename = preg_replace("/[\/]+/","/",str_replace("\\","/",trim($filename)));		
		return (substr($filename,-1) == "/") ? substr($filename,0,-1) : $filename;
	}
	
	/**
	 * ファイル、またはフォルダが存在しているか
	 * 
	 * @static 
	 * @param $filename
	 * @return boolean
	 */
	function exist($filename){
		/*** unit("io.FileUtilTest"); */
		return (is_readable($filename) && (is_file($filename) || is_dir($filename) || is_link($filename)));
	}
	/**
	 * ファイルが存在しているか
	 * 
	 * @static 
	 * @param $filename
	 * @return boolean
	 */
	function isFile($filename){
		return (is_readable($filename) && is_file($filename));
	}
	/**
	 * フォルダが存在しているか
	 * 
	 * @static 
	 * @param $filename
	 * @return boolean
	 */
	function isDir($filename){
		return (is_readable($filename) && is_dir($filename));
	}
	/**
	 * 指定された$directory内のファイル情報をio.model.Fileとして配列で取得
	 *
	 * @static 
	 * @param string $directory
	 * @param boolean $recursive 階層を潜って取得するか
	 * @return array(File)
	 */
	function ls($directory,$recursive=false){
		/*** unit("io.FileUtilTest"); */
		$dataFileList = array();
		$directory = FileUtil::parseFilename($directory);

		if(is_dir($directory)){
			if($handle = opendir($directory)){
				while($pointer = readdir($handle)){
					if($pointer != "." && $pointer != ".."){
						$source = sprintf("%s/%s",$directory,$pointer);
						if(is_file($source)){
							$dataFile = new File($source);
							$dataFileList[$dataFile->fullname] = $dataFile;
						}else{
							if($recursive) $dataFileList = array_merge($dataFileList,FileUtil::ls($source,$recursive));
						}
					}
				}
				closedir($handle);
			}
		}else{
			ExceptionTrigger::raise(new PermissionException($directory));
		}
		return $dataFileList;
	}
	/**
	 * フォルダ名の配列を取得
	 * 
	 * @static 
	 * @param string $directory
	 * @param boolean $recursive 階層を潜って取得するか
	 */
	function dirs($directory,$recursive=false,$fullpath=true){
		/*** unit("io.FileUtilTest"); */
		$list		= array();
		$directory	= FileUtil::parseFilename($directory);

		if(is_readable($directory) && is_dir($directory)){
			if($handle = opendir($directory)){
				while($pointer = readdir($handle)){
					if(	$pointer != "." && $pointer != ".."){
						$source = sprintf("%s/%s",$directory,$pointer);
						
						if(is_dir($source)){
							$list[$source] = ($fullpath) ? $source : $pointer;
							if($recursive)	$list = array_merge($list,FileUtil::dirs($source,$recursive));
						}
					}
				}
				closedir($handle) ;
			}
		}
		usort($list,create_function('$a,$b','
									$at = strtolower($a);
									$bt = strtolower($b);
									return ($at == $bt) ? 0 : (($at < $bt) ? -1 : 1);'));
		return $list;
	}
	/**
	 * ファイルを検索する
	 * 
	 * @static 
	 * @param string $pattern 正規表現パターン
	 * @param string $directory
	 * @param boolean $recursive 階層を潜って取得するか
	 */
	function find($pattern,$directory,$recursive=false){
		/*** unit("io.FileUtilTest"); */
		$match		= array();
		$directory	= trim($directory);
		$fileList	= FileUtil::ls($directory,$recursive);

		if(!empty($fileList)){
			foreach($fileList as $dataFile){
				if(preg_match($pattern,$dataFile->getName())) $match[$dataFile->getFullname()] = $dataFile;
			}
		}
		return $match;
	}

	/**
	 * コピー
	 * $sourceがフォルダの場合はそれ以下もコピーする
	 * 
	 * @static 
	 * @param string $source
	 * @param string $dest
	 * @param int $parmission
	 * @return boolean
	 */
	function cp($source,$dest,$parmission=755){
		/*** unit("io.FileUtilTest"); */
		$source	= FileUtil::parseFilename($source);
		$dest	= FileUtil::parseFilename($dest);
		$dir	= (preg_match("/^(.+)\/[^\/]+$/",$dest,$tmp)) ? $tmp[1] : $dest;
		$bool	= true;

		if(!FileUtil::exist($source)) return false;
		if(FileUtil::mkdir($dir)){
			if(is_dir($source)){
				if($handle = opendir($source)){
					while($pointer = readdir($handle)){
						if(	$pointer != "." && $pointer != ".."){
							$srcname	= sprintf("%s/%s",$source,$pointer);
							$destname	= sprintf("%s/%s",$dest,$pointer);
							$bool		= FileUtil::cp($srcname,$destname);

							if(!$bool) break;
						}
					}
					closedir($handle);
				}
				return $bool;
			}else{
				$filename = (preg_match("/^.+(\/[^\/]+)$/",$source,$tmp)) ? $tmp[1] : "";
				$dest = (is_dir($dest))	? $dest.$filename : $dest;
				if(is_writable(dirname($dest))) copy($source,$dest);
				return FileUtil::exist($dest);
			}
		}
		return true;
	}

	/**
	 * 削除
	 * $sourceが削除の場合はそれ以下も全て削除します
	 * 
	 * @static
	 * @param string $source
	 * @return boolean
	 */
	function rm($source){
		/*** unit("io.FileUtilTest"); */
		if(Variable::istype("File",$source)) $source = $source->getFullname();
		$source	= FileUtil::parseFilename($source);

		if(!FileUtil::exist($source)) return true;
		if(is_writable($source)){
			if(is_dir($source)){
				if($handle = opendir($source)){
					$list = array();
					while($pointer = readdir($handle)){
						if($pointer != "." && $pointer != "..") $list[] = sprintf("%s/%s",$source,$pointer);
					}
					closedir($handle);
					foreach($list as $path){
						if(!FileUtil::rm($path)) return false;
					}
				}
				if(rmdir($source)){
					clearstatcache();
					return true;
				}
			}else if(is_file($source) && unlink($source)){
				clearstatcache();
				return true;				
			}
		}
		ExceptionTrigger::raise(new PermissionException($source));
		return false;
	}

	/**
	 * フォルダを作成する
	 *
	 * @static
	 * @param string $source
	 * @param int $permission
	 * @return boolean
	 */
	function mkdir($source,$permission=null){
		/*** unit("io.FileUtilTest"); */
		$source = FileUtil::parseFilename($source);
		if(!FileUtil::isDir($source)){
			$path = $source;
			$dirstack = array();
			while(!is_dir($path) && $path != DIRECTORY_SEPARATOR){
				array_unshift($dirstack,$path);
				$path = dirname($path);
			}
			while($path = array_shift($dirstack)){
				$bool = (empty($permission)) ? @mkdir($path) : @mkdir($path,Rhaco::phpexe(sprintf("return %04d;",$permission)));
				if($bool === false) return ExceptionTrigger::raise(new PermissionException($path));
			}
		}
		if(!empty($permission)) FileUtil::chmod($source,$permission);
		return true;
	}

	/**
	 * 移動
	 *
	 * @static
	 * @param string $source
	 * @param string $dest
	 * @return boolean
	 */
	function mv($source,$dest){
		/*** unit("io.FileUtilTest"); */
		$source		= FileUtil::parseFilename($source);
		$dest		= FileUtil::parseFilename($dest);
		return (FileUtil::exist($source) && FileUtil::mkdir(dirname($dest))) ? rename($source,$dest) : false;
	}

	/**
	 * 権限を変更する
	 *
	 * @static
	 * @param string $source
	 * @param int $permission
	 * @return boolean
	 */
	function chmod($source,$permission=755){
		if(FileUtil::exist($source) && Env::w()){
			return chmod($source,Rhaco::phpexe(sprintf("return %04d;",$permission)));
		}
		return true;
	}
	
	/**
	 * ファイルサイズを取得する
	 *
	 * @static
	 * @param string $filename
	 * @param string $format
	 * @return int
	 */
	function size($filename,$format="kb"){
		if(is_readable($filename) && is_file($filename)){
			switch(strtolower($format)){
				case "b":	return filesize($filename);
				case "mb":	return ceil((filesize($filename) / 1024) / 1024);
				case "gb":	return ceil(((filesize($filename) / 1024) / 1024) / 1024);
				case "tb":	return ceil((((filesize($filename) / 1024) / 1024) / 1024) / 1024);
				default:	return $size = ceil(filesize($filename) / 1024);
			}
		}
		return 0;		
	}
	
	/**
	 * フォルダの空きを取得する
	 *
	 * @static
	 * @param string $directory
	 * @param string $format
	 * @return int
	 */
	function free($directory,$format="kb"){
		if(is_readable($directory) && is_dir($directory)){
			switch(strtolower($format)){
				case "b":	return disk_free_space($directory);
				case "mb":	return ceil((disk_free_space($directory) / 1024) / 1024);
				case "gb":	return ceil(((disk_free_space($directory) / 1024) / 1024) / 1024);
				case "tb":	return ceil((((disk_free_space($directory) / 1024) / 1024) / 1024) / 1024);
				default:	return ceil(disk_free_space($directory) / 1024);
			}
		}
		return 0;
	}

	/**
	 * 更新時間を取得
	 * 
	 * @static 
	 * @param $filename
	 * @return int
	 */
	function time($filename){
		return (is_readable($filename) && is_file($filename)) ? filemtime($filename) : -1;
	}
	
	/**
	 * 複数のファイルから単一のソースを作成する
	 *
	 * @static 
	 * @param unknown_type $paths
	 * @return unknown
	 */
	function pack($paths,$replace=""){
		/***
		 * $path = FileUtil::path(Rhaco::rhacopath(),"/io/FileUtil.php");
		 * $paths = array("FileUtil.php"=>$path,"io/FileUtil.php"=>$path);
		 * $src = FileUtil::pack($paths);
		 * preg_match_all("/\[\[(.+)?\]\]/",$src,$match);
		 * eq(2,sizeof($match[1]));
		 */
		$io = new FileUtil();
		$result = "";
		$packs = array();

		foreach(ArrayUtil::arrays($paths) as $key => $value){
			if(preg_match("/^[\d]+$/",$key)){
				if(is_dir($value)){
					foreach(FileUtil::dirs($value,true) as $dir){
						$packs[substr($dir,strlen(empty($replace) ? $value : $replace))] = $dir;
					}
					foreach(FileUtil::ls($value,true) as $file){
						$packs[substr($file->fullname,strlen(empty($replace) ? $value : $replace))] = $file->fullname;
					}
				}else if(is_file($value)){
					$packs[substr($value,strlen($replace))] = $value;
				}
			}else{
				$packs[$key] = $value;
			}
		}
		foreach($packs as $name => $path){
			if(!FileUtil::exist($path)) Logger::error("pack fail [".$path."]");
			if(is_file($path)){
				$result .= "[[".$name."]]\n";
				$result .= chunk_split(base64_encode($io->read($path)),76,"\n");
				$result .= "\n\n";
			}else if(is_dir($path)){
				$result .= "[[[".$name."]]]\n";
				$result .= "\n\n";
			}
		}
		return $result;
	}

	/**
	 * packされたソースから展開する
	 * 
	 * @static
	 * @param unknown_type $src
	 * @param unknown_type $outputdir
	 */
	function unpack($src,$outputdir){
		$bool = true;
		$src = StringUtil::toULD($src);
		if(preg_match_all("/\[\[\[([^\[\]]+?)\]\]\]\n(.+?)\n\n\n/ms",$src,$match)){
			foreach($match[1] as $dirname){
				if(!FileUtil::mkdir(FileUtil::path($outputdir,$dirname))) $bool = false;
			}
		}
		if(preg_match_all("/\[\[([^\[\]]+?)\]\]\n(.+?)\n\n\n/ms",$src,$match)){
			foreach($match[1] as $key => $filename){
				$path = FileUtil::path($outputdir,$filename);
				if(!FileUtil::write($path,(base64_decode($match[2][$key])))) $bool = false;
			}
		}
		return $bool;
	}
	
	/**
	 * パブリックなファイルか
	 *
	 * @param unknown_type $path
	 * @return unknown
	 */
	function isPublic($path){
		return (strpos($path,"/.") === false && (basename($path) == "__init__.php" || strpos($path,"/_") === false));
	}
}
?>