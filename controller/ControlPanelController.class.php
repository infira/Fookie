<?php

use Infira\Utils\Http as Http;
use Infira\Utils\File as File;

class ControlPanelController extends Controller
{
	public function __construct()
	{
		$this->allowOnlyDevAccess();
		$this->allowUnAuthorisedAccess();
		parent::__construct();
		Prof()->void();
		$this->showBeforeInstall();
		ini_set('memory_limit', '400M');
		Payload::outputHTML();
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
			exit("task completed");
		}
		exit("task failed");
	}
	
	public function subClass()
	{
		$className = Http::getGET('subClass');
		if ($className == 'db')
		{
			$className = 'InstallDatabase';
		}
		$Db = new $className();
		
		return $Db->start();
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
	
	public function flushAll()
	{
		$output = '';
		$output .= $this->flushCache() . BR;
		$output .= $this->flushCompiledTemplates() . BR;
		
		return $output . "all flushed";
	}
	
	public function flushCache()
	{
		alert("cache flusing is not implemented");
		$this->flushCompiledTemplates();
		
		return "cache flushed";
	}
	
	public function flushCompiledTemplates()
	{
		$this->Tmpl->clearAllCache();
		$this->Tmpl->clearCompiledTemplate();
		
		return "templates flushed";
	}
	
	public function flushEmailErrorCounter()
	{
		Dir::flush(Path::temp("emailErrorSentCount/"));
		
		return "emailErrorSentCount flushed";
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
		$r  .= "<h4>Database: $db</h4>";
		$r  .= $this->getButton("System updates", "db", ['task' => "updates", "reset" => 0]);
		$r  .= $this->getButton("Reset System updates", "db", ['task' => "updates", "reset" => 1]);
		$r  .= " | ";
		$r  .= $this->getButton("Views", 'db', ['task' => "views"]);
		$r  .= $this->getButton("Database ORM models", 'db', ['task' => "ormModels"]);
		$r  .= " | ";
		
		$r .= $this->getButton("all", '', ['task' => "all"]);
		
		$r .= BR . BR . "Assets" . BR;
		
		$r .= $this->getButton("assets version", '', ['task' => 'generateAssetsVersion']);
		
		$r .= BR . BR;
		
		$r .= $this->getButton("flushAll", '', ['task' => "flushAll"]);
		$r .= " | ";
		
		$r .= $this->getButton("flushAssets", '', ['task' => "flushAssets"]);
		
		$r .= $this->getButton("flushCache", '', ['task' => "flushCache"]);
		$r .= $this->getButton("flushCompiledTemplates", '', ['task' => "flushCompiledTemplates"]);
		$r .= $this->getButton("flush email erroir counter", '', ['task' => "flushEmailErrorCounter"]);
		
		
		echo $r . BR . BR . BR;
	}
	
	private function getButton($label, $subClass, $urlParams = [])
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
		if ($ok)
		{
			$style = 'style="color:red;font-weight:bold"';
		}
		$link = Route::getLink('/controlpanel/' . $subClass, $urlParams);
		
		return '<button ' . $style . ' type="button" onclick="window.location=\'' . $link . '\'">' . $label . '</button> ';
	}
}

?>