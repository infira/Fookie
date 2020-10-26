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
	public final static function get($name)
	{
		if (array_key_exists($name, self::$entries))
		{
			return self::$entries[$name];
		}
		elseif (array_key_exists($name, self::$defaultValues))
		{
			return self::$defaultValues[$name];
		}
		
		return false;
	}
	
	public final static function setGetVar($name, $value = null, $fixWidth = false, $setDefinition = false)
	{
		if ($value !== null)
		{
			if ($fixWidth !== false)
			{
				foreach (explode(",", $fixWidth) as $fixW)
				{
					if ($fixW == "float")
					{
						$value = floatval($value);
					}
					elseif ($fixW == "int")
					{
						$value = intval($value);
					}
					elseif ($fixW == "bool")
					{
						$value = (boolean)$value;
					}
					elseif ($fixW == "object")
					{
						$value = (object)$value;
					}
					elseif ($fixW == "function")
					{
						$value = $value;
					}
					elseif ($fixW == "array")
					{
						if (is_string($value))
						{
							$ex     = explode(",", $value);
							$newArr = [];
							if (count($ex) > 0)
							{
								foreach ($ex as $key => $val)
								{
									$val = trim($val);
									if ($val)
									{
										$newArr[$key] = $val;
									}
								}
							}
							$value = $newArr;
						}
					}
					elseif ($fixW == "lower")
					{
						$value = strtolower($value);
					}
					else
					{
						$value = trim($value);
					}
				}
			}
			self::$entries[$name] = $value;
			if ($setDefinition !== false)
			{
				define($setDefinition, $value);
			}
			
			return null;
		}
		
		return self::get($name);
	}
	
	public final static function addDefaultValue($name, $value)
	{
		self::$defaultValues[$name] = $value;
	}
	
	public final static function isSetted($name)
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