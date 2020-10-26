<?php

namespace Infira\Fookie\request;

use Infira\Fookie\facade\Http;
use AppConfig;
use Path;
use Infira\Fookie\facade\Variable;

class Route
{
	private $route;
	
	/**
	 * @var AltoRouterExtendor
	 */
	private static $Alto;
	
	/**
	 * @var RouteNode
	 */
	private static $RouteNode;
	
	private static $defaultRole;
	private static $role;
	private static $roles;
	
	private static $httpVarName = "route";
	
	private static $path;
	
	private static $blockedHTTPOrigin = [];
	private static $Config            = [];
	private static $systemRoutes      = [];
	
	public static function init()
	{
		self::$httpVarName                                                 = AppConfig::routeGETParameter();
		self::$defaultRole                                                 = AppConfig::routeDefaultRole();
		self::$role                                                        = AppConfig::routeCurrent();
		self::$roles                                                       = AppConfig::routeRoles() ? AppConfig::routeRoles() : [];
		self::$path                                                        = Path::fix(Http::getGET(self::$httpVarName, ""));
		self::$Alto                                                        = new AltoRouterExtendor();
		self::$Config                                                      = AppConfig::getRoutes();
		self::$systemRoutes["__ALL_ROLES__"]["ControlPanelDashboard"]      = "GET => controlpanel => Infira\Fookie\controller\ControlPanel#index";
		self::$systemRoutes["__ALL_ROLES__"]["ControlPanelSubClass"]       = "GET => controlpanel/[:subClass] => Infira\Fookie\controller\ControlPanel#subClass";
		self::$systemRoutes["__ALL_ROLES__"]["OperationControllerStarter"] = "GET => op/[:opName] => OperationController#handle";
		foreach (self::$Config->matchTypes as $name => $expression)
		{
			self::addMatchTypes($name, $expression);
		}
		self::blockHTTPOrigin("chrome-extension://aegnopegbbhjeeiganiajffnalhlkkjb"); //it causes lots of _post requests to server it is that plugin https://chrome.google.com/webstore/detail/browser-safety/aegnopegbbhjeeiganiajffnalhlkkjb
	}
	
	public static function addMatchTypes($name, $expression)
	{
		if (!is_string($expression))
		{
			alert("Expression must be string");
		}
		self::$Alto->addMatchTypes([$name => $expression]);
	}
	
