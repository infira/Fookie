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
			self::routeCurrent('web');
			self::routeDefaultRole('web');
			self::routeRoles(['web']);
		}
	}
	
	public function __call($name, $arguments)
	{
		alert("Config : you trying to call config function $name but this not exists or is depreacated");
	}
	
	protected static function setGetVar($name, $value = null, string $setDefinition = null)
	{
		return ConfigEntries::setGetVar($name, $value, $setDefinition);
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
	
	public final static function redisConnection(array $connection = null): ?array
	{
		return self::setGetVar("redisConnection", $connection);
	}
	
	public final static function defaultDbCacheDriver(string $implementer = null): ?string
	{
		if (!in_array($implementer, ["mem", "sess", "redis", null]))
		{
			exit("AppConfig->dbCahceDriver undefined cache implementer = $implementer");
		}
		
		return self::setGetVar("dbCahceDriver", $implementer);
	}
	
	public final static function defaultCacheDriver(string $implementer = null): ?string
	{
		if (!in_array($implementer, ["mem", "sess", "redis", null]))
		{
			exit("AppConfig->defaultCacheDriver undefined cache implementer = $implementer");
		}
		
		return self::setGetVar("defaultCacheDriver", $implementer);
	}
	
	/**
	 * Set developer email, for sending errors, and logs etc
	 *
	 * @param array|null $array
	 * @return array|null
	 */
	public final static function developerEmail(array $array = null): ?array
	{
		return self::setGetVar("developerEmail", $array);
	}
	
	public final static function projectName(string $name = null): ?string
	{
		return self::setGetVar("projectName", $name, "PROJECT_NAME");
	}
	
	/**
	 * Session timeout in seconds
	 *
	 * @param int|null $time
	 */
	public final static function sessionTimeout(int $time = null): ?int
	{
		return self::setGetVar("sessionTimeOut", $time, "SESSION_TIMEOUT");
	}
	
	/**
	 * Vod logging on these db tables
	 *
	 * @param array|null $array $array - array of tables or string of tables separated by commas
	 * @return array
	 */
	public final static function voidDbTablesLog(array $array = null): array
	{
		return (array)self::setGetVar("voidDbTablesLog", $array);
	}
	
	/**
	 * Vod logging on these db tables
	 *
	 * @param bool $bool
	 * @return bool
	 */
	public final static function dbLoggerEnabled(bool $bool = null): bool
	{
		return (bool)self::setGetVar("dbLoggerEnabled", $bool);
	}
	
	/**
	 * Default route role
	 *
	 * @param string|null $role
	 * @return string
	 */
	public final static function routeDefaultRole(string $role = null): string
	{
		return (string)self::setGetVar("routeDefault", $role);
	}
	
	/**
	 * Default route role
	 *
	 * @param string|null $route
	 * @return string
	 */
	public final static function routeCurrent(string $route = null): string
	{
		return (string)self::setGetVar("routeCurrent", $route);
	}
	
	/**
	 * Save requests
	 *
	 * @param array|null $config - ['model'=>'TSavedRequests']
	 * @return array|null
	 */
	public final static function saveRequests(array $config = null): ?array
	{
		return self::setGetVar("saveRequests", $config);
	}
	
	/**
	 * Default route role
	 *
	 * @param array|null $array $array
	 * @return array|null
	 */
	public final static function routeRoles(array $array = null): ?array
	{
		return (array)self::setGetVar("routeRoles", $array);
	}
	
	/**
	 * Set db tables to  void install db class
	 *
	 * @param array|null $array $array
	 * @return array|null
	 */
	public final static function voidDbTableClassInstall(array $array = null): array
	{
		return (array)self::setGetVar("voidDbTableClassInstall", $array);
	}
	
	/**
	 * get environment
	 *
	 * @return string
	 */
	abstract public static function getENV(): string;
	
	/**
	 * Check is current environment
	 *
	 * @param string|array $env
	 * @return bool
	 */
	abstract public static function isENV($env): bool;
	
	/**
	 * Check is current environment live
	 *
	 * @return bool
	 */
	abstract public static function isLiveENV(): bool;
	
	/**
	 * Check is current environment dev
	 *
	 * @return bool
	 */
	abstract public static function isDevENV(): bool;
}

?>