<?php

trait ArrayListPluginConstructableField
{
	private $constructableFieldClasses = [];
	
	private function getNamespace($name)
	{
		return "ArrayConstructableField_$name";
	}
	
	public function addConstructableFieldClass($name, $className)
	{
		$this->constructableFieldClasses[$name] = (object)["className" => $className, "isConstructed" => FALSE];
	}
	
	public function isConstructabileField($name)
	{
		return isset($this->constructableFieldClasses[$name]);
	}
	
	public function getConstructedFieldClass($name)
	{
		return ClassFarm::instance($this->getNamespace($name), $this->constructableFieldClasses[$name]->className);
	}
}
