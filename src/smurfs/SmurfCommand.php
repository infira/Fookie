<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Command\Command;

class SmurfCommand extends Command
{
	/**
	 * @var OutputInterface
	 */
	protected $output;
	protected $input;
	
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
	
	protected function beforeExecute()
	{
		//void
	}
	
	protected function afterExecute()
	{
		//void
	}
}