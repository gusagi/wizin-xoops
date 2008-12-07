<?php
Rhaco::import("setup.database.model.TableModel");
Rhaco::import("setup.database.model.ExTableModel");
Rhaco::import("setup.database.model.MapTableModel");
Rhaco::import("exception.ExceptionTrigger");
Rhaco::import("exception.model.SqlException");
Rhaco::import("lang.ObjectUtil");
Rhaco::import("exception.model.NotFoundException");
Rhaco::import("tag.model.SimpleTag");
Rhaco::import("lang.StringUtil");
Rhaco::import("lang.Variable");
Rhaco::import("util.Logger");
Rhaco::import("exception.model.DuplicateException");
Rhaco::import("network.http.Header");
/**
 * setup.php用　data model
 * 
 * @author Kazutaka Tokushima
 * @license New BSD License
 * @copyright Copyright 2007- rhaco project. All rights reserved.
 */
class DatabaseModel{
	var $name;
	var $class;
	var $recognition_code;

	var $user;
	var $host;
	var $password;
	var $port;
	var $encode = "UTF8";
	var $type = "database.controller.DbUtilMySQL";
	var $prefix;
	var $description;

	var $tableList = array();
	
	function DatabaseModel($dbTag){
		if(Variable::istype("SimpleTag",$dbTag)){
			ObjectUtil::copyProperties($dbTag,$this,true);
			if(empty($this->name)) ExceptionTrigger::raise(new NotFoundException(Message::_("database name")));
			
			$this->class = (empty($this->class)) ? $this->name : $this->class;
			$this->recognition_code = strtolower($this->class);		
	
			if(Rhaco::constant(sprintf("DATABASE_%s_NAME",$this->class)) != null){
				$this->name			= Rhaco::constant(sprintf("DATABASE_%s_NAME",$this->class));
				$this->user			= Rhaco::constant(sprintf("DATABASE_%s_USER",$this->class));
				$this->host			= Rhaco::constant(sprintf("DATABASE_%s_HOST",$this->class));
				$this->password		= Rhaco::constant(sprintf("DATABASE_%s_PASSWORD",$this->class));
				$this->port			= Rhaco::constant(sprintf("DATABASE_%s_PORT",$this->class));
				$this->type			= Rhaco::constant(sprintf("DATABASE_%s_TYPE",$this->class));
				$this->encode		= Rhaco::constant(sprintf("DATABASE_%s_ENCODE",$this->class));
				$this->prefix		= Rhaco::constant(sprintf("DATABASE_%s_PREFIX",$this->class));									
			}else if(empty($this->prefix)){
				$this->prefix		= substr(strtoupper($this->class),0,3)."_";
			}
			if($this->type == "database.controller.DbUtilMySQL") $this->encode = str_replace("-","",$this->encode);		

			foreach($dbTag->getIn("table",true) as $tableTag){
				$table = new TableModel($tableTag);
				if(array_key_exists($table->recognition_code,$this->tableList)){
					ExceptionTrigger::raise(new DuplicateException(Message::_("{1} [{2}]",$table->name,$table->recognition_code)));				
				}
				$this->tableList[$table->recognition_code] = $table;
			}
			foreach($dbTag->getIn("map",true) as $tableTag){
				$table = new MapTableModel($tableTag);
				if(array_key_exists($table->recognition_code,$this->tableList)){
					ExceptionTrigger::raise(new DuplicateException(Message::_("{1} [{2}]",$table->name,$table->recognition_code)));				
				}
				$this->tableList[$table->recognition_code] = $table;
			}			
			foreach($dbTag->getIn("ext",true) as $tableTag){
				$table = new ExTableModel($tableTag,$this->tableList);
				if(array_key_exists($table->recognition_code,$this->tableList)){
					ExceptionTrigger::raise(new DuplicateException(Message::_("{1} [{2}]",$table->class,$table->recognition_code)));				
				}
				$this->tableList[$table->recognition_code] = $table;
			}
			$this->description = StringUtil::toULD($dbTag->getInValue("description"));
	
			foreach($this->tableList as $table){
				foreach($table->columnList as $column){
					$this->tableList[$table->recognition_code]->columnList[$column->recognition_code]->checkReference($this->tableList);
				}
			}
			foreach($this->tableList as $table){
				$referenceList = array();
				foreach($table->columnList as $column){
					if($column->isReference()){
						if(isset($referenceList[$column->reference[0]->recognition_code])){
							ExceptionTrigger::raise(new DuplicateException(Message::_("{1} の参照 {2}",$table->recognition_code,$column->reference[0]->recognition_code)));										
						}
						$referenceList[$column->reference[0]->recognition_code] = $column->reference;
					}
				}
			}
			if(!ExceptionTrigger::invalid()){
				foreach($this->tableList as $table){
					foreach($table->columnList as $column){
						if($column->isReference()){
							$this->tableList[$column->reference[0]->recognition_code]->columnList[$column->reference[1]->recognition_code]->dependList[$table->recognition_code."::".$column->recognition_code] = array($table,$column);
						}
					}
				}				
				foreach($this->tableList as $table){
					if(Variable::istype("MapTableModel",$table)){
						foreach($table->columnList as $column){
							if($column->isReference()){
								foreach($table->columnList as $rcolumn){
									if($rcolumn->isReference() && $rcolumn->recognition_code != $column->recognition_code){
										$this->tableList[$column->reference[0]->recognition_code]->mapList[] = $rcolumn->reference;
									}
								}
							}
						}
					}
				}
				foreach($dbTag->getIn("default",true) as $defaultTag){
					$class = StringUtil::regularizedName($defaultTag->getParameter("class",$defaultTag->getParameter("name")));
					$bool = false;

					foreach($this->tableList as $table_recognition_code => $table){
						if(Variable::iequal($table->recognition_code,$class) || Variable::iequal($table->recognition_code,$class)){
							$tag = new SimpleTag("default",$defaultTag->getValue(),array("name"=>$table->class));
							$this->tableList[$table_recognition_code]->defaults .= $tag->get();
							$bool = true;
							break;
						}
					}
					if(!$bool) ExceptionTrigger::raise(new NotFoundException(Message::_("table name `{1}` of the default data",$class)));
				}
			}
		}
	}	
	function create($target){
		if(Variable::iequal($target,$this->recognition_code)){
			$con = new DbConnection();
			$dbUtil = new DbUtilInitializer(ObjectUtil::copyProperties($this,$con,true));

			if($dbUtil !== null && $dbUtil->connection !== false){
				foreach(split(";",$dbUtil->forward($this)) as $sql){
					if(trim($sql) != ""){
						if(!$dbUtil->query($sql)){
							ExceptionTrigger::raise(new SqlException($sql));
						}
					}
				}
			}
			if(ExceptionTrigger::isException()) $dbUtil->rollback();
			$dbUtil->close();
		}
	}

