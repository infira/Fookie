<?php

namespace Infira\Fookie\OpenAPI;

use Http;

class EndpointValidator
{
	private $inputType = [];
	
	/**
	 * @var JSONParser
	 */
	private $parser;
	
	private $path = '';
	
	public function __construct(string $altoRouterPath, string $apiConfigPath)
	{
		$this->parser = new JSONParser($apiConfigPath);
		$this->path   = preg_replace('/\[.+:/m', '[', $altoRouterPath);
		$this->path   = str_replace(['[', ']'], ['{', '}'], $this->path);
		$this->path = '/' . $this->path;
	}
	
	
	public function isValid(): bool
	{
		return $this->parser->pathExists($this->path);
	}
	
	public function getPaths(): array
	{
		return $this->parser->getPaths();
	}
	
	public function getInputOptions(string $swaggerPath): array
	{
		return $this->parser->getInputOptions($swaggerPath, Http::getRequestMethod());
	}
	
	public function setInputType(string $input, string $type)
	{
		$this->inputType[$input] = $type;
	}
	
	
	public function getClassName()
	{
		return $this->parser->generateRequestClassName($this->path);
	}
	
	public function getRequest(string $namespace = ''): Request
	{
		$namespace = $namespace ? $namespace . '\\' : $namespace;
		$cn        = $namespace . $this->getClassName();
		
		return new $cn();
	}
}