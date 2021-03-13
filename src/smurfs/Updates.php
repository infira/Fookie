<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Infira\Fookie\facade\Variable;
use Db;
use Infira\Utils\File;

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
		if (checkArray($queries))
		{
			$dbUpdates = $dbUpdates = Db::TSqlUpdates()->select()->getValueAsKey("updateNr", true);
			foreach ($queries as $updateNr => $query)
			{
				$ok = true;
				if (isset($dbUpdates[$updateNr]))
				{
					if ($dbUpdates[$updateNr]->installed == 1)
					{
						$ok = false;
					}
				}
				if (substr($query, 0, 6) == "void--")
				{
					$ok = false;
				}
				if ($ok === true)
				{
					$query         = Variable::assign($this->vars, $query);
					$Db            = Db::TSqlUpdates();
					$Db->updateNr  = $updateNr;
					$Db->installed = 1;
					if (substr($query, 0, 10) == "phpScript:")
					{
						$fileName   = substr($query, 10, -1);
						$scriptFile = $this->phpScriptPath . $fileName;
						if (!$isReset)
						{
							$this->runPhpScript($scriptFile);
						}
						
						$Db->phpScript($query);
						$Db->content(File::getContent($scriptFile));
						$this->message('<fg=#cc00ff>PHP script</>: ' . $scriptFile);
					}
					else
					{
						if (!$isReset)
						{
							Db::realQuery($query);
						}
						$Db->content($query);
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