	function createSql($target){
		if(Variable::iequal($target,$this->recognition_code)){
			$con = new DbConnection();
			$dbUtil = new DbUtilInitializer(ObjectUtil::copyProperties($this,$con,true));

			Logger::disableDisplay();
			Header::attach($dbUtil->forward($this),$target.".sql");
			Rhaco::end();
		}		
	}

	function import($target){
		/*** #pass */
		if(Variable::iequal($target,$this->recognition_code)){
			$con = new DbConnection();
			$dbUtil = new DbUtilInitializer(ObjectUtil::copyProperties($this,$con,true));

			foreach($this->tableList as $table){
				if(Rhaco::import("model.".$table->method)){
					$method = $table->method;
					$obj = new $method();
					if(!empty($table->defaults)) $dbUtil->importXml($obj,$table->defaults,false);
					if(!empty($table->default)) $dbUtil->import($obj,FileUtil::read($table->default));
				}
			}
			if(ExceptionTrigger::isException()) $dbUtil->rollback();
			$dbUtil->close();
		}
	}
	function droptable($target){
		/*** #pass */
		if(Variable::iequal($target,$this->recognition_code)){
			$con = new DbConnection();
			$dbUtil = new DbUtilInitializer(ObjectUtil::copyProperties($this,$con,true));

			foreach($this->tableList as $table){
				if(Rhaco::import("model.".$table->method)){
					$method = $table->method;
					$dbUtil->droptable(new $method());
				}
			}
			if(ExceptionTrigger::isException()) $dbUtil->rollback();
			$dbUtil->close();
		}
	}
	
	function isDefault(){
		foreach($this->tableList as $table){
			if($table->isDefaults()) return true;
		}
		return false;
	}
	
