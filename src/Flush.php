<?php

namespace Infira\Fookie;

use Infira\Cachly\Cachly;

class Flush
{
	public static function all()
	{
		self::cache();
	}
	
	public static function cache()
	{
		if (Cachly::isConfigured(Cachly::FILE))
		{
			Cachly::$Driver->File->flush();
		}
		if (Cachly::isConfigured(Cachly::SESS))
		{
			Cachly::$Driver->Sess->flush();
		}
		if (Cachly::isConfigured(Cachly::DB))
		{
			Cachly::$Driver->Db->flush();
		}
	}
}

?>