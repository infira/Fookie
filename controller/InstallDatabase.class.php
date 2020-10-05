<?php

class InstallDatabase
{
	
	public function start()
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
		$gen = new Infira\Poesis\generator\ModelGenerator(Path::modelModels(), Db::default());
		
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
	
	public function updates()
	{
		$phpSqcriptDir = Path::root("db/phpScripts/");
		
		$lines    = file(Path::root("db/updates.sql"));
		$isSystem = 1;
		// Loop through each line
		$sql      = "";
		$templine = "";
		$queries  = [];
		if (Http::getGet("reset") == 1 or Http::getGet("delete") == 1)
		{
			Db::TSqlUpdates()->set("isSystem", $isSystem)->delete();
			if (Http::getGet("delete") === 1)
			{
				exit("Deleted");
			}
		}
		foreach ($lines as $line)
		{
			// Skip it if it's a comment
			if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 1) == '#')
			{
				continue;
			}
			
			
			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';')
			{
				// Perform the query
				$q   = trim($templine);
				$sql .= $q;
				// Reset temp variable to empty
				$templine = '';
				if (trim($q))
				{
					$queries[] = $q;
				}
			}
		}
		if (checkArray($queries))
		{
			$dbUpdates = [];
			if (Http::getGet("reset") == 0)
			{
				$dbUpdates = Db::TSqlUpdates()->set("isSystem", $isSystem)->select()->getValueAsKey("updateNr");
			}
			foreach ($queries as $updateNr => $sql)
			{
				$ok = true;
				if (isset($dbUpdates[$updateNr]))
				{
					if ($dbUpdates[$updateNr]["installed"] == 1)
					{
						$ok = false;
					}
				}
				if (substr($sql, 0, 6) == "void--")
				{
					$ok = false;
				}
				if ($ok === true)
				{
					$Db            = new TSqlUpdates();
					$Db->isSystem  = $isSystem;
					$Db->updateNr  = $updateNr;
					$Db->isSystem  = $isSystem;
					$Db->installed = 1;
					if (substr($sql, 0, 10) == "phpScript:")
					{
						$fileName   = substr($sql, 10, -1);
						$scriptFile = $phpSqcriptDir . $fileName;
						if (Http::getGet("reset") == 0)
						{
							debug($sql);
							$this->runPhpScript($scriptFile);
						}
						
						$Db->sqlQuery          = $sql;
						$Db->phpScriptFileName = $fileName;
						$Db->phpScript         = File::getContent($scriptFile);
					}
					else
					{
						if (Http::getGet("reset") == 0)
						{
							debug($sql);
							Db::realQuery($sql);
						}
						$Db->sqlQuery          = $sql;
						$Db->phpScriptFileName = null;
						$Db->phpScript         = null;
					}
					$Db->save();
				}
			}
		}
		exit("Installed = Db is up to date");
	}
	
	private function runPhpScript($file)
	{
		if (!file_exists($file))
		{
			alert("Php script $file does not exists");
		}
		require_once $file;
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
		$allViews = $this->getSqlViews(Path::root("db/"));
		
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