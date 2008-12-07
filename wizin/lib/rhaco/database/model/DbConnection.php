<?php
Rhaco::import("lang.Variable");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("lang.ArrayUtil");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.IllegalArgumentException");
/**
 * データベース接続用クラス
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2005- rhaco project. All rights reserved.
 */
class DbConnection{
	var $name		= "";
	var $user		= "root";
	var $password	= "";
	var $type		= "database.controller.DbUtilMySQL";
	var $host		= "localhost";
	var $port		= "";
	var $encode		= "utf8";
	var $new		= true;
	var $id			= "";
	
	function DbConnection($dbConnection=array()){
		$this->id = uniqid("CON");
		if(!empty($dbConnection)){
			if(is_string($dbConnection) && strpos($dbConnection,"=") === false){
				$this->id = $dbConnection;
				$this->setHost(Rhaco::constant("DATABASE_".$dbConnection."_HOST"));
				$this->setUser(Rhaco::constant("DATABASE_".$dbConnection."_USER"));
				$this->setPassword(Rhaco::constant("DATABASE_".$dbConnection."_PASSWORD"));
				$this->setName(Rhaco::constant("DATABASE_".$dbConnection."_NAME"));
				$this->setPort(Rhaco::constant("DATABASE_".$dbConnection."_PORT"));
				$this->setEncode(Rhaco::constant("DATABASE_".$dbConnection."_ENCODE"));
				$this->setType(Rhaco::constant("DATABASE_".$dbConnection."_TYPE"));
			}else if(Variable::istype("DbConnection",$dbConnection)){
				ObjectUtil::copyProperties($dbConnection,$this);
			}else{
				ObjectUtil::hashConvObject(ArrayUtil::dict($dbConnection,array("name","user","password","type","host","port","encode","new","id"),false),$this);
			}
		}
	}

	/**
	 * typeからDbUtil***のインスタンスを返す
	 *
	 */
	function base(){
		if(empty($this->type)){
			ExceptionTrigger::raise(new IllegalArgumentException("DB connection"));
			return null;
		}
		return Rhaco::obj($this->type);
	}
	function setHost($value){
		$this->host = (empty($value) ? "localhost" : $value);
	}
	function setPort($value){
		$this->port = $value;
	}
	function setUser($value){
		$this->user = $value;
	}
	function setPassword($value){
		$this->password = $value;
	}
	function setEncode($value){
		$this->encode = $value;
	}
	function setName($value){
		$this->name = $value;
	}
	function setType($value){
		$this->type = $value;
	}
	function setNew($value){
		$this->new = $value;
	}
	function setId($value){
		if(!empty($value)) $this->id = $value;
	}
	
	function initializer(){
		/***
		 * $con = new DbConnection("type=database.controller.DbUtilMySQL");
		 * assert(Variable::istype("DbUtilMySQLInitializer",$con->initializer()),"MySQL");
		 * 
		 * $con = new DbConnection("type=database.controller.DbUtilOracle");
		 * assert(Variable::istype("DbUtilOracleInitializer",$con->initializer()),"Oracle");
		 * 
		 * $con = new DbConnection("type=database.controller.DbUtilPostgreSQL");
		 * assert(Variable::istype("DbUtilPostgreSQLInitializer",$con->initializer()),"PostgreSQL");
		 * 
		 * $con = new DbConnection("type=database.controller.DbUtilSQLite");
		 * assert(Variable::istype("DbUtilSQLiteInitializer",$con->initializer()),"SQLite");
		 */
		return Rhaco::obj("setup.".$this->type."Initializer");
	}
}
?>