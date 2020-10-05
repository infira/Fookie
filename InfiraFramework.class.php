<?php

use Infira\Poesis\DbQueryHistory;
use Infira\Utils\ClassFarm;

class InfiraFramework
{
	/**
	 * Displays the eRaama result
	 *
	 * @return null
	 */
	public static function boot()
	{
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