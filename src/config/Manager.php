<?php

namespace Infira\Fookie\config;

use Infira\Fookie\config\Entries as ConfigEntries;

abstract class Manager
{
	public static function init()
	{
		if (!defined("BASE_DIR"))
		{
			alert("BASE_DIR must be defined");
		}
		if (!defined("TEMP_DIR"))
		{
			alert("TEMP_DIR must be defined");
		}
		if (!defined("APP_DIR"))
		{
			alert("APP_DIR must be defined");
		}
		if (!self::projectName())
		{
			alert("AppConfig porjectName must be defined");
		}
		if (!self::sessionTimeout())
		{
			alert("AppConfig sessionTimeout must be defined");
		}
		
		if (!ConfigEntries::get('__configIsIntialized'))
		{
			ConfigEntries::addDefaultValue("defaultCacheDriver", "sess");
			ConfigEntries::addDefaultValue("dbCacheDriver", "sess");
			ConfigEntries::addDefaultValue("__configIsIntialized", true);
			ConfigEntries::addDefaultValue("voidDbTableClassInstall", []);
			ConfigEntries::addDefaultValue("saveRequests", true);
			self::routeCurrent('web');
			self::routeDefaultRole('web');
			self::routeRoles(['web']);
		}
	}
	
	public function __call($name, $arguments)
	{
		alert("Config : you trying to call config function $name but this not exists or is depreacated");
	}
	
	protected static function setGetVar($name, $value = null, $fixWidth = false, $setDefinition = false)
	{
		return ConfigEntries::setGetVar($name, $value, $fixWidth, $setDefinition);
	}
	
	/**
	 * Infira libs specisifc configs
	 */
	public final static function setDbConnection($dbName, $userName, $password, $host = "localhost")
	{
		if (defined("DB_NAME"))
		{
			alert("DB CONNECTION already defined");
		}
		define("DB_NAME", trim($dbName));
		define("DB_USER", trim($userName));
		define("DB_PASSWORD", trim($password));
		define("DB_HOST", trim($host));
	}
	
	public final static function setDbIntraConnection($dbName, $userName, $password, $host = "localhost")
	{
		if (defined("DB_INTRA_NAME"))
		{
			alert("DB INTRA CONNECTION already defined");
		}
		define("DB_INTRA_NAME", trim($dbName));
		define("DB_INTRA_USER", trim($userName));
		define("DB_INTRA_PASSWORD", trim($password));
		define("DB_INTRA_HOST", trim($host));
	}
	
	public static function setDbLogConnection($dbName, $userName, $password, $host = "localhost")
	{
		if (defined("DB_LOG_NAME"))
		{
			alert("DB_LOG CONNECTION already defined");
		}
		define("DB_LOG_NAME", trim($dbName));
		define("DB_LOG_USER", trim($userName));
		define("DB_LOG_PASSWORD", trim($password));
		define("DB_LOG_HOST", trim($host));
	}
	
	public final static function redisConnection($connection = null)
	{
		return self::setGetVar("redisConnection", $connection, "array");
	}
	
	public final static function defaultDbCacheDriver($implementer = null)
	{
		if (!in_array($implementer, ["mem", "sess", "redis", null]))
		{
			exit("AppConfig->dbCahceDriver undefined cache implementer = $implementer");
		}
		
		return self::setGetVar("dbCahceDriver", $implementer, "string");
	}
	
	public final static function defaultCacheDriver($implementer = null)
	{
		if (!in_array($implementer, ["mem", "sess", "redis", null]))
		{
			exit("AppConfig->defaultCacheDriver undefined cache implementer = $implementer");
		}
		
		return self::setGetVar("defaultCacheDriver", $implementer, "string");
	}
	
	/**
	 * Log SQL Query history to database
	 *
	 * @param string $type [sel,ins,rep,del,upd,set]
	 * @return mixed|null
	 */
	public final static function logSQLQueryHistory($type = null)
	{
		return self::setGetVar("logSQLQueryHistory", $type, "array");
	}
	
	public final static function haltLogSQLQueryHistory($type = null)
	{
		return self::setGetVar("haltLogSQLQueryHistory", $type, "bool");
	}
	
	/**
	 * Set developer email, for sendind errors, and logs etc
	 *
	 * @param array $arr - array(name,email)
	 * @return Ambigous <NULL, mixed, boolean>
	 */
	public final static function developerEmail($array = null)
	{
		return self::setGetVar("developerEmail", $array, "array");
	}
	
	public final static function projectName($name = null)
	{
		return self::setGetVar("projectName", $name, "string", "PROJECT_NAME");
	}
	
	/**
	 * Session timeout in seconds
	 *
	 * @param null $time
	 * @return Ambigous <NULL, mixed, boolean>
	 */
	public final static function sessionTimeout($time = null)
	{
		return self::setGetVar("sessionTimeOut", $time, "int", "SESSION_TIMEOUT");
	}
	
	/**
	 * Vod logging on these db tables
	 *
	 * @param array $array - array of tables or string of tables separated by commas
	 * @return Ambigous <NULL, mixed, boolean>
	 */
	public final static function voidDbTablesLog(array $array = null)
	{
		return self::setGetVar("voidDbTablesLog", $array, "array");
	}
	
	/**
	 * Vod logging on these db tables
	 *
	 * @param bool $bool
	 * @return bool
	 */
	public final static function dbLoggerEnabled(bool $bool = null)
	{
		return self::setGetVar("dbLoggerEnabled", $bool, "bool");
	}
	
	/**
	 * Default route role
	 *
	 * @param string $var
	 * @return bool
	 */
	public final static function routeDefaultRole(string $var = null)
	{
		return self::setGetVar("routeDefault", $var, "string");
	}
	
	/**
	 * Default route role
	 *
	 * @param string $var
	 * @return bool
	 */
	public final static function routeCurrent(string $var = null)
	{
		return self::setGetVar("routeCurrent", $var, "string");
	}
	
	/**
	 * Save requests
	 *
	 * @param bool|null $bool
	 * @return bool
	 */
	public final static function saveRequests(bool $bool = null)
	{
		return self::setGetVar("saveRequests", $bool, "string");
	}
	
	/**
	 * Default route role
	 *
	 * @param string $array
	 * @return bool
	 */
	public final static function routeRoles(array $array = null)
	{
		return self::setGetVar("routeRoles", $array, "array");
	}
	
	/**
	 * Set db tables to  void install db class
	 *
	 * @param string $array
	 * @return bool
	 */
	public final static function voidDbTableClassInstall(array $array = null)
	{
		return self::setGetVar("voidDbTableClassInstall", $array, "array");
	}
	
	/**
	 * get envinronment
	 *
	 * @return string
	 */
	abstract public static function getENV();
	
	/**
	 * Check is current environment
	 *
	 * @param string|array $env
	 * @return bool
	 */
	abstract public static function isENV($env);
	
	/**
	 * Check is current envinronment live
	 *
	 * @return bool
	 */
	abstract public static function isLiveENV();
	
	/**
	 * Check is current envinronment dev
	 *
	 * @return bool
	 */
	abstract public static function isDevENV();
}

?>