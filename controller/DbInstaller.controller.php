<?php

namespace Infira\Fookie\controller;

use Http;
use Path;
use Db;
use Infira\Poesis\generator\ModelGenerator;
use Infira\Utils\Dir;
use Infira\Utils\Fix;
use Infira\Utils\File;

class DbInstaller extends Controller
{
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
		$gen = new ModelGenerator(Path::modelModels(), \Infira\Poesis\ConnectionManager::default());
		
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
	
	private function getSqlViews($dir)
	{
		$output   = [];
		$getViews = Dir::getContents($dir, "dummy.txt");
		if (checkArray($getViews))
		{
			foreach ($getViews as $v)
			{
				$v = Fix::dirPath($dir) . $v;
				if ($v != "." && $v != ".." && is_file($v))
				{
					if (!in_array($v, $output))
					{
						if (strtolower(File::getExtension($v)) == 'sql')
						{
							$output[] = $v;
						}
					}
				}
			}
		}
		
		return $output;
	}
	
	/*
	 * Set base config
	 */
	public function views()
	{
		$allViews = $this->getSqlViews(Path::dbViews());
		
		$voidViews = ["install", "updates"];
		if (Http::existsGET("void"))
		{
			$voidViews = array_merge($voidViews, Variable::toArray(Http::getGet("void")));
		}
		if (Http::existsGET("view"))
		{
			$allViews = preg_grep('/' . Http::getGet("view") . '/', $allViews);
		}
		$output = ["Installing views"];
		foreach ($allViews as $fn)
		{
			$base = trim(str_replace(".sql", "", basename($fn)));
			if (!in_array($base, $voidViews))
			{
				if (substr($base, 0, 2) != "f_")
				{
					Db::fileQuery($fn);
					$output [] = $fn;
				}
			}
		}
		
		$output = array_merge($output, $this->triggers());
		
		return join("<br />", $output);
	}
	
	public function triggers()
	{
		$outut         = ["Installing triggers"];
		$viewFolders   = [];
		$viewFolders[] = Path::root("db/triggers/");
		
		$allTriggers = [];
		foreach ($viewFolders as $folder)
		{
			$allTriggers = array_merge($allTriggers, $this->getSqlViews($folder));
		}
		$voidTriggers = [];
		if (Http::existsGET("void"))
		{
			$voidTriggers = array_merge($voidTriggers, Variable::toArray(Http::getGet("void")));
		}
		
		foreach ($allTriggers as $fn)
		{
			$triggerName = trim(str_replace(".sql", "", basename($fn)));
			if (!in_array($triggerName, $voidTriggers))
			{
				$con     = File::getContent($fn);
				$outut[] = $fn;
				$queries = explode("[TSP]", $con);
				foreach ($queries as $q)
				{
					Db::realQuery($q);
				}
			}
		}
		
		return $outut;
	}
}

?>