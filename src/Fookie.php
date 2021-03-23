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
use Path;

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
	
	public static function boot()
	{
		Route::detect();
		
		$sessionName                 = 'PHPSESSID';
		$differnetSessionForEachRole = true;
		if (Fookie::optExists('differnetSessionForEachRole'))
		{
			$differnetSessionForEachRole = Fookie::opt('differnetSessionForEachRole');
		}
		
		if ($differnetSessionForEachRole)
		{
			$sessionName = Route::getRole();
			if (Http::existsGET('_overrideSessionName'))
			{
				$sessionName = Http::getGET('_overrideSessionName');
			}
		}
		Session::init($sessionName);
		Cache::init();
		Cache::setDefaultDriver(AppConfig::defaultCacheDriver());
		Cache::setCacheKeyPrefix(AppConfig::getENV());
		
		$dbOptons         = new DbDriverOptions();
		$dbOptons->client = ConnectionManager::default()->getMysqli();
		Cache::configureDb($dbOptons);
		
		Payload::init();
		self::beforeRouteBoot();
		Route::boot();
		$payload = Payload::getOutput();
		if (Http::existsGET('showProfile'))
		{
			$payload .= '<pre></pre><div class="_profiler">';
			$payload .= Prof()->dumpTimers();
			$payload .= QueryHistory::getHTMLTable();
			$payload .= '</div></pre>';
		}
		
		self::closeConnections();
		
		return $payload;
	}
	
	public static function initPoesis()
	{
		Poesis::init();
	}
	
	public static function setOperationController(string $controller)
	{
		self::$options['operationController'] = $controller;
	}
	
	public static function setUseDiffernetSessionForEachRole(bool $bool)
	{
		self::$options['differnetSessionForEachRole'] = $bool;
	}
	
	public static function setBeforeRouteBoot(callable $callable)
	{
		self::$options['beforeRouteBoot'] = $callable;
	}
	
	public static function optExists(string $name)
	{
		return array_key_exists($name, self::$options);
	}
	
	/**
	 * @param string $name
	 * @param mixed  $value - if set value is not UNDEFINDED then its used to sed valie
	 * @return mixed|null
	 */
	public static function opt(string $name, $value = UNDEFINDED)
	{
		if ($value !== UNDEFINDED)
		{
			self::$options[$name] = $value;
		}
		
		return self::$options[$name];
	}
	
	
	private static function closeConnections()
	{
		return true;
	}
}

?>