<?php

namespace Infira\Fookie\controller;

use Infira\Utils\ClassFarm;
use AppConfig;
use Infira\Fookie\request\Payload;

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
		if ($this->allowOnlyDevAccess == true and !AppConfig::isDevENV())
		{
			Payload::sendError("oly dev environment can access this controller");
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
		Payload::sendError("auth requierd");
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