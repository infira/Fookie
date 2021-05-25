<?php

namespace Infira\Fookie\Smurf;

use Infira\Fookie\KeyData;

class GenAssetsVersion extends SmurfCommand
{
	public function __construct()
	{
		parent::__construct('assets');
	}
	
	protected function runCommand()
	{
		$assetsVersion = intval(KeyData::get("assetsVersion")) + 1;
		KeyData::set("assetsVersion", $assetsVersion);
		$this->message("new assets version: <info>$assetsVersion</info>");
	}
}

?>