<?php

namespace Infira\Fookie\controller;

use Infira\Utils\Http;
use Infira\Fookie\facade\File;
use Infira\Fookie\request\Route;
use Infira\Fookie\request\Payload;
use Infira\Fookie\Fookie;
use Infira\Fookie\KeyData;

class ControlPanel extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->requireAuth(TRUE);
		Prof()->void();
		$this->showBeforeInstall();
		ini_set('memory_limit', '400M');
		Payload::plainOutput();
	}
	
	protected function isUserAuthotized(): bool
	{
		if (\AppConfig::isLocalENV())
		{
			return TRUE;
		}
		if (Http::exists('subClass'))
		{
			$sc = Http::get('subClass');
			if ($sc == 'updates')
			{
				if (in_array(Http::get('task'), ['ormModels', 'ormModelsDownload']))
				{
					return FALSE;
				}
			}
			elseif (!in_array($sc, ['updates', 'db']))
			{
				return FALSE;
			}
		}
		elseif (!in_array(Http::get('task'), ['generateAssetsVersion', 'flushAll', 'flushAssets', 'flushCache', 'flushCompiledSmartyTemplates']))
		{
			return FALSE;
		}
		
		return TRUE;
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
			$className = Fookie::optExists('systemController') ? Fookie::opt('systemController') : '\Infira\Fookie\controller\SystemUpdater';
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
	
	protected function showBeforeInstall()
	{
		if ($this->isMinOutout())
		{
			return TRUE;
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
		$r  .= $this->getButton("Reset updates", "updates", ["reset" => 1]);
		$r  .= " | ";
		$r  .= "<h4>Database: $db</h4>";
		$r  .= $this->getButton("Views", 'db', ['task' => "views"]);
		$r  .= $this->getButton("Database ORM models", 'db', ['task' => "ormModels"]);
		$r  .= " | ";
		
		$r .= BR . BR . "Assets" . BR;
		
		$r .= $this->getButton("assets version", '', ['task' => 'generateAssetsVersion']);
		
		$r .= BR . BR;
		
		$r .= $this->getButton("flushAll", 'flusher', ['task' => "flushAll"]);
		$r .= " | ";
		
		$r .= $this->getButton("flushAssets", 'flusher', ['task' => "flushAssets"]);
		
		$r .= $this->getButton("flushCache", 'flusher', ['task' => "flushCache"]);
		$r .= $this->getButton("flushCompiled SmartyTemplates", 'flusher', ['task' => "flushCompiledSmartyTemplates"]);
		
		
		echo $r . BR . BR . BR;
	}
	
	private function getButton($label, $controllerName, $urlParams = [])
	{
		$style = "";
		$ok    = TRUE;
		foreach ($urlParams as $key => $val)
		{
			if (Http::getGET($key) != $val)
			{
				$ok = FALSE;
				break;
			}
		}
		if ($controllerName != Http::getGET('subClass'))
		{
			$ok = FALSE;
		}
		if ($ok)
		{
			$style = 'style="color:red;font-weight:bold"';
		}
		$link = Route::getLink('/controlpanel/' . $controllerName, $urlParams);
		
		return '<button ' . $style . ' type="button" onclick="window.location=\'' . $link . '\'">' . $label . '</button> ';
	}
	
	
	//######################################################################## Tasks
	public function generateAssetsVersion()
	{
		$assetsVersion = intval(KeyData::get("assetsVersion")) + 1;
		KeyData::set("assetsVersion", $assetsVersion);
		
		return $assetsVersion . BR;
	}
	
}

?>
