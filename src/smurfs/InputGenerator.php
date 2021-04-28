<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Infira\Fookie\facade\Variable;
use stdClass;
use File;
use Dir;
use Infira\Fookie\OpenAPI\JSONParser;

class InputGenerator extends SmurfCommand
{
	const REMOVE_EMPTY_LINE = '[REMOVE_EMPTY_LINE]';
	private $swaggerJsonPath = '';
	private $installPath     = '';
	private $namespace       = '';
	
	/**
	 * @var JSONParser;
	 */
	private $parser;
	
	public function setSwaggerJsonPath(string $swaggerJsonPath): void
	{
		$this->swaggerJsonPath = $swaggerJsonPath;
	}
	
	public function setInstallPath(string $installPath): void
	{
		$this->installPath = $installPath;
	}
	
	public function setNamespace(string $namespace): void
	{
		$this->namespace = $namespace;
	}
	
	
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('inputs');
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->output = &$output;
		$this->input  = &$input;
		
		$this->beforeExecute();
		$this->parser = new JSONParser($this->swaggerJsonPath);
		
		Dir::flush($this->installPath);
		$ref = '$ref';
		foreach ($this->parser->getPaths() as $path => $epConfig)
		{
			$vars              = new stdClass();
			$vars->path        = $path;
			$vars->namespace   = $this->namespace ? 'namespace ' . $this->namespace . ';' : self::REMOVE_EMPTY_LINE;
			$vars->description = 'Reqest body inputs for enpoint' . $path;
			
			$vars->name       = $this->parser->generateRequestClassName($path);
			$epConfig         = $epConfig->post ?? $epConfig->get ?? $epConfig->put ?? $epConfig->patch;
			$vars->properties = [];
			$vars->inputs     = [];
			if (isset($epConfig->requestBody))
			{
				$requestBody = $epConfig->requestBody->content;
				$fk          = array_keys((array)$requestBody)[0];
				$schema      = $requestBody->$fk->schema;
				$properties  = $this->getProperties(clone $schema, [], $path);
				foreach ($properties as $property => $propertyCnf)
				{
					$value = 'null';
					$enum  = isset($propertyCnf->enum) ? 'One of values ' . join(', ', $propertyCnf->enum) : self::REMOVE_EMPTY_LINE;
					if (isset($propertyCnf->$ref))
					{
						$propertyCnf = $this->parser->getRefValue($propertyCnf->$ref);
					}
					$description = $propertyCnf->description ?? $enum;
					if ($propertyCnf->type == 'string' and property_exists($propertyCnf, 'default'))
					{
						$value = "'" . $propertyCnf->default . "'";
					}
					elseif ($propertyCnf->type == 'integer' and property_exists($propertyCnf, 'default'))
					{
						$value = $propertyCnf->default;
					}
					//elseif ($propertyCnf->type == 'array' and property_exists($propertyCnf, 'default'))
					//{
					//	$value = property_exists($propertyCnf, 'default') ? "'" . $propertyCnf->default . "'" : "[]";
					//}
					elseif ($propertyCnf->type == 'boolean' and property_exists($propertyCnf, 'default'))
					{
						$value = $propertyCnf->default ? 'true' : 'false';
					}
					$type = [];
					if (isset($propertyCnf->nullable))
					{
						$type[] = 'can be NULL';
					}
					if (property_exists($propertyCnf, 'enum'))
					{
						$enum = $propertyCnf->enum;
						array_walk($enum, function (&$item)
						{
							if (is_string($item))
							{
								$item = "'" . $item . "'";
							}
						});
						$type[] = 'can be of [' . join(',', $enum) . ']';
					}
					if (property_exists($propertyCnf, 'minimum'))
					{
						$type[] = 'min=' . $propertyCnf->minimum;
					}
					if (property_exists($propertyCnf, 'maximum'))
					{
						$type[] = 'max=' . $propertyCnf->maximum;
					}
					$vars->inputs[] = '
	/**
	 * ' . addslashes($description) . '
	 * @var ' . $propertyCnf->type . ' ' . join(', ', $type) . '
	 */
	public $' . $property . ' = ' . $value . ';';
					
					$propertyConfigArr   = [];
					$propertyConfigArr[] = '\'type\'=>\'' . $propertyCnf->type . '\'';
					$propertyConfigArr[] = '\'required\'=> ' . (isset($propertyCnf->required) ? 'true' : 'false') . '';
					if (property_exists($propertyCnf, 'default'))
					{
						$default = $propertyCnf->default;
						if (is_bool($propertyCnf->default))
						{
							$default = $propertyCnf->default ? 'true' : 'false';
						}
						elseif (is_string($propertyCnf->default))
						{
							$default = "'" . $propertyCnf->default . "'";
						}
						$propertyConfigArr[] = '\'default\'=>' . $default;
					}
					if (property_exists($propertyCnf, 'format'))
					{
						$propertyConfigArr[] = '\'format\'=>\'' . $propertyCnf->format . '\'';
					}
					if (property_exists($propertyCnf, 'minimum'))
					{
						$propertyConfigArr[] = '\'min\'=>' . $propertyCnf->minimum;
					}
					if (property_exists($propertyCnf, 'maximum'))
					{
						$propertyConfigArr[] = '\'max\'=>' . $propertyCnf->maximum;
					}
					if (property_exists($propertyCnf, 'enum'))
					{
						$enum = $propertyCnf->enum;
						array_walk($enum, function (&$item)
						{
							if (is_string($item))
							{
								$item = "'" . $item . "'";
							}
						});
						$propertyConfigArr[] = '\'enum\'=>[' . join(',', $enum) . ']';
					}
					$vars->properties[] = '
		$this->properties[\'' . $property . '\'] = (object)[' . join(', ', $propertyConfigArr) . '];';
				}
				
				$vars->inputs     = join("\n", $vars->inputs);
				$vars->properties = join('', $vars->properties);
			}
			$this->makeFile($this->installPath . '/' . $vars->name . '.php', Variable::assign((array)$vars, file_get_contents(__DIR__ . '/' . 'openApiRequestTemplate.txt')));
		}
		$this->afterExecute();
		
		return $this->success();
	}
	
	private function getProperties(stdClass $schema, array $properties, $path): array
	{
		$ref = '$ref';
		if (isset($schema->allOf))
		{
			foreach ($schema->allOf as $subSchema)
			{
				if (isset($subSchema->$ref))
				{
					$properties = array_merge($this->getProperties($this->parser->getRefValue($subSchema->$ref), $properties, $path), $properties);
				}
				elseif (isset($subSchema->properties))
				{
					$properties = array_merge($this->getProperties($subSchema, $properties, $path), $properties);
				}
			}
		}
		elseif (isset($schema->$ref))
		{
			$properties = array_merge($this->getProperties($this->parser->getRefValue($schema->$ref), $properties, $path), $properties);
		}
		else//if (isset($schema->properties))
		{
			$properties = array_merge((array)$schema->properties, $properties);
		}
		
		return $properties;
	}
	
	private function makeFile(string $fileName, $content): void
	{
		File::delete($fileName);
		$newLines = [];
		foreach (explode("\n", $content) as $line)
		{
			if (strpos($line, self::REMOVE_EMPTY_LINE) === false)
			{
				$newLines[] = $line;
			}
		}
		File::create($fileName, join("\n", $newLines), "w+", 0777);
		$this->message('<info>generated input: </info>' . $fileName);
	}
}

?>