	public static function handle()
	{
		Prof()->startTimer("Route::detect");
		
		self::$RouteNode = new RouteNode();
		if (Http::getRequestMethod() == "head") //in case of head must return "" https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
		{
			exit;
		}
		if (!Http::existsGET(self::$httpVarName))
		{
			Http::setGET(self::$httpVarName, "");
		}
		$requestUrlRoute = trim(Http::getGET(self::$httpVarName));
		$len             = strlen($requestUrlRoute);
		if ($len > 0)
		{
			if ($requestUrlRoute{$len - 1} == "/")
			{
				$requestUrlRoute = substr($requestUrlRoute, 0, -1);
			}
		}
		
		if (isset($_SERVER["HTTP_ORIGIN"]))
		{
			if (isset(self::$blockedHTTPOrigin[$_SERVER["HTTP_ORIGIN"]]))
			{
				exit("HTTP_ORIGIN not allowed");
			}
		}
		
		
		if (!checkArray(self::$Config->routes))
		{
			alert("routes should not be empty");
		}
		addExtraErrorInfo("currentRole", self::$role);
		$addRoutes = function ($routes, $roleName)
		{
			foreach ($routes as $routeName => $val)
			{
				$ex     = explode("=>", $val);
				$method = trim($ex[0]);
				$path   = trim($ex[1]);
				$target = explode("#", trim($ex[2]));
				
				if (substr($path, 0, 5) == "ajax/")
				{
					$routeName = "$roleName.ajax.$routeName";
				}
				else
				{
					$routeName = "$roleName.$routeName";
				}
				self::$Alto->map($method, $path, ['controller' => $target[0], 'action' => (isset($target[1])) ? $target[1] : "", "roleName" => $roleName], $routeName);
			}
		};
		foreach (self::$systemRoutes as $roleName => $routeVars)
		{
			if ($roleName == "__ALL_ROLES__")
			{
				foreach (self::$roles as $rn)
				{
					$addRoutes($routeVars, $rn);
				}
			}
			else
			{
				$addRoutes($routeVars, $roleName);
			}
		}
		foreach (self::$Config->routes as $roleName => $routeVars)
		{
			if ($roleName == "__ALL_ROLES__")
			{
				foreach (self::$roles as $rn)
				{
					$addRoutes($routeVars, $rn);
				}
			}
			else
			{
				$addRoutes($routeVars, $roleName);
			}
		}
		
		$match = self::$Alto->match($requestUrlRoute);
		if (!$match)
		{
			if (!in_array(Http::getRequestMethod(), ["propfind", "options", "option"]))
			{
				addExtraErrorInfo("requestUrlRoute", $requestUrlRoute);
				addExtraErrorInfo("trace", getTrace());
				addExtraErrorInfo("routes", self::$Alto->getRoutes());
				if (AppConfig::isDevENV())
				{
					alert("route not found : " . (string)$requestUrlRoute);
				}
				else
				{
					exit("route not found : " . (string)$requestUrlRoute);
				}
			}
		}
		else
		{
			$match = Variable::toObject($match, true);
			addExtraErrorInfo("routeInfo", $match);
			// Call session name before session start
			define("USE_SESSION_NAME", $match->target->roleName);
			define("ROUTE_TYPE", $match->target->roleName);
			define("ROUTE_PATH", $requestUrlRoute);
			foreach ($match->params as $key => $val)
			{
				Http::setGET($key, $val);
			}
			
			$match->extra = new \stdClass();
			if (isset(self::$Config->matchParsers[$match->name]))
			{
				self::$Config->matchParsers[$match->name]($match);
			}
			if (strpos($match->target->controller, '[') !== false)
			{
				$getNameFrom               = str_replace(['[', ']'], '', $match->target->controller);
				$match->target->controller = $match->extra->$getNameFrom;
			}
			
			if (strpos($match->target->action, '[') !== false)
			{
				$getNameFrom           = str_replace(['[', ']'], '', $match->target->action);
				$match->target->action = $match->extra->$getNameFrom;
			}
			
			//Set router data
			self::$RouteNode->action     = (isset($match->target)) ? $match->target->action : "";
			self::$RouteNode->controller = $match->target->controller;
			self::$RouteNode->name       = str_replace(self::$role . ".", "", $match->name);
			self::$RouteNode->path       = $requestUrlRoute;
			self::$RouteNode->isAjax     = (substr($requestUrlRoute, 0, 5) == "ajax/");
		}
		Prof()->stopTimer("Route::detect");
		
		
		$controllerMethodArguments = [];
		
		if ($_SERVER["REQUEST_METHOD"] == "POST" && preg_match('%application/json%', $_SERVER["CONTENT_TYPE"]))
		{
			$requestPayload = json_decode(file_get_contents("php://input", false, stream_context_get_default(), 0, $_SERVER["CONTENT_LENGTH"]));
			
			$requestPayload = Variable::apply((object)[], $requestPayload, ["ajaxMethodArguments" => null]);
			
			if (property_exists($requestPayload, 'parseRequestAsPost'))
			{
				alert("Do not use parseRequestAsPost");
				unset($requestPayload->parseRequestAsPost);
			}
			
			if ($requestPayload->ajaxMethodArguments)
			{
				$controllerMethodArguments = $requestPayload->ajaxMethodArguments;
			}
			unset($requestPayload->ajaxMethodArguments);
			
			foreach ((array)$requestPayload as $name => $val)
			{
				Http::setPOST($name, $val);
			}
			if (Http::existsGET('_sr') and AppConfig::isDevENV())
			{
				Session::set('savedPost-' . Http::get('_sr'), Http::getPOST());
			}
			
			if (Http::existsPOST("ajaxMethodArguments"))
			{
				$controllerMethodArguments = array_merge(Variable::toArray(Http::getPOST("ajaxMethodArguments"), $controllerMethodArguments));
			}
		}
		if (Http::existsGET('_rr') and AppConfig::isDevENV())
		{
			$a = Session::get('savedPost-' . Http::get('_rr'));
			Http::flushPOST((is_array($a) ? $a : []));
			$_SERVER['REQUEST_METHOD'] = 'post';
		}
		
		if (!is_array($controllerMethodArguments))
		{
			$controllerMethodArguments = [];
		}
		$controllerName = self::getController();
		addExtraErrorInfo("currentControllerName", $controllerName);
		if (self::in('ControlPanelDashboard,ControlPanelSubClass'))
		{
			require_once Path::fookie('controller/ControlPanel.controller.php');
		}
		$Controller = new $controllerName();
		$Controller->validate();
		$methodName = (self::getAction()) ? self::getAction() : Http::getGET("methodName");
		if (method_exists($Controller, $methodName))
		{
			$actionResult = $Controller->$methodName(...$controllerMethodArguments);
			Payload::set($actionResult);
		}
		else
		{
			alert("$controllerName->$methodName not existing");
		}
	}
	
