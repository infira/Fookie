<?php

namespace Infira\Fookie;

class Log
{
	/**
	 * Log event
	 *
	 * @param string $title
	 * @param mixed  $content
	 * @return int - returns log ID
	 */
	public static function make(string $title, $content): int
	{
		$Db = new \TLog();
		$Db->event($title);
		$userID = 0;
		if (defined("__USER_ID"))
		{
			$userID = __USER_ID;
		}
		$Db->userID = $userID;
		if (isSerializable($content))
		{
			$Db->isSerialized = 1;
			$content          = serialize($content);
		}
		else
		{
			$Db->isSerialized(0);
			alert("Cant serialize");
		}
		$Db->content($content);
		$Db->ts->now();
		$Db->ip = getUserIP();
		$Db->insert();
		
		return $Db->getLastSaveID();
	}
	
	/**
	 * @param int|string $ID log ID or string 'last"
	 * @return string
	 */
	public static function getContent($ID): string
	{
		$Db = new \TLog();
		if ($ID === 'last')
		{
			$Db->orderBy("ID DESC");
			$Db->limit(1);
		}
		else
		{
			$Db->ID($ID);
		}
		$Obj = $Db->select()->getObject();
		if (is_object($Obj))
		{
			if ($Obj->isSerialized == 1)
			{
				debug(unserialize($Obj->content));
			}
			else
			{
				debug($Obj->content);
			}
		}
	}
}

?>