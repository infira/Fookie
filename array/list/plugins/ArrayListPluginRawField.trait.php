<?php

trait ArrayListPluginRawField
{
	private $rawFields = [];
	
	public function addRawField($field)
	{
		foreach (Variable::toArray($field) as $field)
		{
			$this->rawFields[$field] = $field;
		}
	}
	
	public function isRawField($field)
	{
		return isset($this->rawFields[$field]);
	}
	
	/**
	 * Get $field value without converting it to ArrayListNodeVal
	 *
	 * @param $field
	 * @return mixed
	 */
	private function getRawValue($field)
	{
		return parent::offsetGet($field);
	}
}
