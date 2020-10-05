<?php

trait ArrayListPluginManipulation
{
	public function orderBy($field, $desc = FALSE)
	{
		$cmp = function ($a, $b) use ($field)
		{
			return strcmp($a->$field->val(), $b->$field->val());
		};
		
		$arr = $this->getArrayCopy();
		usort($arr, $cmp);
		if (!$desc)
		{
			$arr = array_reverse($arr);
		}
		
		return new ArrayList($arr);
	}
	
	public function distinct($field)
	{
		$distincts = [];
		$newList   = $this->createNewList();
		if ($this->checkArray())
		{
			foreach ($this as $key => $Row)
			{
				if ($Row->exists($field))
				{
					$val = $Row->get($field)->value;
					if (!array_key_exists($val, $distincts))
					{
						$distincts[$val] = $val;
						$newList->add($Row);
					}
				}
			}
			$newList->construct();
		}
		
		return $newList;
	}
	
	public function slice($nr1 = NULL, $nr2 = NULL, $preserveKeys = TRUE)
	{
		/**
		 * @var ArrayList
		 */
		$newArr  = array_slice($this->getArrayCopy(), $nr1, $nr2, $preserveKeys);
		$newList = $this->createNewList(FALSE, $newArr);
		$newList->construct();
		
		return $newList;
	}
	
	function partition($p)
	{
		$list    = $this->getArrayCopy();
		$newList = $this->createNewList();
		$listlen = count($list);
		$partlen = floor($listlen / $p);
		$partrem = $listlen % $p;
		$mark    = 0;
		for ($px = 0; $px < $p; $px++)
		{
			$incr = ($px < $partrem) ? $partlen + 1 : $partlen;
			$part = $this->createNewList();
			$part->setAll(array_slice($list, $mark, $incr));
			$newList->offsetSet($px, $part);
			$mark += $incr;
		}
		
		return $newList;
	}
	
	public function reverse()
	{
		return new $this(array_reverse($this->getArrayCopy()));
	}
	
	public function chunk($nr)
	{
		$NewList = $this->createNewList();
		$chunked = array_chunk($this->getArrayCopy(), $nr);
		foreach ($chunked as $key => $chunk)
		{
			$new = $this->createNewList(UNDEFINDED, $chunk);
			$new->construct();
			$NewList->offsetSet($key, $new);
		}
		
		return $NewList->construct();
	}
	
	/**
	 * Get grouped list
	 *
	 * @param string $name
	 *            - group by field name
	 * @return ArrayList
	 */
	public function group($keyField, $nameField = FALSE)
	{
		$newList = $this->createNewList();
		foreach ($this as $addKey => $item)
		{
			$indexKeyValue = $item->getVal($keyField)->value;
			$name          = "";
			if ($nameField != FALSE)
			{
				$name = $item->getVal($nameField)->value;
			}
			$newList->registerKeyList($indexKeyValue, $name);
			$newList->addToKey($indexKeyValue, $item);
		}
		
		return $newList;
	}
	
	public function constructPages($limit, $perpage, $countAll = 0)
	{
		$this->PagesInfo           = new stdClass();
		$this->PagesInfo->limit    = $limit;
		$this->PagesInfo->perpage  = $perpage;
		$this->PagesInfo->countAll = $countAll;
		$this->PagesInfo->pages    = [];
		for ($i = 1; $i <= $countAll; $i++) //Actually this can be slowed with range function
		{
			$this->PagesInfo->pages[] = $i;
		}
		$this->PagesInfo->pages = array_chunk($this->PagesInfo->pages, $perpage);
		$pages                  = [];
		array_walk($this->PagesInfo->pages, function (&$value, $key) use (&$pages)
		{
			$pages[] = $key + 1;
		});
		$this->PagesInfo->pages = $pages;
		if ($limit == 0)
		{
			$curPage = 1;
		}
		else
		{
			$curPage = ($limit / $perpage) + 1;
		}
		$this->PagesInfo->current = $curPage;
		$count                    = count($pages);
		$this->PagesInfo->count   = $count;
		$this->PagesInfo          = $this->constructPagesInfo($this->PagesInfo, $pages, $count, $curPage, $perpage, $limit);
	}
}
