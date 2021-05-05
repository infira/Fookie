<?php

namespace Infira\Fookie\request;

use Infira\Utils\Http;
use AppConfig;
use Path;
use Infira\Fookie\facade\Variable;
use stdClass;
use Infira\Fookie\controller\Controller;
use Infira\Fookie\Fookie;

class Route
{
	private static $requestID  = null;
	private static $routes     = [];
	private static $options    = [];
	private static $matchTypes = [];
	
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
		self::setMatchType('entity', '[A-Za-z]++');
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
		if ($url && $url{0} == '/')
		{
			$url = substr($url, 1);
		}
		
		return $url;
	}
	
	public static function saveRequestResponse($response)
	{
		if (!AppConfig::saveRequests() or !self::$requestID)
		{
			return;
		}
		$config = AppConfig::saveRequests();
		$model  = $config['model'];
		$db     = new $model();
		$db->Where->ID(self::$requestID);
		if (is_array($response) or is_object($response))
		{
			$db->response->compress(json_encode($response));
		}
		else
		{
			$db->response->compress($response);
		}
		$db->update();
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
				$target->path = $Route->path;
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
		//debug(['$match' => $match]);
		if (!$match)
		{
			if (!in_array(Http::getRequestMethod(), ["propfind", "options", "option"]))
			{
				if (!AppConfig::isLiveWorthy())
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
			self::$RouteNode->rawPath          = $match->target->path;
			self::$RouteNode->controller       = $match->target->controller;
			self::$RouteNode->controllerMethod = $match->target->method;
			self::$RouteNode->name             = $match->name;
			self::$RouteNode->path             = $requestUrlRoute;
			self::$RouteNode->isAjax           = (substr($requestUrlRoute, 0, 5) == "ajax/");
			self::$RouteNode->role             = $match->target->role;
			
			if (is_callable($match->target->controller))
			{
				$m          = $match->target->controller;
				$controller = $m($match);
				if ($controller === false)
				{
					return false;
				}
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
			self::$RouteNode->target           = $match->target;
			self::$RouteNode->params           = $match->params;
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
		
		if (Http::existsGET("ajaxMethodArguments"))
		{
			$controllerMethodArguments = array_merge($controllerMethodArguments, Variable::toArray(json_decode(Http::getGET("ajaxMethodArguments"))));
		}
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
				
				if (checkArray($requestPayload->ajaxMethodArguments))
				{
					$controllerMethodArguments = array_merge($controllerMethodArguments, $requestPayload->ajaxMethodArguments);
				}
				unset($requestPayload->ajaxMethodArguments);
				
				foreach ((array)$requestPayload as $name => $val)
				{
					Http::setPOST($name, $val);
				}
			}
		}
		if (Http::existsPOST("ajaxMethodArguments"))
		{
			$controllerMethodArguments = array_merge($controllerMethodArguments, Variable::toArray(Http::getPOST("ajaxMethodArguments")));
		}
		
		if (AppConfig::saveRequests())
		{
			$config    = AppConfig::saveRequests();
			$saveModel = $config['model'];
			$db        = new $saveModel();
			if (Http::existsGET('_rrid'))
			{
				
				$db->ID(Http::getGET('_rrid'));
				$req                       = $db->select()->getObject();
				$controllerMethodArguments = unserialize($req->methodArguments);
				$post                      = unserialize($req->post);
				addExtraErrorInfo('saved$req', $req);
				addExtraErrorInfo('errorReplicateLink', Http::getCurrentUrl());
				addExtraErrorInfo('$controllerMethodArguments', $controllerMethodArguments);
				Http::flushPOST((is_array($post) ? $post : []));
				$_SERVER['REQUEST_METHOD'] = $req->method;
			}
			else
			{
				$db->methodArguments->json($controllerMethodArguments);
				$db->post->compress(json_encode(Http::getPOST()));
				$db->headers->compress(json_encode(getallheaders()));
				$db->method($_SERVER['REQUEST_METHOD']);
				$db->uri($_SERVER['REQUEST_URI']);
				$db->insert();
				self::$requestID = $db->getLastSaveID();
				Payload::setRequestID(self::$requestID);
			}
		}
		
		if (!is_array($controllerMethodArguments))
		{
			$controllerMethodArguments = [];
		}
		$controllerName = self::getControllerName();
		addExtraErrorInfo("currentControllerName", $controllerName);
		$methodName = self::$RouteNode->controllerMethod;
		
		$Controller = self::getController();
		$Controller->authorize();
		
		if (!method_exists($Controller, "validate"))
		{
			Fookie::error('Controller must contain validate method', null, 500);
		}
		
		if (!$Controller->validate())
		{
			Payload::set(null);
			
			return;
		}
		if (Payload::haveError())
		{
			//
		}
		elseif (method_exists($Controller, $methodName))
		{
			if (method_exists($Controller, 'beforeAction'))
			{
				$Controller->beforeAction();
			}
			$controllerMethodArguments = array_merge($controllerMethodArguments, $Controller->getActionArguments());
			$actionResult              = $Controller->$methodName(...$controllerMethodArguments);
			if (method_exists($Controller, 'resultParser'))
			{
				$actionResult = $Controller->resultParser($actionResult);
			}
			Payload::set($actionResult);
		}
		else
		{
			Fookie::error("$controllerName->$methodName does not exists", null, 500);
		}
	}
	
	public static function map(string $role, string $name, string $requestMethod, string $requestPath, $controller)
	{
		if (isset(self::$routes[$role][$name]))
		{
			Fookie::error("Route $role.$name is already defined", null, 500);
		}
		$controller = is_callable($controller) ? $controller : trim($controller);
		if (is_string($controller))
		{
			if (strpos($controller, '#') === false)
			{
				Fookie::error("Controller($controller) method is undefined", null, 500);
			}
		}
		self::$routes[$role][$name] = (object)['method' => trim($requestMethod), 'path' => trim($requestPath), 'controller' => $controller];
	}
	
	public static function setMatchType(string $name, string $expression)
	{
		if (array_key_exists($name, self::$matchTypes))
		{
			Fookie::error("Match type already defined", null, 500);
		}
		self::$matchTypes[$name] = $expression;
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
	
	public static function getParams(): array
	{
		return self::$RouteNode->params;
	}
	
	public static function getRawPath()
	{
		return self::$RouteNode->rawPath;
	}
	
	/**
	 * Entitites replaces with real values
	 *
	 * @return string
	 */
	public static function getPathStatement(): string
	{
		$path = self::$RouteNode->rawPath;
		foreach (self::$RouteNode->params as $name => $value)
		{
			$path = str_replace("[entity:$name]", $value, $path);
		}
		
		return $path;
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
	
	public static function getControllerName(): string
	{
		return self::$RouteNode->controller;
	}
	
	public static function getController(): Controller
	{
		$cn = self::getControllerName();
		
		return new $cn();
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