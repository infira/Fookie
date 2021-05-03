<?php

namespace Infira\Fookie\OpenAPI;

use Variable;
use Is;
use Date;

abstract class Request
{
	protected $properties = [];
	protected $payload    = [];
	protected $path       = '';
	
	
	public function getProperties(): array
	{
		return $this->properties;
	}
	
	public function validate(array $payload): \stdClass
	{
		$output        = new \stdClass();
		$output->error = null;
		foreach ($this->getProperties() as $property => $config)
		{
			if (property_exists($config, 'default') and !array_key_exists($property, $payload))
			{
				$payload[$property] = $config->default;
			}
			if ($config->required and !array_key_exists($property, $payload))
			{
				$output->error = "$property does not exists in request";
				
				return $output;
			}
			if (array_key_exists($property, $payload))
			{
				$Valid = $this->validateType($property, $payload[$property]);
				if ($Valid->error)
				{
					$output->error = $Valid->error;
					
					return $output;
				}
				$payload[$property] = $Valid->value;
				$this->$property    = $payload[$property];
			}
		}
		$output->payload = $payload;
		
		return $output;
	}
	
	/**
	 * @param string $property
	 * @param mixed  $value
	 * @return \stdClass properties(value,error = null)
	 */
	public function validateType(string $property, $value): \stdClass
	{
		$Output        = new \stdClass();
		$Output->error = null;
		$requiredType  = $this->properties[$property]->type;
		if ($requiredType == 'int')
		{
			$requiredType = 'integer';
		}
		$type = gettype($value);
		if ($requiredType == 'integer' and $type == 'string' and Variable::toString($tmpValue = intval($value)) === $value)
		{
			$value = $tmpValue;
			$type  = 'integer';
		}
		elseif ($requiredType == 'bool' and $type === 'string' and ($value === '1' or $value === '0'))
		{
			$value = $value === '1';
			$type  = 'bool';
		}
		elseif ($requiredType == 'bool' and $type === 'integer' and ($value === 1 or $value === 0))
		{
			$value = $value === 1;
			$type  = 'bool';
		}
		
		if ($requiredType == 'number' and !in_array($type, ['integer', 'float']))
		{
			$Output->error = "Property $property must be $requiredType, $type was given";
			
			return $Output;
		}
		if ($requiredType != $type)
		{
			$Output->error = "Property $property must be $requiredType, $type was given";
			
			return $Output;
		}
		$min = $this->properties[$property]->min ?? null;
		if ($min and $value < $min)
		{
			$Output->error = "Input $property value minimum is $min, $value was givven";
			
			return $Output;
		}
		$max = $this->properties[$property]->max ?? null;
		if ($max and $value > $max)
		{
			$Output->error = "Input $property value maximum is $max, $value was givven";
			
			return $Output;
		}
		
		if (isset($this->properties[$property]->enum) and !in_array($value, $this->properties[$property]->enum))
		{
			$Output->error = "Input $property value must be one off (" . join(',', $this->properties[$property]->enum) . ')';
			
			return $Output;
		}
		$format = $this->properties[$property]->format ?? null;
		if ($format == 'email' and !Is::email($value))
		{
			$Output->error = "Input $property value must be correct email";
			
			return $Output;
		}
		if ($format == 'dateTime' and Date::toSqlDateTime($value) == $value)
		{
			$Output->error = "Input $property value must be in date format YYYY-dd-mm HH:ii:ss";
			
			return $Output;
		}
		$Output->value = $value;
		
		return $Output;
	}
	
	public function exists(string $property): bool
	{
		return property_exists($this, $property);
	}
	
	public function get(string $property, $onNotFound): bool
	{
		
		return property_exists($this, $property);
	}
	
	public function getDefault(string $prperty)
	{
		if (!array_key_exists($prperty, $this->properties))
		{
			return null;
		}
		if (!property_exists($this->properties[$prperty], 'default'))
		{
			return null;
		}
		
		return $this->properties[$prperty]->default;
	}
}

?>