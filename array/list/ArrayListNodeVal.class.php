<?php
Autoloader::voidOnNotExists("ArrayListNodeValPlugins");
Autoloader::voidOnNotExists("ArrayListNodeVal");
if (!trait_exists("ArrayListNodeValPlugins"))
{
	trait ArrayListNodeValPlugins
	{
	}
}

abstract class AbstractArrayListNodeVal
{
	private $TYPE = "list";
	use ArrayListNodeValPlugins;
	use LibsMethodExtensions;
	use ArrayListNodeValPluginIs;
	use ArrayListNodeValPluginProperty;
	use ArrayListNodeValPluginIs;
	use ArrayListNodeValPluginModificator;
	private $value; //actual current value
	
	/**
	 * Original value wich was setted by __constructor
	 *
	 * @var mixed
	 */
	protected $origVal = FALSE;
	
	protected $field = "";
	
	/**
	 * @var callable
	 */
	public $valueParser      = NULL;
	public $valueParserScope = NULL;
	
	/**
	 * @var ArrayListNode
	 */
	public $ListNode;
	
	public $takeNewValue = FALSE;
	
	public function __get($name)
	{
		alert('ArrayListNodeVal->__get : You are tring to get variable <B>"' . $name . '</B>" but it doesn\'t exits in ' . get_class($this) . ' class');
	}
	
	public function __call($method, $args)
	{
		alert("ArrayListNodeVal method($method) is not callable ");
	}
	
	public function __construct($field, $val, &$ListNode = FALSE)
	{
		if ($ListNode !== FALSE AND !is_object($ListNode))
		{
			alert("List node should be object");
		}
		if (Is::isClass($val, "ArrayListNodeVal"))
		{
			alert('$val cannot be instance of ArrayListNodeVal');
		}
		$this->field       = $field;
		$this->value       = $val;
		$this->origVal     = $val;
		$this->tagsCreated = 0;
		if ($ListNode)
		{
			$this->setListNode($ListNode);
		}
	}
	
	public function __toString()
	{
		return $this->offsetGet();
	}
	
	
	private function offsetGet()
	{
		$value = $this->value;
		if (is_callable($this->valueParser))
		{
			$value = callback($this->valueParser, $this->valueParserScope, [$value]);
		}
		if ($this->ListNode AND $this->field)
		{
			if ($this->ListNode->hasFieldValueParser($this->field))
			{
				$value = $this->ListNode->parseFieldValue($this->field, $value);
			}
		}
		
		return $value;
	}
	
	public function val()
	{
		return $this->offsetGet();
	}
	
	/**
	 * Take new value to self instead of returning $newValue
	 */
	public function take()
	{
		$this->takeNewValue = TRUE;
		
		return $this;
	}
	
	/**
	 * Generates new cloned this value
	 *
	 * @param mixed $newVal
	 * @param bool  $returnThis
	 * @return $this
	 */
	private function newValue($newVal, $returnThis = TRUE)
	{
		if (!$returnThis)
		{
			return $newVal;
		}
		if ($this->takeNewValue)
		{
			$newThis = $this;
		}
		else
		{
			$newThis = clone $this;
		}
		$newThis->set($newVal);
		$newThis->takeNewValue = FALSE;
		
		return $newThis;
	}
	
	/**
	 * Gets the original what was setted during construction
	 *
	 * @return mixed
	 */
	public function getOrigValue()
	{
		return $this->origVal;
	}
	
	/**
	 * What is the current value key at $this->List
	 *
	 * @return string
	 */
	public function getField()
	{
		return $this->field;
	}
	
	/**
	 * @param mixed $newValue
	 * @return $this
	 */
	public function set($newValue)
	{
		$this->value = $newValue;
		
		return $this;
	}
	
	/**
	 * Relatet $this to listNode
	 *
	 * @param $Node
	 */
	public function setListNode(&$Node)
	{
		$this->ListNode = &$Node;
	}
	
	/**
	 * Set parser during getting value
	 *
	 * @param callable $parser
	 * @param object   $scope - optional
	 */
	public function setValueParser(callable $parser, $scope = NULL)
	{
		$this->valueParser      = $parser;
		$this->valueParserScope = $scope;
	}
	
	public function debug()
	{
		return debug($this->value);
	}
	
	public function dump()
	{
		return dump($this->value);
	}
}

if (!class_exists("ArrayListNodeVal"))
{
	class ArrayListNodeVal extends AbstractArrayListNodeVal
	{
	}
}
?>