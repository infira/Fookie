<?php

namespace Infira\Fookie;

use Infira\Poesis\QueryHistory;
use Infira\Fookie\facade\Session;
use Infira\Fookie\request\Route;
use Infira\Fookie\request\Payload;
use Infira\Utils\Http;
use Infira\Fookie\facade\Cache;
use Infira\Poesis\ConnectionManager;
use Infira\Cachly\options\DbDriverOptions;
use AppConfig;
use Infira\Poesis\Poesis;

class Fookie
{
	private static $options = [];
	
	public static function beforeRouteBoot()
	{
		if (self::optExists('beforeRouteBoot'))
		{
			$c = self::opt('beforeRouteBoot');
			$c();
		}
	}
	
	public static function boot(callable $beforeController = null)
	{
		Route::match();
		if ($beforeController !== null)
		{
			$beforeController();
		}
		if (Route::isMatched())
		{
			Route::runController();
		}
		else
		{
			Fookie::error("route not found", 'routeNotFound', 404);
		}
		$payload = Payload::getOutput();
		if (self::opt('showProfile'))
		{
			$payload .= '<pre></pre><div class="_profiler">';
			$payload .= Prof()->dumpTimers();
			$payload .= QueryHistory::getHTMLTable();
			$payload .= '</div></pre>';
		}
		self::closeConnections();
		
		return $payload;
	}
	
	public static function initDb()
	{
		Poesis::toggleQueryHistory(AppConfig::poesisQueryHistoryEnabled());
	}
	
	/**
	 * @param string|null $SID - start session with own id, restore data
	 * @param string      $name
	 */
	public static function initSession(string $SID = null, string $name = 'PHPSESSID')
	{
		$differnetSessionForEachRole = true;
		if (Fookie::optExists('differnetSessionForEachRole'))
		{
			$differnetSessionForEachRole = Fookie::opt('differnetSessionForEachRole');
		}
		
		if ($differnetSessionForEachRole)
		{
			$name = Route::getRole();
			if (Http::existsGET('_overrideSessionName'))
			{
				$name = Http::getGET('_overrideSessionName');
			}
		}
		Session::setTimeout(AppConfig::sessionTimeout());
		ini_set('session.gc_maxlifetime', AppConfig::sessionTimeout());
		Session::init($name, $SID);
	}
	
	public static function initCache()
	{
		$dbOptons         = new DbDriverOptions();
		$dbOptons->client = ConnectionManager::default()->getMysqli();
		Cache::configureDb($dbOptons);
		Cache::init();
		Cache::setDefaultDriver(AppConfig::defaultCacheDriver());
		Cache::setCacheKeyPrefix(AppConfig::getENV());
	}
	
	public static function setUseDiffernetSessionForEachRole(bool $bool)
	{
		self::$options['differnetSessionForEachRole'] = $bool;
	}
	
	public static function setShowProfile(callable $callable)
	{
		self::setOpt('showProfile', $callable);
	}
	
	public static function optExists(string $name)
	{
		return array_key_exists($name, self::$options);
	}
	
	public static function setOpt(string $name, $value)
	{
		self::$options[$name] = $value;
	}
	
	public static function opt(string $name)
	{
		if (!self::optExists($name))
		{
			return false;
		}
		
		return self::$options[$name];
	}
	
	private static function closeConnections()
	{
		return true;
	}
	
	public static function error($error, string $code = null, $httpStatusCode = 400)
	{
		if (AppConfig::isLiveWorthy())
		{
			Payload::sendError($error, $code, $httpStatusCode);
		}
		else
		{
			alert($error);
		}
	}
}

?>