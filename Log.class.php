<?php

namespace Infira\Fookie;
/**
 * This class handles users and php errors
 */
class Log
{
	
	/**
	 * Make log
	 *
	 * @param string $title
	 * @param mixed  $content
	 * @param bool   $isError
	 * @return int|object
	 */
	private static function doMake(string $title, $content, bool $isError = false)
	{
		$Db        = new TLog();
		$Db->title = $title;
		$userID    = 0;
		if (defined("__USER_ID"))
		{
			$userID = __USER_ID;
		}
		$Db->userID       = $userID;
		$Db->isSerialized = 0;
		if (is_array($content) or is_object($content))
		{
			$Db->isSerialized = 1;
			$content          = serialize($content);
		}
		$Db->content    = "a";
		$Db->insertDate = Date::nowSqlDateTime();
		$Db->isHandled  = false;
		$Db->isError    = ($isError) ? 1 : 0;
		$Db->ip         = getUserIP();
		$Db->save();
		
		return $Db->getLastSaveID();
	}
	
	
	/**
	 * Log event
	 *
	 * @param string $title
	 * @param string $content
	 * @return int|object
	 */
	public static function make(string $title, string $content)
	{
		return self::doMake($title, $content, false);
	}
	
	
	/**
	 * Log error
	 *
	 * @param string $title
	 * @param string $content
	 * @return int|object
	 */
	public static function makeError(string $title, string $content)
	{
		return self::doMake($title, $content, true);
	}
}

?>