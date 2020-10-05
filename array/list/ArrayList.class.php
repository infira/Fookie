<?php
Autoloader::voidOnNotExists("ArrayListExtendor");
Autoloader::voidOnNotExists("ArrayList");
if (!trait_exists("ArrayListExtendor"))
{
	trait ArrayListExtendor
	{
	}
}

abstract class AbstractArrayList extends ArrayIterator
{
	private $TYPE = "list";
	use ArrayListExtendor;
	use ArrayListPluginConstructableField;
	use ArrayListPluginManipulation;
	use ArrayListPluginRawField;
	use ArrayListPluginFieldParser;
	use ArrayListPluginDebug;
	private $className              = "ArrayList";
	private $listKeys               = [];
	private $firstKey               = FALSE;
	private $lastKey                = FALSE;
	private $count                  = 0;
	private $keyNr                  = 0;
	public  $ListInfo;
	public  $PagesInfo;
	private $listNodeClassName      = "ArrayListNode";
	private $allowedListNodeClasses = ["ArrayListNode", "ArrayList"];
	private $IDFIeld;
	
	public function __construct(array $array = [], $listNodeClassName = "ArrayListNode")
	{
		$this->listNodeClassName = $listNodeClassName;
		$this->className         = get_class($this);
		parent::__construct($array);
		$this->ListInfo             = new stdClass();
		$this->ListInfo->countAll   = 0;
		$this->ListInfo->pagesCount = 0;
		$this->ListInfo->next       = FALSE;
		$this->ListInfo->prev       = FALSE;
		$this->ListInfo->pages      = [];
		$this->ListInfo->last       = FALSE;
	}
	
	public function setIDField(string $field)
	{
		$this->IDFIeld = $field;
	}
	
	public function construct()
	{
		if ($this->checkArray())
		{
			if ($this->IDFIeld)
			{
				$newArray       = [];
				$this->listKeys = [];
				array_map(function ($Row) use (&$newArray)
				{
					$field                  = $this->IDFIeld;
					$this->listKeys[]       = $Row[$field];
					$newArray[$Row[$field]] = $Row;
				}, $this->getArrayCopy());
				$this->setAll($newArray);
			}
			else
			{
				$this->listKeys = array_keys($this->getArrayCopy());
			}
			$this->firstKey = $this->listKeys[0];
			$this->lastKey  = array_key_last($this->listKeys);
		}
		if (method_exists($this, "afterConstruct"))
		{
			$this->afterConstruct();
		}
		
		return $this;
	}
	
	/**
	 * Get ListNode at $index
	 *
	 * @param string $index
	 * @return ArrayListNode
	 */
	public function offsetGet($index)
	{
		if (!$this->exists($index))
		{
			alert("Field not found");
		}
		if ($this->isConstructabileField($index))
		{
			return $this->getConstructedFieldClass($index);
		}
		
		$row = $this->parseRow(parent::offsetGet($index));
		if (is_array($row) OR Is::isClass($row, "stdClass"))
		{
			$row = $this->createListNode($row);
		}
		elseif (is_object($row))
		{
			$class = get_class($row);
			if (!in_array($class, $this->allowedListNodeClasses))
			{
				addExtraErrorInfo("ClassName", $class);
				alert("ArrayList parse value unkonwn class $class");
			}
		}
		/*
		if ($this->hasFieldValueParser($index))
		{
			$storedValue = $this->parseFieldValue($index, $storedValue);
		}
		*/
		parent::offsetSet($index, $row);
		
		return $row;
	}
	
	public function addAllowedListNodeClass($classes)
	{
		$this->allowedListNodeClasses = array_merge($this->allowedListNodeClasses, Variable::toArray($classes));
	}
	
	protected function createListNode($value)
	{
		return new $this->listNodeClassName($value, $this);
	}
	
	/**
	 * @param string $name
	 * @param array  $array
	 * @return ArrayList
	 */
	public function createNewList(string $name, $array = [])
	{
		return new $this->className($name, $array);
	}
	
	public function checkArray()
	{
		return $this->ok();
	}
	
	public function size()
	{
		return $this->count();
	}
	
	public function ok()
	{
		return ($this->count() > 0) ? TRUE : FALSE;
	}
	
	public function flush()
	{
		$this->setAll([]);
		
		return $this;
	}
	
	/**
	 * Exhcnage current storeage
	 *
	 * @param array $arr
	 */
	public function setAll($arr = [])
	{
		parent::__construct($arr);
	}
	
	/**
	 * Does the item exist
	 *
	 * @param string $index
	 * @return bool
	 */
	public function exists(string $index)
	{
		return $this->offsetExists($index);
	}
	
