<?php

namespace Infira\Fookie\controller;

use Infira\Utils\ClassFarm;
use AppConfig;
use Infira\Fookie\Fookie;

ClassFarm::add("Controller", "Controller");

/**
 * @property Controller $Controller
 */
abstract class Controller extends \Infira\Utils\MagicClass
{
	private $authRequired       = false;
	private $allowOnlyDevAccess = false;
	
	public function __construct()
	{
		return true;
	}
	
	public final function authorize()
	{
		if ($this->allowOnlyDevAccess == true and !AppConfig::isLiveWorthy())
		{
			Fookie::error("oly dev environment can access this controller", null, 500);
		}
		elseif ($this->authRequired === true and !$this->isAccessAllowed())
		{
			$this->onAuthRequired();
		}
	}
	
	public function validate(): bool
	{
		return true;
	}
	
	public function beforeAction() { }
	
	public function resultParser($res) { return $res; }
	
	protected function onAuthRequired()
	{
		Fookie::error("auth requierd");
	}
	
	public final function requireAuth(bool $require)
	{
		$this->authRequired = $require;
	}
	
	
	/**
	 * Set current controller to access only with dev environment
	 */
	protected function allowOnlyDevAccess()
	{
		$this->allowOnlyDevAccess = true;
	}
	
	protected function isAccessAllowed(): bool
	{
		return false;
	}
	
	public function getActionArguments(): array
	{
		return [];
	}
}

?>