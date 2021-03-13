<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Infira\Fookie\Autoloader;

class SmurfAutoloader extends SmurfCommand
{
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('autoloader');
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->output = &$output;
		
		if (!$this->isTest())
		{
			Autoloader::$updateFromConsole = true;
		}
		ob_start();
		Autoloader::update(getcwd() . '/config/autoloader.json', 'config/autoloadLocations.php');
		$res = ob_get_contents();
		ob_end_clean();
		$errors = false;
		if ($res)
		{
			$lines = explode("\n", $res);
			foreach ($lines as $line)
			{
				if (substr($line, 0, 14) == 'CONSOLE_ERROR:')
				{
					$errors = true;
					$line   = substr($line, 14);
					$this->error("AutoloaderError:$line");
				}
				else
				{
					$this->message($line);
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