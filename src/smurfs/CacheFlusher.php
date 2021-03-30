<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Infira\Fookie\facade\Variable;
use Db;
use Infira\Utils\File;
use Infira\Fookie\facade\Cache;
use Infira\Cachly\Cachly;

class CacheFlusher extends SmurfCommand
{
	private $vars = [];
	
	private $updateFile    = null;
	private $phpScriptPath = null;
	
	public function addVar(string $name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	protected function setUpdateFile(string $file)
	{
		$this->updateFile = $file;
	}
	
	protected function setPhpScriptPath(string $path)
	{
		$this->phpScriptPath = $path;
	}
	
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('flush')
			->addOption('cache', 'c', InputOption::VALUE_NONE, 'Reset all')
			->addOption('smarty', 's', InputOption::VALUE_NONE, 'Flush');
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
		
		if ($input->getOption('cache'))
		{
			$this->flushCache();
		}
		elseif ($input->getOption('smarty'))
		{
			$this->flushCompiledSmartyTemplates();
		}
		else
		{
			$this->flushCache();
			$this->flushCompiledSmartyTemplates();
		}
		
		
		return $this->success();
	}
	
	function flushCache()
	{
		Cache::init();
		if (Cache::isConfigured(Cachly::FILE))
		{
			Cache::$Driver->File->flush();
		}
		if (Cache::isConfigured(Cachly::SESS))
		{
			Cache::$Driver->Sess->flush();
		}
		if (Cache::isConfigured(Cachly::DB))
		{
			Cache::$Driver->Db->flush();
		}
		$this->info('Cachly flushed');
	}
	
	public function flushCompiledSmartyTemplates()
	{
		\Tpl::Smarty()->clearAllCache();
		\Tpl::Smarty()->clearCompiledTemplate();
		
		$this->info('smarty templates flushed');
	}
}

?>