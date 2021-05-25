<?php

namespace Infira\Fookie\Smurf;

use Infira\Autoloader\Autoloader;

class SmurfAutoloader extends SmurfCommand
{
	private $autoloaderConfigLocations = [];
	
	public function __construct()
	{
		$this->addConfig('silent', 'c');
		parent::__construct('autoloader');
	}
	
	protected function addAutoloaderConfig(string $file, string $prefix = null)
	{
		if ($prefix === null)
		{
			$this->autoloaderConfigLocations[] = $file;
		}
		else
		{
			$this->autoloaderConfigLocations[] = [$file, $prefix];
		}
	}
	
	protected function beforeExecute()
	{
		$this->addAutoloaderConfig(realpath(__DIR__ . '/../') . '/config/autoloader.json', realpath(__DIR__ . '/../') . '/');
	}
	
	protected function runCommand()
	{
		$errors = false;
		if (!$this->isTest())
		{
			Autoloader::$updateFromConsole = true;
		}
		$makedFiles = Autoloader::generateCache($this->autoloaderConfigLocations, 'config/autoloadLocations.php');
		$max        = 1;
		foreach ($makedFiles as $name => $file)
		{
			$max = max($max, strlen($name));
		}
		if ($this->isTest())
		{
			debug($makedFiles);
		}
		else
		{
			foreach ($makedFiles as $name => $file)
			{
				if (substr($file, 0, 14) == 'CONSOLE_ERROR:')
				{
					$errors = true;
					$file   = substr($file, 14);
					$this->error("AutoloaderError:$file");
				}
				else
				{
					$spaces = str_repeat(' ', $max - strlen($name));
					if (!$this->input->getOption('silent'))
					{
						$this->message('<info>generated:' . $name . $spaces . ' </info>' . $file);
					}
				}
			}
		}
		if ($errors)
		{
			$this->blink('Autoloader install failed');
		}
		else
		{
			$this->info('Autoloader installed');
		}
	}
}

?>