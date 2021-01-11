<?php

namespace Infira\Fookie\facade;
class Http extends \Infira\Utils\Http
{
	public static function acceptJSON(): bool
	{
		if (!isset($_SERVER['HTTP_ACCEPT']))
		{
			return false;
		}
		if (strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
		{
			return true;
		}
		
		return false;
	}
}