<?php

/**
 * Class ArrayObjectProps
 * extra usefule methods to handle simple array and stdClass
 */
class ArrayObjectProps extends ArrayObject
{
	
	public function __construct($array = [], $isRecuresive = FALSE)
	{
		if ($isRecuresive)
		{
			foreach ($array as $field => $val)
			{
				if (is_array($val) or is_object($val))
				{
					if (is_array($array))
					{
						$array[$field] = new ArrayObjectProps($val, TRUE);
					}
					elseif (is_object($array))
					{
						$array->$field = new ArrayObjectProps($val, TRUE);
					}
				}
			}
		}
		if (is_null($array))
		{
			$array = [];
		}
		parent::__construct($array, ArrayObject::ARRAY_AS_PROPS);
	}
	
	public function offsetGet($field)
	{
		if (!$this->exists($field))
		{
			alert('Field "' . $field . '" not found');
		}
		
		return parent::offsetGet($field);
	}
	
	/**
	 * Get item
	 *
	 * @param string $field
	 * @param mixed  $returnOnNotFound
	 * @return mixed|null
	 */
	public function get(string $field, $returnOnNotFound = NULL)
	{
		$field = trim($field);
		if (empty($field))
		{
			return $returnOnNotFound;
		}
		if (!$this->exists($field))
		{
			return $returnOnNotFound;
		}
		
		return $this->offsetGet($field);
	}
	
	/**
	 * Set item
	 *
	 * @param string $field
	 * @param mixed  $newVal
	 * @return ArrayObjectProps
	 */
	public function set(string $field, $newVal): ArrayObjectProps
	{
		$this->offsetSet($field, $newVal);
		
		return $this;
	}
	
	/**
	 * Set value via reference
	 *
	 * @param string $field
	 * @param mixed  $value
	 * @return ArrayObjectProps
	 */
	public function setRef(string $field, &$value): ArrayObjectProps
	{
		$this->offsetSet($field, $value);
		
		return $this;
	}
	
	/**
	 * @param string $path - s1>s2>s3 will result array as [s1=>[s2=>[s3=>$value]]]
	 * @param mixed  $value
	 * @return ArrayObjectProps
	 */
	public function setPath(string $path, $value): ArrayObjectProps
	{
		$r = $value;
		foreach (array_reverse(explode(">", $path)) as $k => $field)
		{
			$r = [$field => $r];
		}
		
		$this->set($field, $r[$field]);
		
		return $this;
	}
	
	/**
	 * Append value to existing array
	 *
	 * @param string - $field where to add new value
	 * @param mixed $newVal
	 * @return ArrayObjectProps
	 */
	public function addTo(string $field, $newVal): ArrayObjectProps
	{
		$val   = $this->get($field, [], FALSE);
		$val[] = $newVal;
		
		$this->offsetSet($field, $val);
		
		return $this;
	}
	
	/**
	 * Alias to append
	 *
	 * @param $value
	 * @return ArrayObjectProps
	 */
	public function add($value): ArrayObjectProps
	{
		$this->append($value);
		
		return $this;
	}
	
	/**
	 * Change multiple values to array
	 *
	 * @param array|object $data
	 * @return ArrayObjectProps
	 */
	public function setValues($data): ArrayObjectProps
	{
		if (!Is::isClass($data, "ArrayObjectProps"))
		{
			$d = new ArrayObjectProps($data);
		}
		else
		{
			$d = $data;
		}
		foreach ($d->getIterator() as $k => $v)
		{
			$this->set($k, $v);
		}
		
		return $this;
	}
	
	/**
	 * Flush current array
	 *
	 * @return ArrayObjectProps
	 */
	public function flush(): ArrayObjectProps
	{
		$this->exchangeArray([]);
		
		return $this;
	}
	
	/**
	 * Copy $toKey value from $sourceField value
	 *
	 * @param string $toKey
	 * @param string $sourceKey
	 * @return ArrayObjectProps'
	 */
	public function copy(string $toKey, string $sourceKey): ArrayObjectProps
	{
		$this->set($toKey, $this->get($sourceKey));
		
		return $this;
	}
	
