<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Infira\Poesis\modelGenerator\Options;
use Infira\Poesis\modelGenerator\Generator;
use Infira\Poesis\ConnectionManager;
use Infira\Poesis\Connection;
use File;
use Db;
use Infira\Utils\Dir;


class SmurfDb extends SmurfCommand
{
	private $dbFiles     = ['views' => [], 'triggers' => []];
	private $voidDbFiles = ['views' => [], 'triggers' => []];
	
	/**
	 * @var Options
	 */
	protected $Options;
	private   $installPath = null;
	
	/**
	 * @var Connection
	 */
	private $dbConnection = null;
	
	/**
	 * @param Connection|null $dbConnection
	 */
	public function setDbConnection(?Connection $dbConnection): void
	{
		$this->dbConnection = $dbConnection;
	}
	
	protected function setInstallPath(string $path)
	{
		$this->installPath = $path;
	}
	
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
		$this->setName('db')
			->addOption('models', 'm', InputOption::VALUE_NONE, 'Models')
			->addOption('views', 'w', InputOption::VALUE_NONE, 'Views');
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->output  = &$output;
		$this->input   = &$input;
		$this->Options = new Options();
		$this->beforeExecute();
		
		if ($input->getOption('views'))
		{
			$this->beforeExecute_Views();
			$this->views();
			$this->triggers();
		}
		if ($input->getOption('models'))
		{
			$this->beforeExecute_Models();
			$this->dbConnection = $this->dbConnection ? $this->dbConnection : ConnectionManager::default();
			$gen                = new Generator($this->dbConnection, $this->Options);
			foreach ($gen->generate($this->installPath) as $file)
			{
				$this->message('<info>generated model: </info>' . $file);
			}
		}
		$this->afterExecute();
		
		return $this->success();
	}
	
	public function views()
	{
		foreach ($this->dbFiles['views'] as $fn)
		{
			if (!in_array($fn, $this->voidDbFiles['views']))
			{
				try
				{
					Db::fileQuery($fn);
				}
				catch (\Exception $e)
				{
					$this->error($e->getMessage());
					exit;
				}
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
					try
					{
						Db::realQuery($q);
					}
					catch (\Exception $e)
					{
						$this->error($e->getMessage());
						exit;
					}
				}
				$this->message('<info>installed trigger: </info>' . $fn);
			}
		}
	}
	
	protected function beforeExecute_Models() { }
	
	protected function beforeExecute_Views() { }
}

?>