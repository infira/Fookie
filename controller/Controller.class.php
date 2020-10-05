<?php

use Infira\Utils\ClassFarm;

ClassFarm::add("Controller", "Controller");

/**
 * @property Controller $Controller
 */
abstract class Controller extends \Infira\Utils\MagicClass
{
	private $authRequired       = true;
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
		
		if ($this->authRequired === true and !$this->isUserAuthotized())
		{
			alert("auth requierd");
		}
		if ($this->allowOnlyDevAccess == true and !AppConfig::isDevENV())
		{
			alert("oly dev envinronment can access this controller");
		}
	}
	
	/**
	 * Ghot method to enuse called contoller exteds this class
	 */
	public function validate()
	{
		if (!$this->constructorCalled)
		{
			alert(get_class($this) . ' __construct must be initialized');
		}
		
		return true;
	}
	
	private function isUserAuthotized()
	{
		return true;
	}
	
	/**
	 * Set current controller to void authorization
	 */
	protected function allowUnAuthorisedAccess()
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
}

?>