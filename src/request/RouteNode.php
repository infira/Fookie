<?php

namespace Infira\Fookie\request;

class RouteNode
{
	public $matched = false;
	public $name;
	public $path;
	public $controller;
	public $controllerMethod;
	public $target;
	public $route;
	public $isAjax;
	public $role    = '';
}

?>