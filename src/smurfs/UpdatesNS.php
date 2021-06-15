<?php

namespace Infira\Fookie\Smurf;

use Infira\Fookie\facade\Variable;
use Db;
use Infira\Utils\File;
use Infira\Utils\Fix;

/*
SELF UPDATES
DROP TABLE IF EXISTS `sql_updates`;
CREATE TABLE `sql_updates` (
    `hash` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
    `namespace` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `updateNr` int(11) NOT NULL,
    `installed` tinyint(1) NOT NULL,
    `ts` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
    `sqlQuery` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `rawQuery` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `phpScriptFileName` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `phpScript` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `sql_updates` ADD PRIMARY KEY (`hash`);
 */

class UpdatesNS extends SmurfCommand
{
	private $vars = [];
	
	private $updates = [];
	
	public function __construct()
	{
		$this->addConfig('reset', 'r');
		$this->addConfig('flush', 'f');
		parent::__construct('updates');
	}
	
	public function addVar(string $name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	protected function addUpdate(string $namespace, string $path, string $scriptsPath)
	{
		if (!file_exists($path))
		{
			$this->error("Update file $path does not exist");
		}
		if (!is_dir($scriptsPath))
		{
			$this->error("Scripts path $scriptsPath is not a folder");
		}
		$this->updates[] = ['ns' => $namespace, 'path' => $path, 'scripts' => Fix::dirPath($scriptsPath)];
	}
	
	protected function runCommand()
	{
		foreach ($this->updates as $update)
		{
			$lines = file($update['path']);
			
			// Loop through each line
			$templine = "";
			$queries  = [];
			if ($this->input->getOption('reset') or $this->input->getOption('flush'))
			{
				Db::TSqlUpdates()->namespace($update['ns'])->delete();
				if ($this->input->getOption('flush'))
				{
					return;
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
					$q = trim($templine);
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
				$dbUpdates = Db::TSqlUpdates()->select()->getValueAsKey("hash");
				
				foreach ($queries as $updateNr => $rawQuery)
				{
					$ok   = true;
					$hash = md5($rawQuery . $update['ns'] . $updateNr);
					if (isset($dbUpdates[$hash]))
					{
						if ($dbUpdates[$hash]["installed"] == 1)
						{
							$ok = false;
						}
					}
					$void = false;
					if (substr($rawQuery, 0, 7) == "--void:")
					{
						$void = true;
					}
					addExtraErrorInfo('hash', $hash);
					addExtraErrorInfo('$rawQuery', $rawQuery);
					if ($ok === true)
					{
						$Db = Db::TSqlUpdates();
						$Db->hash($hash);
						$Db->namespace($update['ns']);
						$Db->updateNr($updateNr);
						$Db->installed(1);
						$Db->rawQuery($rawQuery);
						$query = Variable::assign($this->vars, $rawQuery);
						$Db->sqlQuery($query);
						addExtraErrorInfo('$query', $query);
						if (substr($query, 0, 10) == "phpScript:")
						{
							$fileName   = substr($query, 10, -1);
							$scriptFile = $update['scripts'] . $fileName;
							if (!$this->input->getOption('reset'))
							{
								$this->runPhpScript($scriptFile);
							}
							$Db->phpScriptFileName($scriptFile);
							$Db->phpScript(File::getContent($scriptFile));
							$this->message('<fg=#cc00ff>PHP script</>: ' . $scriptFile);
						}
						else
						{
							if (!$this->input->getOption('reset'))
							{
								Db::realQuery($query);
							}
							$this->message('<fg=#00aaff>SQL query</>: ' . $query);
						}
						$Db->insert();
					}
				}
			}
			$this->info('Everything is up to date');
		}
	}
	
	private function runPhpScript($file)
	{
		if (!file_exists($file))
		{
			$this->error("Php script $file does not exists");
			
			return false;
		}
		require_once $file;
		
	}
}

?>