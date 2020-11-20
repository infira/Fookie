<?php

namespace Infira\Fookie\controller;

use Http;
use Path;
use Db;
use Infira\Poesis\modelGenerator\Generator;
use Infira\Poesis\modelGenerator\Options;
use Infira\Utils\Dir;
use Infira\Utils\Fix;
use Infira\Utils\File;

class DbInstaller extends Controller
{
	private $dbFiles     = ['views' => [], 'triggers' => []];
	private $voidDbFiles = ['views' => [], 'triggers' => []];
	
	public function __construct()
	{
		$this->addViewPath(Path::dbViews());
		$this->dbFiles['triggers'][] = Path::dbTriggers();
		parent::__construct();
	}
	
	public function addViewPath(string $path)
	{
		if (!is_dir($path))
		{
			alert("Must be corret path($path)");
		}
		$this->dbFiles['views'][] = $path;
	}
	
	public function run()
	{
		if (!defined("VOID_DB_LOG"))
		{
			define("VOID_DB_LOG", true);
		}
		set_time_limit(7200);
		$installName = Http::getGet('task');
		if (method_exists($this, $installName))
		{
			return $this->$installName();
		}
		else
		{
			return "task not found";
		}
	}
	
	public function ormModels()
	{
		$gen = new Generator(Path::modelModels(), \Infira\Poesis\ConnectionManager::default(), new Options());
		
		return $gen->generate();
	}
	
	public function ormModelsDownload()
	{
		cleanOutput(true);
		$zipFile = Path::temp("orm.zip");
		$zip     = new ZipArchive();
		$zip->open($zipFile, ZipArchive::CREATE);
		foreach (Dir::getContents(Path::modelModels()) as $file)
		{
			$zip->addFile(Path::modelModels($file), $file);
		}
		$zip->close();
		
		/*
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=orm.zip");
		header("Content-length: " . filesize($file));
		header("Pragma: no-cache");
		header("Expires: 0");
		*/
		readfile($zipFile);
		File::delete($zipFile);
		exit;
		
	}
	
	public function addViewFile(string $view)
	{
		$this->dbFiles['views'][] = $view;
	}
	
	public function voidView(string $view)
	{
		$this->voidDbFiles['views'][] = $view;
	}
	
	public function addTriggerFile(string $view)
	{
		$this->dbFiles['tiggers'][] = $view;
	}
	
	
	public function voidTrigger(string $view)
	{
		$this->voidDbFiles['tiggers'][] = $view;
	}
	
	/*
	 * Set base config
	 */
	public function views()
	{
		$output = ["Installing views"];
		foreach ($this->getFiles($this->dbFiles['views']) as $fn)
		{
			if (!in_array($fn, $this->voidDbFiles['views']))
			{
				Db::fileQuery($fn);
				$output [] = $fn;
			}
		}
		
		$output = array_merge($output, $this->triggers());
		
		return join("<br />", $output);
	}
	
	public function triggers()
	{
		$output        = ["Installing triggers"];
		$viewFolders   = [];
		$viewFolders[] = Path::root("db/triggers/");
		
		foreach ($this->getFiles($this->dbFiles['triggers']) as $fn)
		{
			if (!in_array($fn, $this->voidDbFiles['triggers']))
			{
				$con     = File::getContent($fn);
				$queries = explode("[TSP]", $con);
				foreach ($queries as $q)
				{
					Db::realQuery($q);
					$output[] = $fn;
				}
			}
		}
		
		return $output;
	}
	
	
	private function getFiles(array $files)
	{
		$views = [];
		foreach ($files as $file)
		{
			if (is_dir($file))
			{
				$views = array_merge($views, Dir::getContents($file, "dummy.txt", false, true));
			}
			elseif (is_file($file))
			{
				if (!in_array($file, $views))
				{
					if (strtolower(File::getExtension($file)) == 'sql')
					{
						$views[] = $file;
					}
				}
			}
			else
			{
				alert('Fiel is not file or path(' . $file . ') not found');
			}
		}
		
		return $views;
	}
}

?>