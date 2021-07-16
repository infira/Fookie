<?php

namespace Infira\Fookie\Smurf;

use Infira\Poesis\modelGenerator\Options;
use Infira\Poesis\modelGenerator\Generator;
use Infira\Poesis\ConnectionManager;
use Infira\Poesis\Connection;
use File;
use Db;
use Infira\Utils\Dir;
use Infira\Fookie\facade\Variable;


class SmurfDb extends SmurfCommand
{
	private $dbFiles     = ['views' => [], 'triggers' => []];
	private $voidDbFiles = ['views' => [], 'triggers' => []];
	private $variable    = [];
	
	/**
	 * @var Options
	 */
	protected $Options;
	private   $installPath = null;
	
	/**
	 * @var Connection
	 */
	private $dbConnection = null;
	
	public function __construct()
	{
		$this->addConfig('views', 'w', 'runViews');
		$this->addConfig('models', 'm', 'runModels');
		parent::__construct('db');
		$this->Options = new Options();
	}
	
	/**
	 * @param Connection|null $dbConnection
	 */
	public function setDbConnection(?Connection $dbConnection): void
	{
		$this->dbConnection = $dbConnection;
	}
	
	public function addVariable(string $name, $value): void
	{
		$this->variable[$name] = $value;
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
					$realExtension = pathinfo($file)['extension'];
					if ($realExtension == 'sql')
					{
						$this->dbFiles[$to][] = $file;
					}
					else
					{
						$ex  = explode('.', $file);
						$ext = strtolower(join('.', array_slice($ex, -2)));
						if ($ext == 'sql.php')
						{
							$this->dbFiles[$to][] = $file;
						}
					}
				}
			}
			else
			{
				$this->error('File is not file or path(' . $file . ') not found');
			}
		}
	}
	
	public function runModels()
	{
		$this->Options->setDefaultModelDataMethodsExtendor('\Infira\Fookie\Poesis\FDataMethods');
		$this->beforeExecute_Models();
		$this->dbConnection = $this->dbConnection ? $this->dbConnection : ConnectionManager::default();
		
		
		$gen = new Generator($this->dbConnection, $this->Options);
		foreach ($gen->generate($this->installPath) as $file)
		{
			$this->message('<info>generated model: </info>' . $file);
		}
		$this->afterExecute_Models();
	}
	
	public function runViews()
	{
		$this->beforeExecute_Views();
		$this->views();
		$this->triggers();
	}
	
	public function views()
	{
		foreach ($this->dbFiles['views'] as $fn)
		{
			if (!in_array($fn, $this->voidDbFiles['views']))
			{
				try
				{
					if (strtolower(\Infira\Utils\File::getExtension($fn)) == 'php')
					{
						require_once $fn;
						$func = str_replace(['.sql', '.php'], '', File::getFileNameWithoutExtension($fn));
						if (!function_exists($func))
						{
							$this->error('View php file must contain function ' . $func);
						}
						$q = $func();
						if (gettype($q) != 'string')
						{
							$this->error("View function $func must return string");
						}
						Db::complexQuery($this->parseQuery($q));
					}
					else
					{
						Db::fileQuery($fn, $this->variable);
					}
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
						Db::realQuery($this->parseQuery($q));
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
	
	private function parseQuery(string $query)
	{
		return Variable::assign($this->variable, $query);
	}
	
	protected function beforeExecute_Models() { }
	
	protected function afterExecute_Models() { }
	
	protected function beforeExecute_Views() { }
	
	protected function afterExecute_Views() { }
}

?>