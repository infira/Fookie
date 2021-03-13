<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use File;
use Db;
use Path;
use Infira\Utils\Dir;

class DbViews extends SmurfCommand
{
	private $dbFiles     = ['views' => [], 'triggers' => []];
	private $voidDbFiles = ['views' => [], 'triggers' => []];
	
	protected function addViewPath(string $path)
	{
		if (!is_dir($path))
		{
			$this->error("Must be corret path($path)");
			
			return false;
		}
		$this->addFiles([$path], 'views');
	}
	
	protected function addViewFile(string $view)
	{
		if (!file_exists($view))
		{
			$this->error("View($view) does not exists");
			
			return false;
		}
		$this->addFiles([$view], 'views');
	}
	
	protected function voidView(string $view)
	{
		$this->voidDbFiles['views'][] = $view;
	}
	
	protected function addTriggerPath(string $path)
	{
		if (!is_dir($path))
		{
			$this->error("Must be corret path($path)");
			
			return false;
		}
		$this->addFiles([$path], 'triggers');
	}
	
	protected function addTriggerFile(string $view)
	{
		if (!file_exists($view))
		{
			$this->error("View($view) does not exists");
			
			return false;
		}
		$this->addFiles([$view], 'tiggers');
	}
	
	protected function voidTrigger(string $view)
	{
		$this->voidDbFiles['tiggers'][] = $view;
	}
	
	private function addFiles(array $files, $to)
	{
		foreach ($files as $file)
		{
			if (is_dir($file))
			{
				$this->addFiles(Dir::getContents($file, "dummy.txt", false, true), $to);
			}
			elseif (is_file($file))
			{
				if (!in_array($file, $this->dbFiles[$to]))
				{
					if (strtolower(\Infira\Utils\File::getExtension($file)) == 'sql')
					{
						$this->dbFiles[$to][] = $file;
					}
				}
			}
			else
			{
				$this->error('File is not file or path(' . $file . ') not found');
				
				return false;
			}
		}
	}
	
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('db:views');
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->beforeExecute();
		$this->output = &$output;
		
		$this->views();
		$this->triggers();
		
		return $this->success();
	}
	
	public function views()
	{
		foreach ($this->dbFiles['views'] as $fn)
		{
			if (!in_array($fn, $this->voidDbFiles['views']))
			{
				Db::fileQuery($fn);
				$this->message('<info>installed view: </info>' . $fn);
			}
		}
	}
	
	public function triggers()
	{
		foreach ($this->dbFiles['triggers'] as $fn)
		{
			if (!in_array($fn, $this->voidDbFiles['triggers']))
			{
				$con     = File::getContent($fn);
				$queries = explode("[TSP]", $con);
				foreach ($queries as $q)
				{
					Db::realQuery($q);
					$this->message('<info>installed trigger: </info>' . $fn);
				}
			}
		}
	}
}

?>