<?php

namespace Infira\Fookie\controller;

use Infira\Fookie\facade\Http;
use Infira\Fookie\facade\File;
use Infira\Fookie\request\Route;
use Infira\Fookie\request\Payload;
use Infira\Fookie\Fookie;

class ControlPanel extends \Infira\Fookie\controller\Controller
{
	public function __construct()
	{
		$this->allowOnlyDevAccess();
		parent::__construct();
		Prof()->void();
		$this->showBeforeInstall();
		ini_set('memory_limit', '400M');
		Payload::plainOutput();
	}
	
	public function index()
	{
		$installName = Http::getGET('task');
		if (method_exists($this, $installName))
		{
			$output = $this->$installName();
			if (!$this->isMinOutout())
			{
				echo $output . BR . BR;
			}
			
			return 'task completed';
		}
		
		return 'task failed';
	}
	
	public function subClass()
	{
		$controller = Http::getGET('subClass');
		if ($controller == 'updates')
		{
			$className = '\Infira\Fookie\controller\SystemUpdater';
		}
		elseif ($controller == 'db')
		{
			$className = Fookie::optExists('dbInstallerController') ? Fookie::opt('dbInstallerController') : '\Infira\Fookie\controller\DbInstaller';
		}
		elseif ($controller == 'flusher')
		{
			$className = Fookie::optExists('cacheFlusherController') ? Fookie::opt('cacheFlusherController') : '\Infira\Fookie\controller\CacheFlusher';
		}
		else
		{
			alert("Unknown controller");
		}
		$Db = new $className();
		
		return $Db->run();
	}
	
	public function isMinOutout()
	{
		return Http::existsGET("minOutput");
	}
	
	public function generateAssetsVersion()
	{
		$assetsVersion = intval(KeyData::get("assetsVersion")) + 1;
		KeyData::set("assetsVersion", $assetsVersion);
		
		return $assetsVersion . BR;
	}
	
	//------------
	
	
	protected function showBeforeInstall()
	{
		if ($this->isMinOutout())
		{
			return true;
		}
		$r = "";
		if (Http::existsGET("isAll"))
		{
			return $r;
		}
		
		$link = Route::getLink();
		$r    .= '<button type="button" onclick="window.location=\'' . $link . '\'">Go Home</button>';
		
		$r .= "<h4>Else</h4>";
		
		$r .= $this->getButton("Autoloader", '', ['generateAutoloader' => "1"]);
		
		
		$db = DB_NAME;
		$r  .= "<h4>Update</h4>";
		$r  .= $this->getButton("Install updates", "updates", ["reset" => 0]);
		$r  .= $this->getButton("Reset System updates", "updates", ["reset" => 1]);
		$r  .= " | ";
		$r  .= "<h4>Database: $db</h4>";
		$r  .= $this->getButton("Views", 'db', ['task' => "views"]);
		$r  .= $this->getButton("Database ORM models", 'db', ['task' => "ormModels"]);
		$r  .= " | ";
		
		$r .= $this->getButton("all", '', ['task' => "all"]);
		
		$r .= BR . BR . "Assets" . BR;
		
		$r .= $this->getButton("assets version", '', ['task' => 'generateAssetsVersion']);
		
		$r .= BR . BR;
		
		$r .= $this->getButton("flushAll", 'flusher', ['task' => "flushAll"]);
		$r .= " | ";
		
		$r .= $this->getButton("flushAssets", 'flusher', ['task' => "flushAssets"]);
		
		$r .= $this->getButton("flushCache", 'flusher', ['task' => "flushCache"]);
		$r .= $this->getButton("flushCompiledTemplates", 'flusher', ['task' => "flushCompiledTemplates"]);
		$r .= $this->getButton("flush email erroir counter", 'flusher', ['task' => "flushEmailErrorCounter"]);
		
		
		echo $r . BR . BR . BR;
	}
	
	private function getButton($label, $controllerName, $urlParams = [])
	{
		$style = "";
		$ok    = true;
		foreach ($urlParams as $key => $val)
		{
			if (Http::getGET($key) != $val)
			{
				$ok = false;
				break;
			}
		}
		if ($controllerName != Http::getGET('subClass'))
		{
			$ok = false;
		}
		if ($ok)
		{
			$style = 'style="color:red;font-weight:bold"';
		}
		$link = Route::getLink('/controlpanel/' . $controllerName, $urlParams);
		
		return '<button ' . $style . ' type="button" onclick="window.location=\'' . $link . '\'">' . $label . '</button> ';
	}
}

?>