	function isReserved($name,$type,$label){
		if(!Rhaco::isVariable("RHACO_DATABASE_RESERVED_NAME")){
			Rhaco::setVariable("RHACO_DATABASE_RESERVED_NAME",array(
"ACCESS",
"ACCOUNT",
"ACTIVATE",
"ADD",
"ADDADD",
"ADMIN",
"ADVISE",
"AFTER",
"ALL",
"ALLOCATE",
"ALL_ROWS",
"ALTER",
"ANALYZE",
"AND",
"ANY",
"ARCHIVE",
"ARCHIVELOG",
"ARRAY",
"AS",
"ASC",
"AT",
"AUDIT",
"AUTHENTICATED",
"AUTHORIZATION",
"AUTOEXTEND",
"AUTOMATIC",
"BACKUP",
"BECOME",
"BEFORE",
"BEGIN",
"BETWEEN",
"BFILE",
"BIGINT",
"BINARY",
"BITMAP",
"BLOB",
"BLOCK",
"BODY",
"BOTH",
"BY",
"CACHE",
"CACHE_INSTANCES",
"CANCEL",
"CASCADE",
"CASE",
"CAST",
"CFILE",
"CHAINED",
"CHANGE",
"CHAR",
"CHARACTER",
"CHAR_CS",
"CHECK",
"CHECKPOINT",
"CHOOSE",
"CHUNK",
"CLEAR",
"CLOB",
"CLONE",
"CLOSE",
"CLOSE_CACHED_OPEN_CURSORS",
"CLUSTER",
"COALESCE",
"COLLATE",
"COLUMN",
"COLUMNS",
"COMMENT",
"COMMIT",
"COMMITTED",
"COMPATIBILITY",
"COMPILE",
"COMPLETE",
"COMPOSITE_LIMIT",
"COMPRESS",
"COMPUTE",
"CONNECT",
"CONNECT_TIME",
"CONSTRAINT",
"CONSTRAINTS",
"CONTENTS",
"CONTINUE",
"CONTROLFILE",
"CONVERT",
"COST",
"CPU_PER_CALL",
"CPU_PER_SESSION",
"CREATE",
"CROSS",
"CURRENT",
"CURRENT_DATE",
"CURRENT_SCHEMA",
"CURRENT_TIME",
"CURRENT_TIMESTAMP",
"CURRENT_USER",
"CURREN_USER",
"CURSOR",
"CYCLE",
"DANGLING",
"DATABASE",
"DATABASES",
"DATAFILE",
"DATAFILES",
"DATAOBJNO",
"DATE",
"DAY_HOUR",
"DAY_MICROSECOND",
"DAY_MINUTE",
"DAY_SECOND",
"DBA",
"DBHIGH",
"DBLOW",
"DBMAC",
"DEALLOCATE",
"DEBUG",
"DEC",
"DECIMAL",
"DECLARE",
"DEFAULT",
"DEFERRABLE",
"DEFERRED",
"DEGREE",
"DELAYED",
"DELETE",
"DEREF",
"DESC",
"DESCRIBE",
"DIRECTORY",
"DISABLE",
"DISCONNECT",
"DISMOUNT",
"DISTINCT",
"DISTINCTROW",
"DISTRIBUTED",
"DIV",
"DML",
"DOUBLE",
"DROP",
"DUAL",
"DUMP",
"EACH",
"ELSE",
"ENABLE",
"ENCLOSED",
"END",
"ENFORCE",
"ENTRY",
"ESCAPE",
"ESCAPED",
"EXCEPT",
"EXCEPTIONS",
"EXCHANGE",
"EXCLUDING",
"EXCLUSIVE",
"EXECUTE",
"EXISTS",
"EXPIRE",
"EXPLAIN",
"EXTENT",
"EXTENTS",
"EXTERNALLY",
"FAILED_LOGIN_ATTEMPTS",
"FALSE",
"FAST",
"FIELDS",
"FILE",
"FIRST_ROWS",
"FLAGGER",
"FLOAT",
"FLOAT4",
"FLOAT8",
"FLOB",
"FLUSH",
"FOR",
"FORCE",
"FOREIGN",
"FREELIST",
"FREELISTS",
"FROM",
"FULL",
"FULLTEXT",
"FUNCTION",
"GLOBAL",
"GLOBALLY",
"GLOBAL_NAME",
"GRANT",
"GROUP",
"GROUPS",
"HASH",
"HASHKEYS",
"HAVING",
"HEADER",
"HEAP",
"HIGH_PRIORITY",
"HOUR_MICROSECOND",
"HOUR_MINUTE",
"HOUR_SECOND",
"IDENTIFIED",
"IDGENERATORS",
"IDLE_TIME",
"IF",
"IGNORE",
"IMMEDIATE",
"IN",
"INCLUDING",
"INCREMENT",
"INDEX",
"INDEXED",
"INDEXES",
"INDICATOR",
"IND_PARTITION",
"INFILE",
"INITIAL",
"INITIALLY",
"INITRANS",
"INNER",
"INSERT",
"INSTANCE",
"INSTANCES",
"INSTEAD",
"INT",
"INT1",
"INT2",
"INT3",
"INT4",
"INT8",
"INTEGER",
"INTERMEDIATE",
"INTERSECT",
"INTERVAL",
"INTO",
"IS",
"ISOLATION",
"ISOLATION_LEVEL",
"JOIN",
"KEEP",
"KEY",
"KEYS",
"KILL",
"LABEL",
"LAYER",
"LEADING",
"LEFT",
"LESS",
"LEVEL",
"LIBRARY",
"LIKE",
"LIMIT",
"LINES",
"LINK",
"LIST",
"LOAD",
"LOB",
"LOCAL",
"LOCALTIME",
"LOCALTIMESTAMP",
"LOCK",
"LOCKED",
"LOG",
"LOGFILE",
"LOGGING",
"LOGICAL_READS_PER_CALL",
"LOGICAL_READS_PER_SESSION",
"LONG",
"LONGBLOB",
"LONGTEXT",
"LOW_PRIORITY",
"MANAGE",
"MASTER",
"MATCH",
"MAX",
"MAXARCHLOGS",
"MAXDATAFILES",
"MAXEXTENTS",
"MAXINSTANCES",
"MAXLOGFILES",
"MAXLOGHISTORY",
"MAXLOGMEMBERS",
"MAXSIZE",
"MAXTRANS",
"MAXVALUE",
"MEDIUMBLOB",
"MEDIUMINT",
"MEDIUMTEXT",
"MEMBER",
"MIDDLEINT",
"MIN",
"MINEXTENTS",
"MINIMUM",
"MINUS",
"MINUTE_MICROSECOND",
"MINUTE_SECOND",
"MINVALUE",
"MLSLABEL",
"MLS_LABEL_FORMAT",
"MOD",
"MODE",
"MODIFY",
"MOUNT",
"MOVE",
"MTS_DISPATCHERS",
"MULTISET",
"NATIONAL",
"NATURAL",
"NCHAR",
"NCHAR_CS",
"NCLOB",
"NEEDED",
"NESTED",
"NETWORK",
"NEW",
"NEXT",
"NOARCHIVELOG",
"NOAUDIT",
"NOCACHE",
"NOCOMPRESS",
"NOCYCLE",
"NOFORCE",
"NOLOGGING",
"NOMAXVALUE",
"NOMINVALUE",
"NONE",
"NOORDER",
"NOOVERRIDE",
"NOPARALLEL",
"NOREVERSE",
"NORMAL",
"NOSORT",
"NOT",
"NOTHING",
"NOWAIT",
"NO_WRITE_TO_BINLOG",
"NULL",
"NUMBER",
"NUMERIC",
"NVARCHAR2",
"OBJECT",
"OBJNO",
"OBJNO_REUSE",
"OF",
"OFF",
"OFFLINE",
"OID",
"OIDINDEX",
"OLD",
"ON",
"ONLINE",
"ONLY",
"OPCODE",
"OPEN",
"OPTIMAL",
"OPTIMIZE",
"OPTIMIZER_GOAL",
"OPTION",
"OPTIONALLY",
"OR",
"ORDER",
"ORGANIZATION",
"OSLABEL",
"OUTER",
"OUTFILE",
"OVERFLOW",
"OWN",
"PACKAGE",
"PARALLEL",
"PARTITION",
"PASSWORD",
"PASSWORD_GRACE_TIME",
"PASSWORD_LIFE_TIME",
"PASSWORD_LOCK_TIME",
"PASSWORD_REUSE_MAX",
"PASSWORD_REUSE_TIME",
"PASSWORD_VERIFY_FUNCTION",
"PCTFREE",
"PCTINCREASE",
"PCTTHRESHOLD",
"PCTUSED",
"PCTVERSION",
"PERCENT",
"PERMANENT",
"PLAN",
"PLSQL_DEBUG",
"POST_TRANSACTION",
"PRECISION",
"PRESERVE",
"PRIMARY",
"PRIOR",
"PRIVATE",
"PRIVATE_SGA",
"PRIVILEGE",
"PRIVILEGES",
"PROCEDURE",
"PROFILE",
"PUBLIC",
"PURGE",
"QUEUE",
"QUOTA",
"RANGE",
"RAW",
"RBA",
"READ",
"READUP",
"REAL",
"REBUILD",
"RECOVER",
"RECOVERABLE",
"RECOVERY",
"REF",
"REFERENCES",
"REFERENCING",
"REFRESH",
"REGEXP",
"RENAME",
"REPLACE",
"REQUIRE",
"RESET",
"RESETLOGS",
"RESIZE",
"RESOURCE",
"RESTRICT",
"RESTRICTED",
"RETURN",
"RETURNING",
"REUSE",
"REVERSE",
"REVOKE",
"RIGHT",
"RLIKE",
"ROLE",
"ROLES",
"ROLLBACK",
"ROW",
"ROWID",
"ROWNUM",
"ROWS",
"RULE",
"SAMPLE",
"SAVEPOINT",
"SB4",
"SCAN_INSTANCES",
"SCHEMA",
"SCN",
"SCOPE",
"SD_ALL",
"SD_INHIBIT",
"SD_SHOW",
"SECOND_MICROSECOND",
"SEGMENT",
"SEG_BLOCK",
"SEG_FILE",
"SELECT",
"SEPARATOR",
"SEQUENCE",
"SERIALIZABLE",
"SESSION",
"SESSIONS_PER_USER",
"SESSION_CACHED_CURSORS",
"SET",
"SHARE",
"SHARED",
"SHARED_POOL",
"SHOW",
"SHRINK",
"SIZE",
"SKIP",
"SKIP_UNUSABLE_INDEXES",
"SMALLINT",
"SNAPSHOT",
"SOME",
"SONAME",
"SORT",
"SPATIAL",
"SPECIFICATION",
"SPLIT",
"SQL_BIG_RESULT",
"SQL_CALC_FOUND_ROWS",
"SQL_SMALL_RESULT",
"SQL_TRACE",
"SSL",
"STANDBY",
"START",
"STARTING",
"STATEMENT_ID",
"STATISTICS",
"STOP",
"STORAGE",
"STORE",
"STRAIGHT_JOIN",
"STRUCTURE",
"SUCCESSFUL",
"SWITCH",
"SYNONYM",
"SYSDATE",
"SYSDBA",
"SYSOPER",
"SYSTEM",
"SYS_OP_ENFORCE_NOT_NULL$",
"SYS_OP_NTCIMG$",
"TABLE",
"TABLES",
"TABLESPACE",
"TABLESPACE_NO",
"TABNO",
"TEMPORARY",
"TERMINATED",
"THAN",
"THE",
"THEN",
"THREAD",
"TIME",
"TIMESTAMP",
"TINYBLOB",
"TINYINT",
"TINYTEXT",
"TO",
"TOPLEVEL",
"TRACE",
"TRACING",
"TRAILING",
"TRANSACTION",
"TRANSITIONAL",
"TRIGGER",
"TRIGGERS",
"TRUE",
"TRUNCATE",
"TX",
"TYPE",
"UB2",
"UBA",
"UID",
"UNARCHIVED",
"UNDO",
"UNION",
"UNIQUE",
"UNLIMITED",
"UNLOCK",
"UNRECOVERABLE",
"UNSIGNED",
"UNTIL",
"UNUSABLE",
"UNUSED",
"UPDATABLE",
"UPDATE",
"USAGE",
"USE",
"USER",
"USING",
"UTC_DATE",
"UTC_TIME",
"UTC_TIMESTAMP",
"VALIDATE",
"VALIDATION",
"VALUE",
"VALUES",
"VARBINARY",
"VARCHAR",
"VARCHAR2",
"VARCHARACTER",
"VARYING",
"VIEW",
"WHEN",
"WHENEVER",
"WHERE",
"WITH",
"WITHOUT",
"WORK",
"WRITE",
"WRITEDOWN",
"WRITEUP",
"XID",
"XOR",
"YEAR",
"YEAR_MONTH",
"ZEROFILL",
"ZONE",
));
		}
		if(in_array(strtoupper($name),Rhaco::getVariable("RHACO_DATABASE_RESERVED_NAME",array()))){
			Logger::warning(Message::_("{1} reserved word '{2}'",$type,$label));
			return false;
		}
		return true;
	}
}
?>