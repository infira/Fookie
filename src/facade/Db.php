<?php

namespace Infira\Fookie\facade;

/**
 * Class Db
 * @method static null close()
 * @method static \Infira\Poesis\dr\DataMethods dr(string $query)
 * @method static mysqli_result query(string $query)
 * @method static bool realQuery(string $query)
 * @method static void multiQuery(string $query, callable|bool $callback)
 * @method static mixed escape($data, bool $checkArray = false)()
 * @method static void fileQuery(string $fileLocation, array $vars = [])
 * @method static void complexQuery(string $query, array $vars = [])
 * @method static int getLastInsertID()
 * @method static object getLastQueryInfo()
 * @method static void debugLastQuery()
 * @method static bool setVar(string $name, bool $value = false)
 * @method static mixed getVar(string $name)
 */
class Db extends \Infira\Utils\Facade
{
	use \PoesisModelShortcut;
	
	private static $connections = [];
	
	public static function getInstanceConfig(): array
	{
		return ["name" => "Database", "constructor" => function ()
		{
			return self::connection();
		}];
	}
	
	
	public static function connection()
	{
		return \Infira\Poesis\ConnectionManager::default();
	}
}

?>