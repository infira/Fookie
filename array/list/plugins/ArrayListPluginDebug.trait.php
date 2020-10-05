<?php

trait ArrayListPluginDebug
{
	public function getCleanArrayCopy()
	{
		$node = [];
		foreach ($this->getIterator() as $key => $val)
		{
			if (is_object($val))
			{
				if (method_exists($val, "val"))
				{
					$node[$key] = $val->val();
				}
				elseif (method_exists($val, "dump"))
				{
					$node[$key] = $val->dump();
				}
				else
				{
					$node[$key] = $val;
				}
			}
			else
			{
				$node[$key] = $val;
			}
		}
		
		return $node;
	}
	
	public function debug()
	{
		debug($this->getArrayCopy());
	}
	
	public function dump()
	{
		return dump($this->getArrayCopy());
	}
	
	public function debugClean()
	{
		debugClean($this->getCleanArrayCopy());
		exit;
	}
	
	public function dumpClean()
	{
		return dump($this->getCleanArrayCopy());
	}
}