	/**
	 * Add item to storage
	 *
	 * @param object|array $item
	 * @param string       $addFieldValueAsKey
	 * @return number
	 */
	public function add($item, string $addFieldValueAsKey = "")
	{
		$this->count++;
		if (empty($item))
		{
			alert("ArrayListAbstract add item cant be empty");
		}
		if (Is::isClass($item, $this->listNodeClassName))
		{
			$Node = $item;
		}
		else
		{
			$Node = $this->createListNode($item);
		}
		if ($addFieldValueAsKey === FALSE)
		{
			$addKey = $this->keyNr;
		}
		else
		{
			$addKey = $Node->get($addFieldValueAsKey)->value;
		}
		$this->keyNr++;
		$this->offsetSet($addKey, $Node);
		
		return $addKey;
	}
	
	/**
	 * Alias to add
	 *
	 * @param mixed $value
	 */
	public function append($value)
	{
		$this->add($value);
	}
	
	/**
	 * Add Rows to list
	 *
	 * @param array $data
	 */
	public function addRows($data)
	{
		foreach ($data as $v)
		{
			$this->add($v);
		}
	}
	
	/**
	 * Get first row
	 *
	 * @param mixed $returnOnNotFound
	 * @return mixed|null
	 */
	public function first($returnOnNotFound = NULL)
	{
		return $this->get($this->firstKey, $returnOnNotFound);
	}
	
	/**
	 * Get last row
	 *
	 * @param mixed $returnOnNotFound
	 * @return mixed|null
	 */
	public function last($returnOnNotFound = NULL)
	{
		return $this->get($this->lastKey, $returnOnNotFound);
	}
	
	
	/**
	 * get item at $index
	 *
	 * @param string $index
	 * @param mixed  $returnOnNotFound
	 * @return mixed|null
	 */
	public function get(string $index, $returnOnNotFound = NULL)
	{
		if (!$this->exists($index))
		{
			return $returnOnNotFound;
		}
		
		return $this->offsetGet($index);;
	}
	
	public function current()
	{
		return $this->get($this->key(), NULL);
	}
	
	
	/**
	 * Get random item
	 *
	 * @return mixed
	 */
	public function random()
	{
		return $this->get(array_rand($this->listKeys, 1));
	}
	
	private $__DATA_FILTER_FIELD = FALSE;
	
	private $__DATA_FILTER_VALUE = FALSE;
	
	private function doFilter($v)
	{
		$f = $this->__DATA_FILTER_FIELD;
		$v = $v->$f;
		if (is_object($v))
		{
			$v = $v->val();
		}
		
		return $v == $this->__DATA_FILTER_VALUE;
	}
	
	public function filter($index = "value", $value)
	{
		$this->__DATA_FILTER_FIELD = $index;
		$this->__DATA_FILTER_VALUE = $value;
		
		/**
		 * @var ArrayList
		 */
		$newArr  = array_filter($this->getArrayCopy(), [$this, "doFilter"]);
		$newList = $this->createNewList(FALSE, $newArr);
		$newList->construct();
		
		return $newList;
	}
	
	/**
	 * Find value in each row
	 *
	 * @param string $field
	 * @param        $value
	 * @return bool|ArrayList
	 */
	public function findByFieldValue(string $field, $value)
	{
		foreach ($this->getIterator() as $Row)
		{
			if ($Row->get($field)->val() == $value)
			{
				return $Row;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Collect each row field value
	 *
	 * @param string $field
	 * @return array
	 */
	public function getFieldValues(string $field)
	{
		$outout = [];
		if ($this->ok())
		{
			foreach ($this->getIterator() as $Row)
			{
				$outout[] = $Row->get($field)->value;
			}
		}
		
		return $outout;
	}
	
	/**
	 * Sum each row $field value
	 *
	 * @param $field
	 * @return float|int
	 */
	public function sumFields($field)
	{
		return array_sum($this->getFieldValues($field));
	}
	
	public function count()
	{
		return $this->count;
	}
	
	private $rowParser = [];
	
	public function setRowParser(callable $parser, $scope = FALSE)
	{
		$this->rowParser['parser'] = $parser;
		$this->rowParser['scope']  = $scope;
	}
	
	public function hasRowParser(): bool
	{
		return isset($this->rowParser['parser']);
	}
	
	public function parseRow($row)
	{
		return callback($this->rowParser['parser'], $this->rowParser['scope'], [$row]);
	}
}

if (!class_exists("ArrayList"))
{
	class ArrayList extends AbstractArrayList
	{
	}
}
?>