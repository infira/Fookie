<?php

class ArrayCallback extends ArrayIterator
{
	
	private $callback;
	private $scope;
	private $callbackExtraParams = [];
	
	public function __construct($value = [])
	{
		parent::__construct($value);
	}
	
	public function setCallback(callable $callback, array $callbackExtraParams = [], $scope = NULL)
	{
		$this->callback            = $callback;
		$this->scope               = $scope;
		$this->callbackExtraParams = $callbackExtraParams;
	}
	
	public function each($callback, array $extraParams = [], $scope = NULL)
	{
		foreach ($this as $key => $row)
		{
			$res = $this->rowCallback($row, $key, $callback, $extraParams, $scope);
			if ($res === BREAK_)
			{
				break;
			}
		}
		
		return $this;
	}
	
	public function current()
	{
		return $this->rowCallback(parent::current(), parent::key(), NULL);
	}
	
	private function rowCallback($row, $key, $callback, array $extraParams = [], $scope = NULL)
	{
		$row = (object)$row;
		if ($this->callback)
		{
			$row = callback($this->callback, $this->scope, array_merge([$row, $key], $this->callbackExtraParams));
		}
		if ($callback)
		{
			$row = callback($callback, $scope, array_merge([$row, $key], $extraParams));
		}
		if (is_null($row))
		{
			alert("callback reulst cannot be NULL");
		}
		
		return $row;
	}
	
	/**
	 * Alias to append
	 *
	 * @param $value
	 */
	public function add($value)
	{
		$this->append($value);
	}
	
	public function checkArray()
	{
		return ($this->count() > 0);
	}
	
	/**
	 * Alias to offsetGet
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->offsetGet($key);
	}
	
	/**
	 * Get first item of array
	 *
	 * @param bool $returnOnNotFound
	 * @return bool|mixed
	 */
	public function first($returnOnNotFound = FALSE)
	{
		$k = $this->count() - 1;
		if ($this->exists($k))
		{
			return $this->offsetGet($k);
		}
		
		return $returnOnNotFound;
	}
	
	/**
	 * Alias to offsetSet
	 *
	 * @param string $key
	 * @param mixed  $newVal
	 * @return mixed
	 */
	public function set($key, $newVal)
	{
		$this->offsetSet($key, $newVal);
	}
	
	/**
	 * Alias to offsetUnset
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function delete($key)
	{
		$this->offsetUnset($key);
	}
	
	/**
	 * Alias to offsetExists
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function exists($key)
	{
		return $this->offsetExists($key);
	}
	
	/**
	 * Debug values
	 */
	public function debug()
	{
		debug($this->getArrayCopy());
	}
	
}

?>