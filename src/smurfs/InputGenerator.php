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
		foreach ($this->parser->getPaths() as $path => $epConfig)
		{
			$vars              = new stdClass();
			$vars->namespace   = $this->namespace ? 'namespace ' . $this->namespace . ';' : self::REMOVE_EMPTY_LINE;
			$vars->description = 'Reqest body inputs for enpoint' . $path;
			
			$vars->name = $this->parser->generateRequestClassName($path);
			$vars->path = $this->parser->getSimplePath($path);
			$epConfig   = $epConfig->post ?? $epConfig->get ?? $epConfig->put ?? $epConfig->patch;
			$vars->properties = [];
			$vars->inputs     = [];
			if (isset($epConfig->requestBody))
			{
				$requestBody      = $epConfig->requestBody->content;
				$fk               = array_keys((array)$requestBody)[0];
				$schema           = $requestBody->$fk->schema;
				$this->parseSchema($schema, $vars);
				$vars->inputs     = join("\n", $vars->inputs);
				$vars->properties = join('', $vars->properties);
			}
			$this->makeFile($this->installPath . '/' . $vars->name . '.php', Variable::assign((array)$vars, file_get_contents(__DIR__ . '/' . 'openApiRequestTemplate.txt')));
		}
		$this->afterExecute();
		
		return $this->success();
	}
	
	private function parseSchema(stdClass $schema, stdClass &$vars)
	{
		$ref = '$ref';
		if (isset($schema->allOf))
		{
			foreach ($schema->allOf as $subSchema)
			{
				if (isset($subSchema->$ref))
				{
					$this->parseSchema($this->parser->getRefValue($subSchema->$ref), $vars);
				}
				elseif (isset($subSchema->properties))
				{
					$this->parseSchema($subSchema, $vars);
				}
			}
		}
		elseif (isset($schema->$ref))
		{
			$this->parseSchema($this->parser->getRefValue($schema->$ref), $vars);
		}
		elseif (isset($schema->properties))
		{
			foreach ($schema->properties as $property => $propertyCnf)
			{
				$value = 'null';
				$enum  = isset($propertyCnf->enum) ? 'One of values ' . join(', ', $propertyCnf->enum) : self::REMOVE_EMPTY_LINE;
				
				if (isset($propertyCnf->$ref))
				{
					$propertyCnf = $this->parser->getRefValue($propertyCnf->$ref);
				}
				$description = $propertyCnf->description ?? $enum;
				if ($propertyCnf->type == 'string')
				{
					$value = isset($propertyCnf->default) ? "'" . $propertyCnf->default . "'" : "''";
				}
				elseif ($propertyCnf->type == 'integer')
				{
					$value = $propertyCnf->default ?? "null";
				}
				elseif ($propertyCnf->type == 'array')
				{
					$value = isset($propertyCnf->default) ? "'" . $propertyCnf->default . "'" : "[]";
				}
				$type = [];
				if (isset($propertyCnf->nullable))
				{
					$type[] = 'can be NULL';
				}
				elseif (isset($propertyCnf->enum))
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
				$vars->inputs[] = '
	/**
	 * ' . $description . '
	 * @var ' . $propertyCnf->type . ' ' . join(', ', $type) . '
	 */
	public $' . $property . ' = ' . $value . ';';
				
				$propertyConfigArr   = [];
				$propertyConfigArr[] = '\'type\'=>\'' . $propertyCnf->type . '\'';
				$propertyConfigArr[] = '\'required\'=> ' . (isset($propertyCnf->required) ? 'true' : 'false') . '';
				if (isset($propertyCnf->default))
				{
					$propertyConfigArr[] = '\'default\'=>\'' . $propertyCnf->default . '\'';
				}
				if (isset($propertyCnf->format))
				{
					$propertyConfigArr[] = '\'format\'=>\'' . $propertyCnf->format . '\'';
				}
				if (isset($propertyCnf->enum))
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
		}
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