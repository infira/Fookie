<?php

namespace Infira\Fookie\Smurf;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use File;
use Db;
use Path;
use Infira\Utils\Dir;
use Infira\Fookie\KeyData;

class GenAssetsVersion extends SmurfCommand
{
	/**
	 * @return void
	 */
	protected function configure(): void
	{
		$this->setName('assets');
	}
	
	/**
	 * @param InputInterface  $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$this->beforeExecute();
		$this->output = &$output;
		
		$assetsVersion = intval(KeyData::get("assetsVersion")) + 1;
		KeyData::set("assetsVersion", $assetsVersion);
		
		$this->message("new assets version: <info>$assetsVersion</info>");
		
		return $this->success();
	}
}

?>