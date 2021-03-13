<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Infira\Poesis\modelGenerator\Options;
use Infira\Poesis\modelGenerator\Generator;
use Infira\Poesis\ConnectionManager;
use Path;
use Infira\Poesis\Connection;

class DbModels extends SmurfCommand
{
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
	
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('db:models');
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
		
		$this->dbConnection = $this->dbConnection ? $this->dbConnection : \Infira\Poesis\ConnectionManager::default();
		$gen                = new Generator($this->dbConnection, $this->Options);
		foreach ($gen->generate($this->installPath) as $file)
		{
			$this->message('<info>generated model: </info>' . $file);
		}
		$this->afterExecute();
		
		return $this->success();
	}
}

?>