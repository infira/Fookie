<?php

trait ArrayListNodePluginList
{
	/**
	 * Set sub listNode
	 *
	 * @param string $field
	 * @param mixed  $data
	 * @return ArrayListNode
	 */
	public function setAsList($field, $data = [])
	{
		$this->addRawField($field);
		$List = new ArrayList($data);
		$List->construct();
		parent::offsetSet($field, $List);
		
		return $this;
	}
	
	private function isList($field)
	{
		return Is::isClass(self::offsetGet($field), "ArrayList");
	}
	
	/**
	 * Set variable as setAsListNode
	 *
	 * @param string $field
	 * @param mixed  $data
	 * @return ArrayListNode
	 */
	public function setAsListNode($field, $data)
	{
		$this->addRawField($field);
		$data = ($data) ? $data : [];
		parent::offsetSet($field, new ArrayListNode($data));
		
		return $this;
	}
	
	
	private function isListNode($field)
	{
		return Is::isClass(self::offsetGet($field), "ArrayListNode");
	}
	
	private function isListNodeVal($field)
	{
		return Is::isClass(parent::offsetGet($field), "ArrayListNodeVal");
	}
}
