<?php
Autoloader::voidOnNotExists("ArrayListNodePlugins");
Autoloader::voidOnNotExists("ArrayListNode");
if (!trait_exists("ArrayListNodePlugins"))
{
	trait ArrayListNodePlugins
	{
	}
}

abstract class AbstractArrayListNode extends ArrayObjectProps
{
	private $TYPE = "node";
	use ArrayListNodePlugins;
	use ArrayListPluginConstructableField;
	use ArrayListPluginRawField;
	use ArrayListPluginDebug;
	use ArrayListNodePluginList;
	use ArrayListPluginFieldParser;
	
	/**
	 * @var ArrayList
	 */
	protected $List;
	
	/**
	 * @param array $array
	 * @param bool  $List
	 */
	public function __construct($array = [], &$List = FALSE)
	{
		$this->List = &$List;
		parent::__construct(Variable::toArray($array), ArrayObject::ARRAY_AS_PROPS);
	}
	
	public $_index = 0;
	
	public function prev()
	{
		return $this->List->getAt($this->_index - 1);
	}
	
	public function next()
	{
		return $this->List->getAt($this->_index + 1);
	}
	
	public function index()
	{
		return new ArrayListNodeVal(FALSE, $this->_index, $this);
	}
	
	/**
	 * Get parentList
	 * Returns null when list doest not exist
	 *
	 * @return ArrayList|null
	 */
	public function getList()
	{
		return $this->List;
	}
	
	/**
	 * Setter
	 *
	 * @param string $field
	 * @param string $value
	 * @return ArrayListNode
	 */
	public function __get($field)
	{
		alert("asdad");
	}
	
	/**
	 * Setter
	 *
	 * @param string $field
	 * @param string $value
	 * @return ArrayListNode
	 */
	public function __set(string $field, $value = '')
	{
		if ($this->exists($field))
		{
			if ($this->isRawField($field))
			{
				parent::offsetSet($field, $value);
			}
			elseif ($this->isSettedAsNodeVal($field) or $this->isSettedAsNode($field))
			{
				$this->get($field)->set($value);
			}
			else
			{
				parent::offsetSet($field, $value);
			}
		}
		else
		{
			parent::offsetSet($field, $value);
		}
		
		return $this;
	}
	
	/**
	 * Get variable
	 *
	 * @param string $field
	 * @param mixed  $returnOnNotFound - return this value on not found, defaults to false
	 * @return mixed
	 */
	public function get(string $field = "value", $returnOnNotFound = NULL)
	{
		if (!$this->exists($field))
		{
			return $returnOnNotFound;
		}
		
		return $this->offsetGet($field);
	}
	
	/**
	 * Get value as ArrayListNodeVal
	 *
	 * @param string $field
	 * @param mixed  $returnOnNotFound - returns when $field is not found
	 * @return ArrayListNodeVal
	 */
	public function getVal(string $field, $returnOnNotFound = NULL)
	{
		if (!$this->exists($field))
		{
			return new ArrayListNodeVal($field, $returnOnNotFound, $this);
		}
		if ($this->isRawField($field))
		{
			return new ArrayListNodeVal($field, $this->getRawValue($field), $this);
		}
		
		return $this->offsetGet($field);
	}
	
	
	public function offsetGet($field)
	{
		if ($this->isConstructabileField($field))
		{
			return $this->getConstructedFieldClass($field);
		}
		elseif ($this->isRawField($field))
		{
			return $this->getRawValue($field);
		}
		$finalValue = $this->getFinalValue($field);
		if (!Is::isClass($finalValue, "ArrayListNodeVal"))
		{
			$finalValue = new ArrayListNodeVal($field, $finalValue, $this);
			/*
			if ($this->hasFieldValueParser($field))
			{
				$finalValue->setValueParser($this->fieldValueParser[$field]->parser, $this->fieldValueParser[$field]->scope);
			}
			*/
		}
		parent::offsetSet($field, $finalValue);
		
		return $finalValue;
	}
	
	private function getFinalValue(string $field)
	{
		if (method_exists($this, "_____GetFieldValue"))
		{
			$value = $this->_____GetFieldValue($field);
		}
		else
		{
			$value = parent::offsetGet($field);
		}
		
		/*
		if ($this->hasFieldValueParser($field))
		{
			$value = $this->parseFieldValue($field, $value);
		}
		 */
		
		return $value;
	}
	
	/**
	 * Implode field values with $glue
	 *
	 * @param string $fieldNames
	 * @param string $glue
	 * @return ArrayListNodeVal
	 */
	public function implode(string $fieldNames, string $glue = ",")
	{
		return new ArrayListNodeVal(FALSE, parent::implode($fieldNames));
	}
	
	/**
	 * Alias to implode
	 *
	 * @param string $fieldNames
	 * @param string $glue
	 * @return ArrayListNodeVal
	 */
	public function join(string $fieldNames, string $glue = ",")
	{
		return $this->implode($fieldNames, $glue);
	}
}

if (!class_exists("ArrayListNode"))
{
	class ArrayListNode extends AbstractArrayListNode
	{
	}
}
?>