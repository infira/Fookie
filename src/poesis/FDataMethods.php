<?php

namespace Infira\Fookie\Poesis;

use Infira\Poesis\dr\DataMethods;

class FDataMethods extends DataMethods
{
	/**
	 * Get options for html <select tag
	 *
	 * @param string /resource $queryResultOrSql
	 * @param string $key
	 * @param string $v
	 * @param bool   $firstNull
	 * @param bool   $isExtOptions
	 * @param array  $extraFields - to add extra fields to array
	 * @return array
	 */
	public function options($key = 'ID', $v = 'name', $firstNull = true, $isExtOptions = false, $extraFields = [], $fetchRow = false): array
	{
		$newArray = [];
		if ($firstNull !== true and !checkArray($firstNull) and !is_object($firstNull) and $firstNull !== false)
		{
			$firstNull = urldecode($firstNull);
			if ($isExtOptions)
			{
				$newArray[] = [0, $firstNull];
			}
			else
			{
				$newArray[0] = $firstNull;
			}
		}
		elseif ($firstNull === true)
		{
			if ($isExtOptions)
			{
				$newArray[] = [0, "---"];
			}
			else
			{
				$newArray[0] = "---";
			}
		}
		elseif (checkArray($firstNull))
		{
			$newArray[$firstNull[0]] = $firstNull[1];
		}
		elseif (is_object($firstNull))
		{
			$newArray[$firstNull->value] = $firstNull->label;
		}
		if ($isExtOptions)
		{
			if ($fetchRow == true)
			{
				$this->loop("fetch_row", function ($row) use (&$newArray)
				{
					$newArray[] = $row;
				}, null, false);
			}
			else
			{
				$addFields = array_merge([$key, $v], $extraFields);
				$this->each(function ($row) use (&$newArray, &$addFields)
				{
					$addArr = [];
					foreach ($addFields as $n)
					{
						$addArr[] = $row->$n;
					}
					$newArray[] = $addArr;
				});
			}
			
		}
		else
		{
			$this->each(function ($row) use (&$newArray, &$key, &$v)
			{
				$newArray[Variable::toString($row->$key)] = $row->$v;
			});
		}
		
		return $newArray;
	}
	
	public function getExtOptions($key = 'ID', $v = 'name', $firstNull = true, $extraFields = []): array
	{
		return $this->options($key, $v, $firstNull, true, $extraFields);
	}
	
	/**
	 * Get data as FarrayList
	 * old = getArrayList
	 *
	 * @param string $IDField
	 * @param string $arrayListClassName
	 * @return \Infira\Farray\FarrayList
	 */
	public function getFarrayList($IDField = "ID", $arrayListClassName = "\Infira\Farray\FarrayList"): \Infira\Farray\FarrayList
	{
		$List = new $arrayListClassName($this->getRes()->fetch_all(MYSQLI_ASSOC));
		$List->setIDField($IDField);
		if ($this->rowParserCallback)
		{
			$List->setRowParser($this->rowParserCallback);
		}
		$List->construct();
		
		return $List;
	}
	
	/**
	 * Get data as FarrayNode
	 * old = getArrayListNode
	 *
	 * @return \Infira\Farray\FarrayNode
	 */
	public function getFarrayNode(): \Infira\Farray\FarrayNode
	{
		return $this->getRowAsClass("\Infira\Farray\FarrayNode");
	}
	
	/**
	 * Get data as \Infira\Farray\Callback, each row will be stdClass
	 *
	 * @return \Infira\Farray\Callback
	 */
	public function getFarrayCallback(): \Infira\Farray\Callback
	{
		$List = $this->getAllAsClass("\Infira\Farray\Callback");
		if ($this->rowParserCallback)
		{
			$List->setCallback($this->rowParserCallback, ($this->rowParserArguments ? $this->rowParserArguments : []));
		}
		
		return $List;
	}
	
	/**
	 * Get Data as \Infira\Farray\FarrayObject
	 *
	 * @return \Infira\Farray\FarrayObject
	 */
	public function getFarrayObject(): \Infira\Farray\FarrayObject
	{
		return $this->getRowAsClass("\Infira\Farray\FarrayObject");
	}
}

?>