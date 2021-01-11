<?php

namespace Infira\Fookie\controller;

use Http;
use Db;
use Path;
use Infira\Utils\File;

class SystemUpdater extends Controller
{
	public function run()
	{
		if (!defined("VOID_DB_LOG"))
		{
			define("VOID_DB_LOG", true);
		}
		set_time_limit(7200);
		$phpSqcriptDir = Path::systemUpdates("scripts/");
		
		$lines    = file(Path::systemUpdates("updates.sql"));
		$isSystem = 1;
		// Loop through each line
		$sql      = "";
		$templine = "";
		$queries  = [];
		if (Http::getGet("reset") == 1 or Http::getGet("delete") == 1)
		{
			Db::TSqlUpdates()->isSystem($isSystem)->delete();
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
				$dbUpdates = Db::TSqlUpdates()->isSystem($isSystem)->select()->getValueAsKey("updateNr");
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
					debug($sql);
					$Db            = Db::TSqlUpdates();
					$Db->updateNr  = $updateNr;
					$Db->isSystem  = $isSystem;
					$Db->installed = 1;
					if (substr($sql, 0, 10) == "phpScript:")
					{
						$fileName   = substr($sql, 10, -1);
						$scriptFile = $phpSqcriptDir . $fileName;
						if (Http::getGet("reset") == 0)
						{
							$this->runPhpScript($scriptFile);
						}
						
						$Db->sqlQuery          = $sql;
						$Db->phpScriptFileName = $fileName;
						addExtraErrorInfo('$scriptFile', $scriptFile);
						$Db->phpScript = File::getContent($scriptFile);
					}
					else
					{
						if (Http::getGet("reset") == 0)
						{
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
		
		return 'Installed = Db is up to date';
	}
	
	private function runPhpScript($file)
	{
		if (!file_exists($file))
		{
			alert("Php script $file does not exists");
		}
		require_once $file;
	}
}

?>