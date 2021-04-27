<?php

namespace Infira\Fookie\OpenAPI;

use AppConfig;

class JSONParser
{
	/**
	 * @var \stdClass
	 */
	private $config;
	
	public function __construct(string $swaggerFile)
	{
		if (!file_exists($swaggerFile))
		{
			alert("Swagger file not found");
		}
		$this->config        = json_decode(file_get_contents($swaggerFile));
		$this->config->paths = (array)$this->config->paths;
	}
	
	public function pathExists(string $path): bool
	{
		return array_key_exists($path, $this->config->paths);
	}
	
	public function getPaths(): array
	{
		return $this->config->paths;
	}
	
	public function getConfig(): \stdClass
	{
		return $this->config;
	}
	
	public function getRefValue(string $ref): \stdClass
	{
		$ref = str_replace('#/', '', $ref);
		
		$output = $this->config;
		foreach (explode('/', $ref) as $cmp)
		{
			//addExtraErrorInfo('$cmp', $cmp);
			//addExtraErrorInfo('$output', $output);
			$output = $output->$cmp;
		}
		
		return (object)(array)$output;
	}
	
	public function generateRequestClassName(string $apiPath)
	{
		$ex = explode('/', substr($apiPath, 1));
		array_walk($ex, function (&$part)
		{
			$part = \Infira\Fookie\facade\Variable::ucFirst($part);
		});
		$name = join('', $ex) . 'Request';
		
		return preg_replace('/\{.+?}/m', '', $name);
	}
}

?>