<?php

namespace Infira\Fookie\request;

use Infira\Fookie\facade\Http;
use AppConfig;
use Path;
use Infira\Fookie\facade\Variable;
use \Infira\Fookie\facade\Session;
use Infira\Fookie\Fookie;

class Route
{
	private        $route;
	private static $routes = [];
	
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
	private static $httpVarName       = "route";
	private static $path;
	private static $blockedHTTPOrigin = [];
	
	public static function init()
	{
		self::$httpVarName = AppConfig::routeGETParameter();
		self::$defaultRole = AppConfig::routeDefaultRole();
		self::$role        = AppConfig::routeCurrent();
		self::$path        = Path::fix(Http::getGET(self::$httpVarName, ""));
		self::$Alto        = new AltoRouterExtendor();
		self::map('system', 'ControlPanelDashboard', 'GET', 'controlpanel', '\Infira\Fookie\controller\ControlPanel#index');
		self::map('system', 'ControlPanelSubClass', 'GET', 'controlpanel/[:subClass]', '\Infira\Fookie\controller\ControlPanel#subClass');
		
		$operationController = Fookie::optExists('operationController') ? Fookie::opt('operationController') : '\Infira\Fookie\controller\Operation';
		self::map('system', 'Operation', 'GET', 'op/[:opName]', "$operationController#handle");
		self::blockHTTPOrigin("chrome-extension://aegnopegbbhjeeiganiajffnalhlkkjb"); //it causes lots of _post requests to server it is that plugin https://chrome.google.com/webstore/detail/browser-safety/aegnopegbbhjeeiganiajffnalhlkkjb
	}
	
	public static function getRequestUrl()
	{
		$url = trim(Http::getGET(self::$httpVarName));
		$len = strlen($url);
		if ($len > 0)
		{
			if ($url{$len - 1} == "/")
			{
				$url = substr($url, 0, -1);
			}
		}
		
		return $url;
	}
	
	public static function detect()
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
		if (isset($_SERVER["HTTP_ORIGIN"]))
		{
			if (isset(self::$blockedHTTPOrigin[$_SERVER["HTTP_ORIGIN"]]))
			{
				exit("HTTP_ORIGIN not allowed");
			}
		}
		
		addExtraErrorInfo("currentRole", self::$role);
		$addRoutes = function ($routes, $roleName)
		{
			foreach ($routes as $role => $Route)
			{
				$target       = new \stdClass();
				$target->role = $roleName;
				if (is_string($Route->controller))
				{
					$ex                 = explode("#", $Route->controller);
					$target->controller = $ex[0];
					$target->method     = $ex[1];
				}
				else
				{
					$target->controller = $Route->controller;
					$target->method     = null;
				}
				self::$Alto->map($Route->method, $Route->path, $target, "$roleName.$role");
			}
		};
		foreach (self::$routes as $role => $routes)
		{
			if ($role == "__ALL_ROLES__")
			{
				foreach (array_keys(self::$routes) as $rn)
				{
					if ($rn != '__ALL_ROLES__')
					{
						$addRoutes($routes, $rn);
					}
				}
			}
			else
			{
				$addRoutes($routes, $role);
			}
		}
		$requestUrlRoute = self::getRequestUrl();
		$match           = self::$Alto->match($requestUrlRoute);
		//debug($match);exit;
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
			$match = (object)$match;
			addExtraErrorInfo("routeMatch", $match);
			self::$RouteNode->controller       = $match->target->controller;
			self::$RouteNode->controllerMethod = $match->target->method;
			self::$RouteNode->name             = $match->name;
			self::$RouteNode->path             = $requestUrlRoute;
			self::$RouteNode->isAjax           = (substr($requestUrlRoute, 0, 5) == "ajax/");
			self::$RouteNode->role             = $match->target->role;
			
			if (is_callable($match->target->controller))
			{
				$m                         = $match->target->controller;
				$controller                = $m($match);
				$match->target->controller = $controller->controller;
				$match->target->method     = $controller->method;
			}
			define("ROUTE_TYPE", $match->target->role);
			define("ROUTE_PATH", $requestUrlRoute);
			foreach ($match->params as $key => $val)
			{
				Http::setGET($key, $val);
			}
			
			$match->extra = new \stdClass();
			if (strpos($match->target->controller, '[') !== false)
			{
				$getNameFrom               = str_replace(['[', ']'], '', $match->target->controller);
				$match->target->controller = $match->extra->$getNameFrom;
			}
			
			if (strpos($match->target->method, '[') !== false)
			{
				$getNameFrom           = str_replace(['[', ']'], '', $match->target->method);
				$match->target->method = $match->extra->$getNameFrom;
			}
			//Set router data
			self::$RouteNode->controller       = $match->target->controller;
			self::$RouteNode->controllerMethod = $match->target->method;
		}
		Prof()->stopTimer("Route::detect");
	}
	
	public static function boot()
	{
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
			if (Http::existsPOST("ajaxMethodArguments"))
			{
				$controllerMethodArguments = array_merge(Variable::toArray(Http::getPOST("ajaxMethodArguments"), $controllerMethodArguments));
			}
			
			if (Http::existsGET('_sr') and AppConfig::isDevENV())
			{
				$sr = Http::getGET('_sr');
				Session::set("savedPost-$sr", Http::getPOST());
				Session::set("saveControllerMethodArguments-$sr", $controllerMethodArguments);
			}
		}
		
		
		if (Http::existsGET('_rr') and AppConfig::isDevENV())
		{
			$rr                        = Http::getGET('_rr');
			$post                      = Session::get("savedPost-$rr", Http::getPOST());
			$controllerMethodArguments = Session::get("saveControllerMethodArguments-$rr", $controllerMethodArguments);
			Http::flushPOST((is_array($post) ? $post : []));
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
		
		
		$methodName = self::$RouteNode->controllerMethod;
		$Controller = new $controllerName();
		$Controller->validate();
		if (Payload::haveError())
		{
			//
		}
		elseif (method_exists($Controller, $methodName))
		{
			$actionResult = $Controller->$methodName(...$controllerMethodArguments);
			Payload::set($actionResult);
		}
		else
		{
			alert("$controllerName->$methodName not existing");
		}
	}
	
	public static function map(string $role, string $name, string $requestMethod, string $requestPath, $controller)
	{
		if (isset(self::$routes[$role][$name]))
		{
			alert("Route $role.$name is already defined");
		}
		$controller = is_callable($controller) ? $controller : trim($controller);
		if (is_string($controller))
		{
			if (strpos($controller, '#') === false)
			{
				alert("Controller($controller) method is undefined");
			}
		}
		self::$routes[$role][$name] = (object)['method' => trim($requestMethod), 'path' => trim($requestPath), 'controller' => $controller];
	}
	
	public static function setMatchType($name, $expression)
	{
		self::$Alto->addMatchTypes([$name => $expression]);
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
	
	public static function getRole(): ?string
	{
		return self::$RouteNode->role;
	}
	
	public static function isRole(string $checkRole)
	{
		return self::$RouteNode->role == $checkRole;
	}
	
	public static function getController()
	{
		return self::$RouteNode->controller;
	}
	
	public static function getControllerMethod()
	{
		return self::$RouteNode->method;
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
			
			return "/" . self::$Alto->generate($routeName, $params);
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
	
	public static function getOperationLink($params = null)
	{
		return self::getFullLink('Operation', $params);
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