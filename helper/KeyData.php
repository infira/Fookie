<?php

namespace Infira\Fookie\helper;
class KeyData
{
	public static function get($name)
	{
		return Rm::once($name, function ($name)
		{
			$Db       = new TKeyData();
			$Db->name = $name;
			$obj      = $Db->select()->getObject();
			if (is_object($obj))
			{
				if ($obj->unSerialize)
				{
					$obj->data = unserialize($obj->data);
				}
				$value = $obj->data;
			}
			else
			{
				$value = UNDEFINDED;
			}
			
			return $value;
		});
	}
	
	public static function set($name, $data)
	{
		$serialize = 0;
		if (is_array($data) or is_object($data))
		{
			$serialize = 1;
			$data      = serialize($data);
		}
		$Db = new TKeyData();
		$Db->name($name);
		$Db->data($data);
		$Db->unSerialize($serialize);
		$Db->replace();
	}
}