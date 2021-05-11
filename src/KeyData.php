<?php

namespace Infira\Fookie;

use Infira\Fookie\facade\Rm;
use Db;

class KeyData
{
	public static function exists(string $name): bool
	{
		return Rm::once(['KeyData->exists', $name], function () use (&$name)
		{
			return Db::TKeyData()->name($name)->hasRows();
		});
	}
	
	
	public static function get(string $name)
	{
		return Rm::magic(function () use (&$name)
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
				
				return $obj->data;
			}
			
			return null;
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
		$Db = new \TKeyData();
		$Db->name($name);
		$Db->data($data);
		$Db->unSerialize($serialize);
		$Db->replace();
	}
}