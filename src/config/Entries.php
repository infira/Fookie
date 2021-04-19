<?php

namespace Infira\Fookie\config;

class Entries
{
	public static $entries = [];
	
	public static $defaultValues = [];
	
	/**
	 * Get client config
	 *
	 * @param string $name
	 * @return mixed
	 */
	public final static function get(string $name)
	{
		if (array_key_exists($name, self::$entries))
		{
			return self::$entries[$name];
		}
		elseif (array_key_exists($name, self::$defaultValues))
		{
			return self::$defaultValues[$name];
		}
		
		return null;
	}
	
	public final static function setGetVar(string $name, $value = null, string $setDefinition = null)
	{
		if ($value !== null)
		{
			self::$entries[$name] = $value;
			if ($setDefinition)
			{
				define($setDefinition, $value);
			}
		}
		
		return self::get($name);
	}
	
	public final static function addDefaultValue(string $name, $value)
	{
		self::$defaultValues[$name] = $value;
	}
	
	public final static function isSetted(string $name): bool
	{
		if (array_key_exists($name, self::$entries))
		{
			if (self::$entries[$name] != null)
			{
				return true;
			}
		}
		
		return false;
	}
}

?>