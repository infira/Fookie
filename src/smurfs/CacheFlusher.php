<?php

namespace Infira\Fookie\Smurf;

use Infira\Fookie\facade\Cache;
use Infira\Fookie\Flush;

class CacheFlusher extends SmurfCommand
{
	public function __construct()
	{
		$this->addConfig('cache', 'c', 'flushCache');
		parent::__construct('flush');
	}
	
	protected function flushCache()
	{
		Cache::init();
		Flush::cache();
		$this->info('Cachly flushed');
		$this->afterFlush();
	}
	
	public function afterFlush()
	{
	
	}
	
}

?>