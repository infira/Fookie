<?php

namespace Infira\Fookie;

use Path;

abstract class PathHandler
{
	public static function init()
	{
		$protocol = false;
		if (!$protocol)
		{
			$serverUrl = 'http';
			if (isset($_SERVER['HTTPS']))
			{
				$isHttps = strtolower($_SERVER['HTTPS']);
				if ($isHttps == 'on')
				{
					$serverUrl .= 's';
				}
			}
			$serverUrl .= '://';
		}
		else
		{
			$serverUrl = $protocol;
		}
		$serverUrl .= $_SERVER['HTTP_HOST'];
		if (substr($serverUrl, -1) != "/")
		{
			$serverUrl .= "/";
		}
		define("SITE_URL", $serverUrl);
	}
	
	public static final function root(string $path = "", $getAsUrl = false)
	{
		return self::base(BASE_DIR, $path, $getAsUrl);
	}
	
	public static final function app(string $path = "", $getAsUrl = false)
	{
		return self::base(APP_DIR, $path, $getAsUrl);
	}
	
	public static final function temp($file = "", $getAsUrl = false)
	{
		return self::base(TEMP_DIR, $file, $getAsUrl);
	}
	
	private static final function base(string $base, string $path = "", $getAsUrl = false)
	{
		if (is_dir($path))
		{
			$path = self::fix($path);
		}
		$base = self::fix($base);
		if (!is_dir($base))
		{
			alert("Basedir($base) must be dir");
		}
		$path = str_replace($base, "", $path);
		$path = $base . $path;
		if ($getAsUrl)
		{
			return self::toUrl($path);
		}
		else
		{
			return self::toPath($path);
		}
	}
	
	public static final function urlPseudo($string)
	{
		return str_replace(self::root(false, true), "[BASE_URL]", $string);
	}
	
	public static final function pseudoUrl($string)
	{
		return str_replace("[BASE_URL]", self::root(false, true), $string);
	}
	
	public static final function pseudoPath($string)
	{
		return str_replace(["[BASE_URL]", "%5BBASE_URL%5D"], self::root(), $string);
	}
	
	public static final function toPath($path)
	{
		return str_replace(SITE_URL, BASE_DIR, $path);
	}
	
	public static final function toUrl($path)
	{
		$dir = str_replace(DIRECTORY_SEPARATOR, "/", $path);
		$dir = str_replace(BASE_DIR, SITE_URL, $dir);
		$dir = str_replace(SITE_URL, "", $dir);
		
		return SITE_URL . $dir;
	}
	
	public static final function fix($dir)
	{
		if ($dir) //if empty reutrn empty
		{
			if (is_file($dir))
			{
				return $dir;
			}
			$dir = str_replace("/", DIRECTORY_SEPARATOR, $dir);
			$len = strlen($dir) - 1;
			if ($dir{$len} != DIRECTORY_SEPARATOR and !is_file($dir))
			{
				$dir .= DIRECTORY_SEPARATOR;
			}
		}
		
		return $dir;
	}
	
	public static function relative($path, $root = null)
	{
		if (is_dir($path))
		{
			$path = self::fix($path);
		}
		if (!$root)
		{
			$root = self::root();
		}
		$path = str_replace($root, "", $path);
		$path = str_replace(self::root(false, true), "", $path);
		$path = str_replace(self::fix(realpath(self::root("../"))), "", $path);
		
		return $path;
	}
	
	//################################################################### SOF APP paths
	
	public static final function assets($file = "", $getAsUrl = false)
	{
		return self::app("assets/" . $file, $getAsUrl);
	}
	
	public static final function controller($file = "", $getAsUrl = false)
	{
		return self::app("controller/" . $file, $getAsUrl);
	}
	
	public static final function facade($file = "", $getAsUrl = false)
	{
		return self::app("facade/" . $file, $getAsUrl);
	}
	
	public static final function helper($file = "", $getAsUrl = false)
	{
		return self::app("helper/" . $file, $getAsUrl);
	}
	
	public static final function model($file = "", $getAsUrl = false)
	{
		return self::app("model/" . $file, $getAsUrl);
	}
	
	public static final function modelModels($file = "", $getAsUrl = false)
	{
		return self::model("models/" . $file, $getAsUrl);
	}
	
	public static final function modelExtensions($file = "", $getAsUrl = false)
	{
		return self::model("extensions/" . $file, $getAsUrl);
	}
	
	public static final function service($file = "", $getAsUrl = false)
	{
		return self::app("service/" . $file, $getAsUrl);
	}
	
	public static final function view($file = "", $getAsUrl = false)
	{
		return self::app("view/" . $file, $getAsUrl);
	}
	
	//################################################################### EOF APP paths
	
	//################################################################### SOF Fookie paths
	public static final function fookie($file = "", $getAsUrl = false)
	{
		return self::root("vendor/infira/fookie/src/" . $file, $getAsUrl);
	}
	
	public static final function fookieJS($file = "", $getAsUrl = false)
	{
		return self::fookie("js/" . $file, $getAsUrl);
	}
	
	public static final function fookieTraits($file = "", $getAsUrl = false)
	{
		return self::fookie("traits/" . $file, $getAsUrl);
	}
	
	public static function db(string $file = "", bool $getAsUrl = false)
	{
		return self::root("db/" . $file, $getAsUrl);
	}
	
	public static function dbViews(string $file = "", bool $getAsUrl = false)
	{
		return self::db("views/" . $file, $getAsUrl);
	}
	
	public static function dbTriggers(string $file = "", bool $getAsUrl = false)
	{
		return self::db("triggers/" . $file, $getAsUrl);
	}
	
	public static function systemUpdates(string $file = "", bool $getAsUrl = false)
	{
		return self::db("updates/" . $file, $getAsUrl);
	}
	
	//################################################################### SOF Fookie paths
	
	abstract public static function config(string $file = "", bool $getAsUrl = false);
}

?>