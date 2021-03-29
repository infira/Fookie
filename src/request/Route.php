<?php

namespace Infira\Fookie\request;

use Infira\Utils\Http;
use AppConfig;
use Path;
use Infira\Fookie\facade\Variable;
use Infira\Fookie\Fookie;
use stdClass;
use Infira\Fookie\facade\Db;

class Route
{
	private        $route;
	private static $routes  = [];
	private static $options = [];
	
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
	private static $blockedHTTPOrigin = [];
	
	public static function init()
	{
		self::$defaultRole = AppConfig::routeDefaultRole();
		self::$role        = AppConfig::routeCurrent();
		self::$Alto        = new AltoRouterExtendor();
		self::$RouteNode   = new RouteNode();
		
		self::map('system', 'Operation', 'GET', 'op/[:opName]', function ()
		{
			$operationController = self::opt('operationController') ? self::opt('operationController') : '\Infira\Fookie\controller\Operation';
			
			return (object)['controller' => $operationController, 'method' => 'handle'];
		});
		self::blockHTTPOrigin("chrome-extension://aegnopegbbhjeeiganiajffnalhlkkjb"); //it causes lots of _post requests to server it is that plugin https://chrome.google.com/webstore/detail/browser-safety/aegnopegbbhjeeiganiajffnalhlkkjb
	}
	
	public static function getRequestUrl()
	{
		$ex  = explode('?', $_SERVER['REQUEST_URI'], 2);
		$url = trim($ex[0]);
		$len = strlen($url);
		if ($len > 0)
		{
			if ($url{$len - 1} == "/")
			{
				$url = substr($url, 0, -1);
			}
		}
		if ($url{0} == '/')
		{
			$url = substr($url, 1);
		}
		
		return $url;
	}
	
	public static function match(): bool
	{
		Prof()->startTimer("Route::detect");
		if (Http::getRequestMethod() == "head") //in case of head must return "" https://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html
		{
			exit;
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
				$target       = new stdClass();
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
		if (!$match)
		{
			if (!in_array(Http::getRequestMethod(), ["propfind", "options", "option"]))
			{
				if (!AppConfig::isLiveENV())
				{
					Payload::setField("requestUrlRoute", $requestUrlRoute);
					Payload::setField("trace", getTrace());
					Payload::setField("routes", self::$Alto->getRoutes());
				}
				
				return false;
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
			//Set router data
			self::$RouteNode->controller       = $match->target->controller;
			self::$RouteNode->controllerMethod = $match->target->method;
			self::$RouteNode->matched          = true;
		}
		Prof()->stopTimer("Route::detect");
		
		return true;
	}
	
	public static function runController()
	{
		if (!self::isMatched())
		{
			return false;
		}
		$controllerMethodArguments = [];
		$contentType               = '';
		if (isset($_SERVER["CONTENT_TYPE"]) and preg_match('%application/json%', $_SERVER["CONTENT_TYPE"]))
		{
			$contentType = $_SERVER["CONTENT_TYPE"];
		}
		if ($_SERVER["REQUEST_METHOD"] == "POST" && preg_match('%application/json%', $contentType))
		{
			if ($_SERVER["REQUEST_METHOD"] == "POST" && preg_match('%application/json%', $contentType))
			{
				$requestPayload = json_decode(file_get_contents("php://input", false, stream_context_get_default(), 0, $_SERVER["CONTENT_LENGTH"]));
				addExtraErrorInfo('php://input', $requestPayload);
				
				$requestPayload = Variable::apply((object)[], $requestPayload, ["ajaxMethodArguments" => null]);
				
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
			}
		}
		
		if (Http::existsGET('_rid') and AppConfig::saveRequests())
		{
			$ID = Http::getGET('_rid');
			$Db = Db::TSavedRequest();
			$Db->ID($ID);
			$Db->methodArguments->serialize($controllerMethodArguments);
			$Db->post->serialize(Http::getPOST());
			$Db->method($_SERVER['REQUEST_METHOD']);
			$Db->uri($_SERVER['REQUEST_URI']);
			$Db->insert();
			$currentUrl = Http::getCurrentUrl();
			if (preg_match('/_rid/', $currentUrl))
			{
				$repLink = str_replace('_rid', '_rrid', $currentUrl);
			}
			else
			{
				if (strpos($currentUrl, '?') !== false)
				{
					$repLink = $currentUrl . '&_rrid=' . $ID;
				}
				else
				{
					$repLink = $currentUrl . '?_rrid=' . $ID;
				}
			}
			addExtraErrorInfo('errorReplicateLink', $repLink);
			Payload::setField('repLink', $repLink);
			Payload::setField('repID', Http::getGET('_rid'));
		}
		
		
		if (Http::existsGET('_rrid') and AppConfig::saveRequests())
		{
			$Db = Db::TSavedRequest();
			$Db->ID(Http::getGET('_rrid'));
			$req                       = $Db->select()->getObject();
			$controllerMethodArguments = unserialize($req->methodArguments);
			$post                      = unserialize($req->post);
			addExtraErrorInfo('saved$req', $req);
			addExtraErrorInfo('errorReplicateLink', Http::getCurrentUrl());
			addExtraErrorInfo('$controllerMethodArguments', $controllerMethodArguments);
			Http::flushPOST((is_array($post) ? $post : []));
			$_SERVER['REQUEST_METHOD'] = $req->method;
		}
		
		if (!is_array($controllerMethodArguments))
		{
			$controllerMethodArguments = [];
		}
		$controllerName = self::$RouteNode->controller;
		addExtraErrorInfo("currentControllerName", $controllerName);
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
			if (AppConfig::isLiveENV())
			{
				Payload::setError("$controllerName->$methodName does not exists");
			}
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
	
	public static function isRole(string $checkRole): bool
	{
		return self::$RouteNode->role == $checkRole;
	}
	
	public static function getController()
	{
		return self::$RouteNode->controller;
	}
	
	public static function getControllerMethod(): string
	{
		return self::$RouteNode->controllerMethod;
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
	
	public static function isMatched(): bool
	{
		return self::$RouteNode->matched;
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
		return Path::root(substr(self::getLink($pathOrName, $params), 1), true);
	}
	
	public static function go(string $pathOrName = '', $params = null)
	{
		if (Http::isAjax())
		{
			addExtraErrorInfo("goToPage", getTrace());
			alert("Cant redirect on ajax");
		}
		Http::go(self::getLink($pathOrName, $params));
	}
	
	//######################## Options
	
	public static function setOperationController(string $controller)
	{
		self::setOpt('operationController', $controller);
	}
	
	public static function optExists(string $name)
	{
		return array_key_exists($name, self::$options);
	}
	
	public static function setOpt(string $name, $value)
	{
		self::$options[$name] = $value;
	}
	
	public static function opt(string $name)
	{
		if (!self::optExists($name))
		{
			return false;
		}
		
		return self::$options[$name];
	}
}

?>