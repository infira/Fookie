<?php

class ConfigEntries
{
	public static $entries = [];
	
	public static $defaultValues = [];
	
	/**
	 * Get client config
	 *
	 * @param string $name
	 * @return mixed
	 */
	public final static function get($name)
	{
		if (array_key_exists($name, self::$entries))
		{
			return self::$entries[$name];
		}
		elseif (array_key_exists($name, self::$defaultValues))
		{
			return self::$defaultValues[$name];
		}
		
		return FALSE;
	}
	
	public final static function setGetVar($name, $value = NULL, $fixWidth = FALSE, $setDefinition = FALSE)
	{
		if ($value !== NULL)
		{
			if ($fixWidth !== FALSE)
			{
				foreach (explode(",", $fixWidth) as $fixW)
				{
					if ($fixW == "float")
					{
						$value = floatval($value);
					}
					elseif ($fixW == "int")
					{
						$value = intval($value);
					}
					elseif ($fixW == "bool")
					{
						$value = (boolean)$value;
					}
					elseif ($fixW == "object")
					{
						$value = (object)$value;
					}
					elseif ($fixW == "function")
					{
						$value = $value;
					}
					elseif ($fixW == "array")
					{
						if (is_string($value))
						{
							$ex     = explode(",", $value);
							$newArr = [];
							if (count($ex) > 0)
							{
								foreach ($ex as $key => $val)
								{
									$val = trim($val);
									if ($val)
									{
										$newArr[$key] = $val;
									}
								}
							}
							$value = $newArr;
						}
					}
					elseif ($fixW == "lower")
					{
						$value = strtolower($value);
					}
					else
					{
						$value = trim($value);
					}
				}
			}
			self::$entries[$name] = $value;
			if ($setDefinition !== FALSE)
			{
				define($setDefinition, $value);
			}
			
			return NULL;
		}
		
		return self::get($name);
	}
	
	public final static function addDefaultValue($name, $value)
	{
		self::$defaultValues[$name] = $value;
	}
	
	public final static function isSetted($name)
	{
		if (array_key_exists($name, self::$entries))
		{
			if (self::$entries[$name] != NULL)
			{
				return TRUE;
			}
		}
		
		return FALSE;
	}
}

abstract class ConfigManager
{
	
	public final static function init()
	{
		if (!ConfigEntries::$entries)
		{
			ConfigEntries::addDefaultValue("defaultCacheDriver", "sess");
			ConfigEntries::addDefaultValue("dbCacheDriver", "sess");
			ConfigEntries::addDefaultValue("adaptiveImgHandlerClassName", "LibsAdaptiveImgHandler");
			ConfigEntries::addDefaultValue("__configIsIntialized", TRUE);
			ConfigEntries::addDefaultValue("voidDbTableClassInstall", []);
			self::routeGETParameter('route');
			self::routeCurrent('web');
			self::routeDefaultRole('web');
			self::routeRoles(['web']);
		}
	}
	
	public function __call($name, $arguments)
	{
		alert("Config : you trying to call config function $name but this not exists or is depreacated");
	}
	
