<?php

namespace Infira\Fookie;

use Infira\Fookie\facade\Rm;

class KeyData
{
	public static function get($name)
	{
		return Rm::once($name, function () use (&$name)
		{
			$Db       = new \TKeyData();
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
		if (isSerializable($data))
		{
			$serialize = 1;
			$data      = serialize($data);
		}
		else
		{
			alert("Cant serialize");
		}
		$Db = new TKeyData();
		$Db->name($name);
		$Db->data($data);
		$Db->unSerialize($serialize);
		$Db->replace();
	}
}