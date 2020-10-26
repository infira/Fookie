<?php

trait PoesisDataGettersExtendor
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
	public function options($key = 'ID', $v = 'name', $firstNull = true, $isExtOptions = false, $extraFields = [], $fetchRow = false)
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
				});
			}
			else
			{
				$addFields = array_merge([$key, $v], $extraFields);
				$this->loop("fetch_object", function ($row) use (&$newArray, &$addFields)
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
			$this->loop("fetch_object", function ($row) use (&$newArray, &$key, &$v)
			{
				$newArray[Variable::toString($row->$key)] = $row->$v;
			});
		}
		
		return $newArray;
	}
	
	public function getExtOptions($key = 'ID', $v = 'name', $firstNull = true, $extraFields = [])
	{
		return $this->options($key, $v, $firstNull, true, $extraFields);
	}
	
	public function eachSmarty($_smarty_tpl, $smartyFunctionToCall, $convertRowToArrayListNode = false)
	{
		$key    = 0;
		$res    = $this->getRes();
		$output = "";
		while ($row = $res->fetch_object())
		{
			$pRow = $this->rowParser($row, [$key, $row]);//parsed Row
			if ($pRow !== SKIP_)
			{
				$key++;
				if ($pRow === BREAK_)
				{
					break;
				}
				if ($pRow === CONTINUE_)
				{
					continue;
				}
			}
			if ($convertRowToArrayListNode)
			{
				$pRow = new ArrayListNode($pRow);
			}
			$output .= $_smarty_tpl->ext->_tplFunction->callTemplateFunction($_smarty_tpl, $smartyFunctionToCall, ["Row" => $pRow], true);
		}
		
		return $output;
	}
	
	
	/**
	 * Get data as ArrayList
	 *
	 * @param string $IDField
	 * @return ArrayList
	 */
	public function getArrayList($IDField = "ID", $arrayListClassName = "ArrayList")
	{
		$List = new $arrayListClassName($this->getRes()->fetch_all(MYSQLI_ASSOC));
		$List->setIDField($IDField);
		if ($this->rowParserCallback)
		{
			$List->setRowParser($this->rowParserCallback, $this->rowParserScope);
		}
		$List->construct();
		
		return $List;
	}
	
	/**
	 * Get data as ArrayListNode
	 *
	 * @return ArrayListNode
	 */
	public function getArrayListNode()
	{
		return $this->getRowAsClass("ArrayListNode");
	}
	
	/**
	 * Get data as ArraylList, each row will be stdClass
	 *
	 * @return ArrayCallback
	 */
	public function getArrayCallback()
	{
		$List = $this->getAllAsClass("ArrayCallback");
		if ($this->rowParserCallback)
		{
			$List->setCallback($this->rowParserCallback, ($this->rowParserArguments ? $this->rowParserArguments : []), $this->rowParserScope);
		}
		
		return $List;
	}
	
	/**
	 * Get Data as ArrayObjectProps
	 *
	 * @return ArrayObjectProps
	 */
	public function getArrayObjectProps()
	{
		return $this->getRowAsClass("ArrayObjectProps");
	}
}

?>