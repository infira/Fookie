<?php

namespace Infira\Fookie;

use Infira\Farray\FarrayObject;
use Infira\Utils\Fix;

class Vars extends FarrayObject
{
	/**
	 * If $value == '__ifo_undefined__' THEN it returns value rather set
	 *
	 * @param string $field
	 * @param mixed  $value
	 * @return mixed
	 */
	public function setGet(string $field, $value = UNDEFINDED, string $fixWidth = null)
	{
		if ($value === UNDEFINDED)
		{
			return $this->get($field);
		}
		else
		{
			if ($fixWidth)
			{
				$value = Fix::valueWith($value, $fixWidth);
			}
			$this->set($field, $value);
			
			return $value;
		}
	}
}
?>