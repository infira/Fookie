<?php

namespace Infira\Fookie;

use Infira\Poesis\orm\Model;

class Log
{
	private static $userID = 0;
	
	public static function make(string $title, $content): int
	{
		$db = self::getModel();
		$db->title($title);
		$db->userID(self::$userID);
		if (isSerializable($content))
		{
			$db->isSerialized = 1;
			$content          = serialize($content);
		}
		else
		{
			$db->isSerialized(0);
			alert("Cant serialize");
		}
		$db->content($content);
		$db->ip(getUserIP());
		$db->insert();
		
		return $db->getLastSaveID();
	}
	
	public static function getContent($ID): ?string
	{
		$db = self::getModel();
		if ($ID === 'last')
		{
			$db->orderBy("ID DESC");
			$db->limit(1);
		}
		else
		{
			$db->ID($ID);
		}
		$Obj = $db->select()->getObject();
		if (is_object($Obj))
		{
			if ($Obj->isSerialized == 1)
			{
				return unserialize($Obj->content);
			}
			else
			{
				return $Obj->content;
			}
		}
		
		return null;
	}
	
	public static function setUserID(int $ID)
	{
		self::$userID = $ID;
	}
	
	private static function getModel(): Model
	{
		$model = \AppConfig::logModel();
		
		return new $model();
	}
}

?>