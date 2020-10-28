<?php

namespace Infira\Fookie;

use Infira\Poesis\DbQueryHistory;
use Infira\Fookie\facade\Session;
use Infira\Fookie\request\Route;
use Infira\Fookie\request\Payload;
use Infira\Fookie\facade\Http;
use Infira\Fookie\facade\Cache;
use Infira\Poesis\Poesis;
use Autoloader;

class Fookie
{
	/**
	 * Displays the eRaama result
	 *
	 * @return null
	 */
	public static function boot()
	{
		Autoloader::voidOnNotExists('PoesisDataGettersExtendor');
		Autoloader::voidOnNotExists('PoesisDataGettersExtendor2');
		Autoloader::voidOnNotExists('PoesisConnectionExtendor');
		Autoloader::voidOnNotExists('PoesisModelExtendor');
		Autoloader::init();
		
		\AppConfig::finalize();
		Poesis::init();
		Poesis::setDefaultConnection(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
		Cache::init();
		Session::init();
		Route::init();
		Payload::init();
		Route::handle();
		$payload = Payload::getOutput();
		
		if (Http::existsGET('showProfile'))
		{
			$payload .= '<pre></pre><div class="_profiler">';
			$payload .= Prof()->dumpTimers();
			$payload .= DbQueryHistory::getHTMLTable();
			$payload .= '</div></pre>';
		}
		
		self::closeConnections();
		
		return $payload;
	}
	
	
	private static function closeConnections()
	{
		return true;
	}
}

?>