<?php

namespace Infira\Fookie\OpenAPI;

class EndpointValidator
{
	/**
	 * @var JSONParser
	 */
	private $parser;
	
	private $path = '';
	
	public function __construct(string $altoRouterPath, string $apiConfigPath)
	{
		$this->parser = new JSONParser($apiConfigPath);
		$this->path   = str_replace('[i:', '[integer:', $altoRouterPath);
		$this->path   = str_replace('[*:', '[string:', $this->path);
		$this->path   = str_replace(['[', ']'], ['{', '}'], $this->path);
		$this->path   = '/' . $this->path;
	}
	
	
	public function isValid(): bool
	{
		return $this->parser->pathExists($this->path);
	}
	
	public function getPaths(): array
	{
		return $this->parser->getPaths();
	}
	
	public function getRequest(string $namespace = ''): Request
	{
		$namespace = $namespace ? $namespace . '\\' : $namespace;
		$cn        = $namespace . $this->parser->generateRequestClassName($this->path);
		
		return new $cn();
	}
}

?>