	protected static function finalizeConfig()
	{
		if (!ConfigEntries::get("__configIsIntialized"))
		{
			alert("AppConfig::init() must be called");
		}
		if (!self::projectName())
		{
			alert("AppConfig porjectName must be defined");
		}
		if (!self::sessionTimeout())
		{
			alert("AppConfig sessionTimeout must be defined");
		}
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
	
	public final static function redisConnection($connection = NULL)
	{
		return ConfigEntries::setGetVar("redisConnection", $connection, "array");
	}
	
	public final static function defaultDbCacheDriver($implementer = NULL)
	{
		if (!in_array($implementer, ["mem", "sess", "redis", NULL]))
		{
			exit("AppConfig->dbCahceDriver undefined cache implementer = $implementer");
		}
		
		return ConfigEntries::setGetVar("dbCahceDriver", $implementer, "string");
	}
	
	public final static function defaultCacheDriver($implementer = NULL)
	{
		if (!in_array($implementer, ["mem", "sess", "redis", NULL]))
		{
			exit("AppConfig->defaultCacheDriver undefined cache implementer = $implementer");
		}
		
		return ConfigEntries::setGetVar("defaultCacheDriver", $implementer, "string");
	}
	
	public final static function cacheBaseAlias($alias = NULL)
	{
		return ConfigEntries::setGetVar("cacheBaseAlias", $alias, "string", "CACHE_BASE_ALIAS");
	}
	
	public final static function adaptiveImgHandlerClassName($className = NULL)
	{
		return ConfigEntries::setGetVar("adaptiveImgHandlerClassName", $className);
	}
	
	/**
	 * Log SQL Query history to database
	 *
	 * @param string $type [sel,ins,rep,del,upd,set]
	 * @return mixed|null
	 */
	public final static function logSQLQueryHistory($type = NULL)
	{
		return ConfigEntries::setGetVar("logSQLQueryHistory", $type, "array");
	}
	
	public final static function haltLogSQLQueryHistory($type = NULL)
	{
		return ConfigEntries::setGetVar("haltLogSQLQueryHistory", $type, "bool");
	}
	
	/**
	 * Set developer email, for sendind errors, and logs etc
	 *
	 * @param array $arr - array(name,email)
	 * @return Ambigous <NULL, mixed, boolean>
	 */
	public final static function developerEmail($array = NULL)
	{
		return ConfigEntries::setGetVar("developerEmail", $array, "array");
	}
	
	public final static function projectName($name = NULL)
	{
		return ConfigEntries::setGetVar("projectName", $name, "string", "PROJECT_NAME");
	}
	
	/**
	 * Session timeout in seconds
	 *
	 * @param null $time
	 * @return Ambigous <NULL, mixed, boolean>
	 */
	public final static function sessionTimeout($time = NULL)
	{
		return ConfigEntries::setGetVar("sessionTimeOut", $time, "int", "SESSION_TIMEOUT");
	}
	
	/**
	 * Vod logging on these db tables
	 *
	 * @param array $array - array of tables or string of tables separated by commas
	 * @return Ambigous <NULL, mixed, boolean>
	 */
	public final static function voidDbTablesLog(array $array = NULL)
	{
		return ConfigEntries::setGetVar("voidDbTablesLog", $array, "array");
	}
	
	/**
	 * Vod logging on these db tables
	 *
	 * @param bool $bool
	 * @return bool
	 */
	public final static function dbLoggerEnabled(bool $bool = NULL)
	{
		return ConfigEntries::setGetVar("dbLoggerEnabled", $bool, "bool");
	}
	
	/**
	 * What is the route http variable defined in .htaccess
	 *
	 * @param $var $arr - parameter name in _get
	 * @return bool
	 */
	public final static function routeGETParameter(string $var = NULL)
	{
		return ConfigEntries::setGetVar("routeGETParameter", $var, "string");
	}
	
	/**
	 * Default route role
	 *
	 * @param string $var
	 * @return bool
	 */
	public final static function routeDefaultRole(string $var = NULL)
	{
		return ConfigEntries::setGetVar("routeDefault", $var, "string");
	}
	
	/**
	 * Default route role
	 *
	 * @param string $var
	 * @return bool
	 */
	public final static function routeCurrent(string $var = NULL)
	{
		return ConfigEntries::setGetVar("routeCurrent", $var, "string");
	}
	
	/**
	 * Default route role
	 *
	 * @param string $array
	 * @return bool
	 */
	public final static function routeRoles(array $array = NULL)
	{
		return ConfigEntries::setGetVar("routeRoles", $array, "array");
	}
	
	/**
	 * Set db tables to  void install db class
	 *
	 * @param string $array
	 * @return bool
	 */
	public final static function voidDbTableClassInstall(array $array = NULL)
	{
		return ConfigEntries::setGetVar("voidDbTableClassInstall", $array, "array");
	}
	
	/**
	 * Get routes array
	 *
	 * @return array
	 */
	public final static function getRoutes(): object
	{
		$routes = [];
		require_once Path::config("routes.php");
		
		$output               = new stdClass();
		$output->routes       = isset($routes) ? is_array($routes) ? $routes : [] : [];
		$output->matchParsers = isset($matchParsers) ? is_array($matchParsers) ? $matchParsers : [] : [];
		$output->matchTypes   = isset($matchTypes) ? is_array($matchTypes) ? $matchTypes : [] : [];
		
		return $output;
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
	 * Check is current envinronment live like, prelive, env what must act like live
	 *
	 * @return bool
	 */
	abstract public static function isLiveWorthy();
	
	/**
	 * Check is current envinronment dev
	 *
	 * @return bool
	 */
	abstract public static function isDevENV();
}

?>