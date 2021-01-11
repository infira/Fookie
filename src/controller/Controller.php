<?php

namespace Infira\Fookie\controller;

use Infira\Utils\ClassFarm;
use Infira\Fookie\facade\Http;
use Infira\Fookie\request\Route;
use Infira\Fookie\facade\Session;
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
	private $constructorCalled  = false;
	
	public function __construct()
	{
		$this->constructorCalled = true;
		if (Http::getGET("showSID"))
		{
			debug(Session::getSID());
		}
		if (Http::getGET("debugSession"))
		{
			debug(Session::get());
		}
		if (!isAjaxRequest() && Http::existsGET("showRoute"))
		{
			debug(Route::getName());
		}
	}
	
	/**
	 * Ghot method to enuse called contoller exteds this class
	 */
	public function validate()
	{
		if (!$this->constructorCalled)
		{
			Payload::setError(get_class($this) . ' __construct must be initialized');
		}
		elseif ($this->authRequired === true and !$this->isUserAuthotized())
		{
			$this->onAuthRequired();
		}
		elseif ($this->allowOnlyDevAccess == true and !AppConfig::isDevENV())
		{
			Payload::setError("oly dev envinronment can access this controller");
		}
		
		return true;
	}
	
	protected function onAuthRequired()
	{
		Payload::setError("auth requierd");
	}
	
	protected final function disAllowUnAuthorisedAccess()
	{
		$this->authRequired = true;
	}
	
	protected final function allowUnAuthorisedAccess()
	{
		$this->authRequired = false;
	}
	
	
	/**
	 * Set current controller to access only with dev envinronment
	 */
	protected function allowOnlyDevAccess()
	{
		$this->allowOnlyDevAccess = true;
	}
	
	protected function isUserAuthotized(): bool
	{
		return true;
	}
}

?>