	public static function getName()
	{
		return self::$RouteNode->name;
	}
	
	public static function getPath()
	{
		return self::$RouteNode->path;
	}
	
	public static function getRoute()
	{
		return self::$RouteNode->route;
	}
	
	public static function getController()
	{
		return self::$RouteNode->controller;
	}
	
	public static function getAction()
	{
		return self::$RouteNode->action;
	}
	
	/**
	 * Is current route name
	 *
	 * @param string $name
	 * @return bool
	 */
	public static function is(string $name)
	{
		return (self::$RouteNode->name == $name);
	}
	
	/**
	 * Check is current route name in $names
	 *
	 * @param string|array $names
	 * @return bool
	 */
	public static function in($names)
	{
		return (in_array(self::$RouteNode->name, Variable::toArray($names)));
	}
	
	public static function blockHTTPOrigin($origin)
	{
		self::$blockedHTTPOrigin[$origin] = $origin;
	}
	
	public static function getLink(string $pathOrName = '', $params = null)
	{
		if (is_string($params))
		{
			$params = parseStr($params);
		}
		if (!checkArray($params))
		{
			$params = [];
		}
		
		
		$pathOrName = trim($pathOrName);
		if (!$pathOrName)
		{
			$pathOrName = './';
		}
		if (substr($pathOrName, 0, 2) == './') //get current path link
		{
			$url  = '/' . self::getPath();
			$left = substr($pathOrName, 2);
			if ($left)
			{
				$url .= '/' . $left;
			}
		}
		elseif ($pathOrName{0} != '/')
		{
			if (strpos($pathOrName, '.') === false)
			{
				$routeName = self::$role . '.' . $pathOrName;
			}
			else
			{
				$routeName = $pathOrName;
			}
			
			return self::$Alto->generate($routeName, $params);
		}
		else
		{
			$url = $pathOrName;
		}
		if (checkArray($params))
		{
			$url .= '/?' . http_build_query($params);
		}
		$url = str_replace('//', '/', $url);
		
		return $url;
	}
	
	public static function getFullLink(string $pathOrName = '', $params = null)
	{
		return Path::root(self::getLink($pathOrName, $params), true);
	}
	
	public static function go(string $pathOrName = '', $params = null)
	{
		if (isAjaxRequest())
		{
			addExtraErrorInfo("goToPage", getTrace());
			alert("Cant redirect on ajax");
		}
		Http::go(self::getLink($pathOrName, $params));
	}
}

?>