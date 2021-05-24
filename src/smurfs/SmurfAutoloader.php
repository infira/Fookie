<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Infira\Autoloader\Autoloader;
use Symfony\Component\Console\Input\InputOption;

class SmurfAutoloader extends SmurfCommand
{
	private $autoloaderConfigLocations = [];
	
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
	
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('autoloader')
			->addOption('silent', 's', InputOption::VALUE_NONE, 'Silent');;
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->output = &$output;
		$this->addAutoloaderConfig(realpath(__DIR__ . '/../') . '/config/autoloader.json', realpath(__DIR__ . '/../') . '/');
		$this->beforeExecute();
		
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
					if (!$input->getOption('silent'))
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
		
		return $this->success();
	}
}

?>