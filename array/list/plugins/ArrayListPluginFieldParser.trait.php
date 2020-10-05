<?php

trait ArrayListPluginFieldParser
{
	private $fieldValueParser = [];
	
	public function addFieldValueParser(string $field, callable $parser, $scope = FALSE)
	{
		if ($this->TYPE == "node")
		{
			$this->List->addFieldValueParser($field, $parser, $scope);
		}
		else
		{
			if ($this->isRawField($field))
			{
				alert("Cannot add row parset on rawField");
			}
			$this->fieldValueParser[$field] = (object)["parser" => $parser, "scope" => $scope];
		}
	}
	
	public function hasFieldValueParser(string $field): bool
	{
		if ($this->TYPE == "node")
		{
			return $this->List->hasFieldValueParser($field);
		}
		else
		{
			return (isset($this->fieldValueParser[$field]));
		}
	}
	
	public function getFieldValueParser(string $field)
	{
		if ($this->TYPE == "node")
		{
			return $this->List->getFieldValueParser($field);
		}
		else
		{
			return $this->fieldValueParser[$field];
		}
	}
	
	public function parseFieldValue(string $field, $value)
	{
		$Parser = $this->getFieldValueParser($field);
		
		return callback($Parser->parser, $Parser->scope, [$value, $field]);
	}
	
}
