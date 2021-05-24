<?php

namespace Infira\Fookie;

abstract class PathHandler
{
	public static final function root(string $path = "", $getAsUrl = false): string
	{
		return self::base(BASE_DIR, $path, $getAsUrl);
	}
	
	public static function app(string $path = "", $getAsUrl = false): string
	{
		return self::base(APP_DIR, $path, $getAsUrl);
	}
	
	public static function temp(string $file = "", bool $getAsUrl = false): string
	{
		return self::base(TEMP_DIR, $file, $getAsUrl);
	}
	
	private static function base(string $base, string $path = "", $getAsUrl = false): string
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
	
	public static function urlPseudo(string $string): string
	{
		return str_replace(self::root(false, true), "[BASE_URL]", $string);
	}
	
	public static function pseudoUrl(string $string): string
	{
		return str_replace("[BASE_URL]", self::root(false, true), $string);
	}
	
	public static function pseudoPath(string $string): string
	{
		return str_replace(["[BASE_URL]", "%5BBASE_URL%5D"], self::root(), $string);
	}
	
	public static function toPath(string $path): string
	{
		return str_replace(SITE_URL, BASE_DIR, $path);
	}
	
	public static function toUrl(string $path): string
	{
		$dir = str_replace(DIRECTORY_SEPARATOR, "/", $path);
		$dir = str_replace(BASE_DIR, SITE_URL, $dir);
		$dir = str_replace(SITE_URL, "", $dir);
		
		return SITE_URL . $dir;
	}
	
	public static final function fix(string $dir): string
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
	
	public static function assets(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("assets/" . $file, $getAsUrl);
	}
	
	public static function controller(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("controller/" . $file, $getAsUrl);
	}
	
	public static function facade(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("facade/" . $file, $getAsUrl);
	}
	
	public static function helper(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("helper/" . $file, $getAsUrl);
	}
	
	public static function model(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("model/" . $file, $getAsUrl);
	}
	
	public static function modelModels(string $file = "", bool $getAsUrl = false): string
	{
		return self::model("models/" . $file, $getAsUrl);
	}
	
	public static function modelExtensions(string $file = "", bool $getAsUrl = false): string
	{
		return self::model("extensions/" . $file, $getAsUrl);
	}
	
	public static function service(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("service/" . $file, $getAsUrl);
	}
	
	public static function view(string $file = "", bool $getAsUrl = false): string
	{
		return self::app("view/" . $file, $getAsUrl);
	}
	
	//################################################################### EOF APP paths
	
	//################################################################### SOF Fookie paths
	public static function fookie(string $file = "", bool $getAsUrl = false): string
	{
		return self::root("vendor/infira/fookie/src/" . $file, $getAsUrl);
	}
	
	public static function fookieJS(string $file = "", bool $getAsUrl = false): string
	{
		return self::fookie("js/" . $file, $getAsUrl);
	}
	
	public static function fookieTraits(string $file = "", bool $getAsUrl = false): string
	{
		return self::fookie("traits/" . $file, $getAsUrl);
	}
	
	public static function db(string $file = "", bool $getAsUrl = false): string
	{
		return self::root("db/" . $file, $getAsUrl);
	}
	
	public static function dbViews(string $file = "", bool $getAsUrl = false): string
	{
		return self::db("views/" . $file, $getAsUrl);
	}
	
	public static function dbTriggers(string $file = "", bool $getAsUrl = false): string
	{
		return self::db("triggers/" . $file, $getAsUrl);
	}
	
	public static function systemUpdates(string $file = "", bool $getAsUrl = false): string
	{
		return self::db("updates/" . $file, $getAsUrl);
	}
	
	//################################################################### SOF Fookie paths
	
	abstract public static function config(string $file = "", bool $getAsUrl = false);
}

?>