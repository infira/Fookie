<?php

namespace Infira\Fookie\request;

class RouteNode
{
	public $matched = false;
	public $name;
	public $path;
	public $rawPath;
	public $controller;
	public $controllerMethod;
	public $target;
	public $params;
	public $route;
	public $isAjax;
	public $role    = '';
}

?>