<?php

namespace IFW\facade;

use Infira\Utils\ClassFarm;

class Facade
{
	public static function __callStatic($method, $args)
	{
		return self::getInstance(static::getInstanceConfig())->$method(...$args);
	}
	
	protected static function getInstance($instaceConfig)
	{
		Prof()->startTimer("Facade->getInstance");
		if (!ClassFarm::exists($instaceConfig["ns"]))
		{
			ClassFarm::add($instaceConfig["ns"], $instaceConfig["instance"]);
		}
		$instance = ClassFarm::get($instaceConfig["ns"]);
		Prof()->stopTimer("Facade->getInstance");
		
		return $instance;
	}
}