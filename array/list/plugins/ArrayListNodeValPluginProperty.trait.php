<?php

trait ArrayListNodeValPluginProperty
{
	/**
	 * Alias to length
	 */
	public function len(): int
	{
		return $this->length();
	}
	
	/**
	 * Returns this class value string length
	 *
	 * @return int
	 */
	public function length(): int
	{
		return strlen($this->value);
	}
	
	/**
	 * Is regular expression match
	 *
	 * @param $pattern
	 * @return bool
	 */
	public function match($pattern): bool
	{
		return Is::match($pattern, $this->value);
	}
	
	/**
	 * count value
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->value);
	}
	
	/**
	 * Get image width
	 *
	 * @return int
	 */
	public function width()
	{
		if (!$this->ok())
		{
			return 0;
		}
		if (!file_exists($this->value))
		{
			return 0;
		}
		$size = getimagesize($this->value);
		
		return $size[0];
	}
	
	/**
	 * Get image height
	 *
	 * @return int
	 */
	public function height()
	{
		if (!$this->ok())
		{
			return 0;
		}
		if (!file_exists($this->value))
		{
			return 0;
		}
		$size = getimagesize($this->value);
		
		return $size[1];
	}
	
	
}