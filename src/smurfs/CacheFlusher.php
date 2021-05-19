<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Infira\Fookie\facade\Cache;
use Symfony\Component\Console\Input\InputOption;
use Infira\Fookie\Flush;

class CacheFlusher extends SmurfCommand
{
	private $configs = [];
	
	public function __construct(string $name = null)
	{
		$this->addConfig('cache', 'c', 'flushCache');
		parent::__construct($name);
	}
	
	protected function addConfig(string $name, string $shortcut, string $method)
	{
		$this->configs[$name] = ['method' => $method, 'shortcut' => $shortcut];
	}
	
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$c = $this->setName('flush');
		foreach ($this->configs as $name => $config)
		{
			$c = $c->addOption($name, $config['shortcut'], InputOption::VALUE_NONE);
		}
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->output = &$output;
		$this->beforeExecute();
		set_time_limit(7200);
		
		$found = false;
		foreach ($this->configs as $name => $config)
		{
			if ($input->getOption($name))
			{
				$method = $config['method'];
				$this->$method();
				$found = true;
				break;
			}
		}
		if (!$found)
		{
			foreach ($this->configs as $name => $config)
			{
				$method = $config['method'];
				$this->$method();
			}
		}
		
		
		return $this->success();
	}
	
	protected function flushCache()
	{
		Cache::init();
		Flush::cache();
		$this->info('Cachly flushed');
	}
}

?>