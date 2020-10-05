<?php

trait ArrayListNodeValPluginIs
{
	/**
	 * Is in array
	 *
	 * @param mixed $val
	 * @return bool
	 */
	public function in($val)
	{
		return in_array($this->value, Variable::toArray($val));
	}
	
	/**
	 * Is not in
	 *
	 * @param mixed $val
	 * @return bool
	 */
	public function notIn($val)
	{
		return !$this->in($val);
	}
	
	/**
	 * Is value
	 *
	 * @param mixed $val
	 * @param bool  $compareStrict use === for comparison
	 * @return bool
	 */
	public function is($val, bool $compareStrict = FALSE)
	{
		if ($compareStrict)
		{
			return $this->value === $val;
		}
		
		return $this->value == $val;
	}
	
	/**
	 * Is not $val
	 *
	 * @param mixed $val
	 * @return bool
	 */
	public function isnt($val)
	{
		return $this->value != $val;
	}
	
	/**
	 * Alias to inst
	 *
	 * @param mixed $val
	 * @see $this->isnt()
	 * @return bool
	 */
	public function isNot($val)
	{
		return $this->isnt($val);
	}
	
	/**
	 * Alias to inst
	 *
	 * @param mixed $val
	 * @see $this->isnt()
	 * @return bool
	 */
	public function not($val)
	{
		return $this->isnt($val);
	}
	
	/**
	 * Is value between
	 *
	 * @param int|float $from
	 * @param int|float $to
	 * @return bool
	 */
	public function between($from, $to)
	{
		if ($this->value >= $from and $this->value <= $to)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Check is $value a faile and does it exists
	 *
	 * @return bool
	 */
	public function exists()
	{
		if ($this->ok())
		{
			return file_exists($this->value);
		}
		
		return FALSE;
	}
	
	/**
	 * Is value not empty
	 *
	 * @return bool
	 */
	public function ok(): bool
	{
		return !empty($this->value);
	}
	
	/**
	 * Is value not empty
	 *
	 * @return bool
	 */
	public function notOk(): bool
	{
		return !$this->ok();
	}
	
	/**
	 * Is html empty or not
	 *
	 * @param string $voidTags - in case ohtml tags to void on stripping, see http://php.net/manual/en/function.strip-tags.php
	 * @return bool
	 */
	public function isHTMLOk(string $voidTags = ""): bool
	{
		$val = $this->value;
		if (is_string($this->value))
		{
			$val = trim(Variable::htmlToText($val, $voidTags));
		}
		
		return !empty($val);
	}
	
	/**
	 * Alias to ok()
	 *
	 * @see $this->ok()
	 * @return bool
	 */
	public function notEmpty(): bool
	{
		return $this->ok();
	}
	
	/**
	 * Alias to notOk()
	 *
	 * @see $this->notOk()
	 * @return bool
	 */
	public function empty(): bool
	{
		return $this->notOk();
	}
	
	/**
	 * Is array and has values
	 *
	 * @return bool
	 */
	public function checkArray()
	{
		return (checkArray($this->value));
	}
	
	/**
	 * Is bigger than $to
	 *
	 * @param int|float $to
	 * @return bool
	 */
	public function isBigger($to)
	{
		return ($this->value > $to);
	}
	
	/**
	 * Is bigger or equal to $to
	 *
	 * @param int|float $to
	 * @return bool
	 */
	public function isBiggerEq($to)
	{
		return ($this->value >= $to);
	}
	
	/**
	 * Is smaller than $to
	 *
	 * @param int|float $to
	 * @return bool
	 */
	public function isSmaller($to)
	{
		return ($this->value < $to);
	}
	
	/**
	 * Is smaller or equal to $to
	 *
	 * @param int|float $to
	 * @return bool
	 */
	public function isSmallerEq($to)
	{
		return ($this->value <= $to);
	}
	
	/**
	 * Check if this value contains string
	 *
	 * @param string $str            - what to check
	 * @param bool   $convertToLower - use strtolower on check
	 * @return bool
	 */
	public function contains($str, $convertToLower = FALSE): bool
	{
		$val = $this->value;
		if ($convertToLower)
		{
			$str = Variable::toLower($str);
			$val = Variable::toLower($val);
		}
		
		return (strpos($val, $str) === FALSE) ? FALSE : TRUE;
	}
	
	
}