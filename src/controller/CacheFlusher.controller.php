<?php

namespace Infira\Fookie\controller;

use Http;
use Path;
use Db;
use Infira\Poesis\modelGenerator\Generator;
use Infira\Poesis\modelGenerator\Options;
use Infira\Utils\Dir;
use Infira\Utils\Fix;
use Infira\Utils\File;
use Infira\Fookie\facade\Cache;

class CacheFlusher extends Controller
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
	
	public function flushAll()
	{
		$output = '';
		$output .= $this->flushCache() . BR;
		$output .= $this->flushCompiledTemplates() . BR;
		
		return $output . "all flushed";
	}
	
	function flushCache()
	{
		if (Cache::isConfigured('file'))
		{
			Cache::$Driver->File->flush();
		}
		Cache::$Driver->Sess->flush();
		if (Cache::isConfigured('db'))
		{
			Cache::$Driver->Db->flush();
		}
		
		return "cache flushed";
	}
	
	public function flushCompiledTemplates()
	{
		$this->View->clearAllCache();
		$this->View->clearCompiledTemplate();
		
		return "templates flushed";
	}
	
	public function flushEmailErrorCounter()
	{
		Dir::flush(Path::temp("emailErrorSentCount/"));
		
		return "emailErrorSentCount flushed";
	}
	
	public function isUserAuthotized(): bool { return true; }
}

?>