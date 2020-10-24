<?php

namespace Infira\Fookie;
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
	
	public static final function root($path = "", $getAsUrl = false)
	{
		if (func_num_args() > 2)
		{
			alert("Cannot use over 2 arguments");
		}
		if ($path === false)
		{
			$path = "";
		}
		if (!is_string($path))
		{
			addExtraErrorInfo("path", var_dump($path));
			addExtraErrorInfo("pathType", gettype($path));
			alert("Must be string");
		}
		if (is_dir($path))
		{
			$path = self::fix($path);
		}
		$path = str_replace(BASE_DIR, "", $path);
		$path = BASE_DIR . $path;
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
			$root = Path::root();
		}
		$path = str_replace($root, "", $path);
		$path = str_replace(Path::root(false, true), "", $path);
		$path = str_replace(self::fix(realpath(self::root("../"))), "", $path);
		
		return $path;
	}
	
}

?>