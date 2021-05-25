<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;

abstract class SmurfCommand extends Command
{
	/**
	 * @var OutputInterface
	 */
	protected $output;
	protected $input;
	private   $configs = [];
	
	public function __construct(string $name = null)
	{
		parent::__construct($name);
	}
	
	protected function addConfig(string $name, string $shortcut, string $method = null)
	{
		$this->configs[$name] = ['method' => $method, 'shortcut' => $shortcut];
	}
	
	protected function configure(): void
	{
		$c = $this;
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
		set_time_limit(7200);
		$this->output = &$output;
		$this->input  = &$input;
		$this->beforeExecute();
		$this->runCommand();
		$found = false;
		foreach ($this->configs as $name => $config)
		{
			if ($input->getOption($name))
			{
				if ($config['method'] !== null)
				{
					$method = $config['method'];
					$this->$method();
					$found = true;
				}
			}
		}
		if (!$found)
		{
			foreach ($this->configs as $config)
			{
				if ($config['method'] !== null)
				{
					$method = $config['method'];
					$this->$method();
				}
			}
		}
		$this->afterExecute();
		
		return $this->success();
	}
	
	protected function isTest()
	{
		return (defined('IS_TEST'));
	}
	
	protected function error(string $msg, string $prefix = '')
	{
		if ($this->isTest())
		{
			echo("<div>$prefix<span style='color:red'>$msg</span></div>");
		}
		else
		{
			$this->output->writeln($prefix . "<error>$msg</error>");
		}
	}
	
	protected function info(string $msg, string $prefix = '')
	{
		if ($this->isTest())
		{
			echo("<div>$prefix<span style='color:green'>$msg</span></div>");
		}
		else
		{
			$this->output->writeln($prefix . "<info>$msg</info>");
		}
	}
	
	protected function message(string $msg)
	{
		if ($this->isTest())
		{
			echo("<div>$msg</div>");
		}
		else
		{
			$this->output->writeln($msg);
		}
	}
	
	protected function blink($msg)
	{
		$outputStyle = new OutputFormatterStyle('red', '#ff0', ['bold', 'blink']);
		$this->output->getFormatter()->setStyle('fire', $outputStyle);
		$this->output->writeln("<fire>$msg</>");
	}
	
	protected function success(): int
	{
		return Command::SUCCESS;
	}
	
	protected function beforeExecute() { }
	
	protected function afterExecute()
	{
		//void
	}
	
	protected function runCommand() { }
}

?>