<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Infira\Fookie\facade\Variable;
use Db;
use Infira\Utils\File;

/*
SELF UPDATES
ALTER TABLE `sql_updates` DROP PRIMARY KEY;
ALTER TABLE `sql_updates` ADD `hash` VARCHAR(35) NULL DEFAULT NULL FIRST;
UPDATE sql_updates SET hash = md5();
ALTER TABLE `sql_updates` ADD PRIMARY KEY(`hash`);
ALTER TABLE `sql_updates` ADD `ts` TIMESTAMP(6) on update CURRENT_TIMESTAMP(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6) AFTER `installed`;
ALTER TABLE `sql_updates` CHANGE `content` `sqlQuery` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sql_updates` ADD `rawQuery` LONGTEXT NULL DEFAULT NULL AFTER `sqlQuery`;
ALTER TABLE `sql_updates` ADD `phpScriptFileName` LONGTEXT NOT NULL AFTER `rawQuery`;
 */

class Updates extends SmurfCommand
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
		$this->setName('updates')
			->addOption('reset', 'r', InputOption::VALUE_NONE, 'Reset all')
			->addOption('flush', 'f', InputOption::VALUE_NONE, 'Flush');
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
		
		
		$lines   = file($this->updateFile);
		$isReset = $input->getOption('reset');
		
		// Loop through each line
		$templine = "";
		$queries  = [];
		if ($isReset or $input->getOption('flush'))
		{
			Db::TSqlUpdates()->truncate();
			if ($input->getOption('flush'))
			{
				return $this->success();
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
		$isSystem = 1; //later when CMS is implemented this is needed
		if (checkArray($queries))
		{
			$dbUpdates = Db::TSqlUpdates()->select()->getValueAsKey("hash");
			
			foreach ($queries as $updateNr => $rawQuery)
			{
				$ok   = true;
				$hash = md5($rawQuery . $isSystem . $updateNr);
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
					$Db->updateNr($updateNr);
					//$Db->isSystem ($isSystem);
					$Db->installed(1);
					$Db->rawQuery($rawQuery);
					$query = Variable::assign($this->vars, $rawQuery);
					$Db->sqlQuery($query);
					addExtraErrorInfo('$query', $query);
					if (substr($query, 0, 10) == "phpScript:")
					{
						$fileName   = substr($query, 10, -1);
						$scriptFile = $this->phpScriptPath . $fileName;
						if (!$isReset)
						{
							$this->runPhpScript($scriptFile);
						}
						$Db->phpScriptFileName($scriptFile);
						$Db->phpScript(File::getContent($scriptFile));
						$this->message('<fg=#cc00ff>PHP script</>: ' . $scriptFile);
					}
					else
					{
						if (!$isReset)
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
		
		return $this->success();
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