	/**
	 * Rename field
	 *
	 * @param string $toKey
	 * @param string $sourceKey
	 * @return ArrayObjectProps
	 */
	public function rename(string $toKey, string $sourceKey): ArrayObjectProps
	{
		if ($this->exists($sourceKey))
		{
			$this->copy($toKey, $sourceKey);
			$this->delete($sourceKey);
		}
		
		return $this;
	}
	
	/**
	 * Delete item
	 *
	 * @param $field
	 * @return ArrayObjectProps
	 */
	public function delete(string $field): ArrayObjectProps
	{
		if ($this->exists($field))
		{
			$this->offsetUnset($field);
		}
		
		return $this;
	}
	
	/**
	 * Does item exist
	 *
	 * @param string $field
	 * @return bool
	 */
	public function exists(string $field): bool
	{
		return $this->offsetExists($field);
	}
	
	
	/**
	 * Returns true when item value is not empy
	 *
	 * @param string $field
	 * @return bool
	 */
	public function notEmpty(string $field): bool
	{
		return !$this->isEmpty($field);
	}
	
	/**
	 * Returns true when is empty
	 *
	 * @param string $field
	 * @return bool
	 */
	public function isEmpty(string $field): bool
	{
		if (!$this->offsetExists($field))
		{
			return TRUE;
		}
		
		return empty($this->offsetGet($field));
	}
	
	/**
	 * Checks is the item is array and has values
	 *
	 * @param string $field
	 * @return bool
	 */
	public function checkArray(string $field)
	{
		if ($this->offsetExists($field) and checkArray($this->get($field)))
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Get all
	 *
	 * @param bool $getAsStdClass - get all as stcClass
	 * @return array|stdClass
	 */
	public function getAll($getAsStdClass = FALSE)
	{
		if ($getAsStdClass)
		{
			return (object)$this->getArrayCopy();
		}
		
		return $this->getArrayCopy();
	}
	
	/**
	 * Get multiple key values
	 *
	 * @param array $keys
	 * @return array
	 */
	public function getMulti(array $keys = []): array
	{
		$keys = array_flip($keys);
		foreach ($keys as $field => $v)
		{
			$keys[$field] = $this->get($field, NULL);
		}
		
		return $keys;
	}
	
	/**
	 * Does have any values
	 *
	 * @return bool
	 */
	public function ok(): bool
	{
		return ($this->count() > 0) ? TRUE : FALSE;
	}
	
	/**
	 * Get number of elements
	 *
	 * @return int
	 */
	public function size(): int
	{
		return $this->count();
	}
	
	/**
	 * Debug current value
	 *
	 * @return void
	 */
	public function debug()
	{
		debug($this->getArrayCopy());
	}
	
	/**
	 * build http query string from current values or parseStr and then set values
	 *
	 * @param mixed|null $value - if value is NULL then string will be returned
	 * @return $this|string
	 */
	public function parseStr($value = NULL)
	{
		if ($value === NULL)
		{
			return http_build_query($this->getArrayCopy());
		}
		else
		{
			$data = parseStr(urldecode($value));
			if (is_object($data) or is_array($data))
			{
				$this->setValues($data);
			}
			
			return $this;
		}
	}
	
	/**
	 * Iterate items with $callback
	 *
	 * @param callable $callback
	 * @param object   $scope
	 */
	public function each(callable $callback, $scope = NULL)
	{
		foreach ($this->getIterator() as $field => $val)
		{
			callback($callback, $scope, [$val, $field]);
		}
	}
	
	/**
	 * Implode field values with $glue
	 *
	 * @param string $fieldNames
	 * @param string $glue
	 * @return string
	 */
	public function implode(string $fieldNames, string $glue = ",")
	{
		$newValue = [];
		foreach (Variable::toArray($fieldNames) as $field)
		{
			$v = $this->get($field)->val();
			if ($v)
			{
				$newValue[] = $v;
			}
		}
		
		return join($glue, $newValue);
	}
	
	/**
	 * Alias to implode
	 *
	 * @param string $fieldNames
	 * @param string $glue
	 * @return string
	 */
	public function join(string $fieldNames, string $glue = ",")
	{
		return $this->implode($fieldNames, $glue);
	}
}

?>