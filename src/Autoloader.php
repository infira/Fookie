<?php

namespace Infira\Fookie;

use Infira\Fookie\PathHandler as Path;
use File;
use Infira\Utils\Regex;
use Infira\Fookie\facade\Variable;

class Autoloader
{
	private static $locations              = [];
	private static $settedIncludePaths     = [];
	private static $isInited               = false;
	private static $collectedFiles         = [];
	private static $allCollectedFiles      = [];
	private static $classLocationFileLines = [];
	public static  $updateFromConsole      = false;
	private static $maxLen                 = 0;
	
	public static function init(string $classLocationFilePath): bool
	{
		if (self::$isInited)
		{
			return true;
		}
		self::$isInited = true;
		$path           = pathinfo($classLocationFilePath, PATHINFO_DIRNAME);
		if (!is_dir($path))
		{
			self::error("TEMP path for installing autoloader not existing");
		}
		if (!is_writable($path))
		{
			self::error("TEMP path for installing autoloader is not writable");
		}
		
		Prof()->startTimer("Autoloader->loadClassLocations");
		if (!file_exists($classLocationFilePath))
		{
			self::error($classLocationFilePath . " does not exists");
		}
		require_once $classLocationFilePath;
		Prof()->stopTimer("Autoloader->loadClassLocations");
		
		spl_autoload_register(['\Infira\Fookie\Autoloader', 'loader'], true);
		
		return true;
	}
	
	private static function loader($className)
	{
		Prof()->startTimer("Autoloader->load");
		if (in_array($className, ["Memcached", "Memcache"]))
		{
			return true;
		}
		$requireFile = null;
		if (array_key_exists($className, self::$locations))
		{
			$requireFile = self::$locations[$className];
		}
		if ($requireFile)
		{
			if (!file_exists($requireFile))
			{
				self::error("Autoloader: class '$className file '$requireFile' not found");
			}
			else
			{
				require_once($requireFile);
			}
		}
		else
		{
			self::error("Autoloader: class '$className' not found");
		}
		Prof()->stopTimer("Autoloader->load");
	}
	
	public static function setPath(string $name, string $classFileLocation)
	{
		self::$locations[$name] = $classFileLocation;
	}
	
	public static function update(string $jsonFile, string $installLocation)
	{
		if (!file_exists($jsonFile))
		{
			alert("Config file not found");
		}
		$defaults             = [];
		$defaults['classMap'] = [];
		$defaults['scan']     = [];
		$config               = (array)json_decode(file_get_contents($jsonFile));
		
		$config             = array_merge($defaults, $config);
		$config['classMap'] = (array)$config['classMap'];
		
		$lines = ['<?php'];
		foreach ((array)$config['classMap'] as $class => $path)
		{
			self::collect($class, $path);
		}
		
		foreach ($config['scan'] as $item)
		{
			if (!is_dir($item->path))
			{
				self::error($item->path . ' is not a dir');
			}
			self::addIncludePath($item->path, $item->recursive);
		}
		foreach (self::$classLocationFileLines as $row)
		{
			$lines[] = str_replace('[SPACES]', str_repeat(' ', self::$maxLen - $row->len), $row->str);
		}
		$lines[] = '?>';
		
		file_put_contents($installLocation, trim(join("\n", $lines)));
		
		/*
		$namespaces = [];
		foreach ($namespaces as $install)
		{
			$dir = $install[1];
			if (!is_dir($dir))
			{
				self::error($dir . ' is not a dir');
			}
			$namespace = substr($install[0], 3);
			$dir       = trim($dir);
			$dir       = Path::fix($dir);
			if (is_dir($dir))
			{
				$handler = scandir($dir);
				foreach ($handler as $nDir)
				{
					if (!in_array($nDir, [" . git", " . svn"]))
					{
						if ($nDir != " ..")
						{
							$f = $dir . $nDir;
							if (is_file($f))
							{
								$pi = pathinfo($f);
								$ns = $namespace . '\\' . explodeAt('.', $pi['filename'], 0);
								if ($ns{(strlen($ns) - 1)} == '\\')
								{
									$ns = substr($ns, 0, -1);
								}
								if (isset($pi['extension']))
								{
									if ($pi['extension'] == 'php')
									{
										self::collect('namespaces', $ns, $f);
									}
								}
							}
						}
					}
				}
			}
		}
		*/
		
		return dump(self::$collectedFiles);
	}
	
	private static function collect($name, $file)
	{
		if (!isset(self::$allCollectedFiles[$file]))
		{
			$name = explodeAt('.', basename($name), 0);
			if (isset(self::$collectedFiles[$name]))
			{
				$msg = '<br/>Cant define autoloader class(' . $name . ') twice = ' . $file . '<br/>';
				$msg .= 'Previousliy declared: <pre>' . dump(self::$collectedFiles) . '</pre>';
				self::error($msg);
			}
			$lineStart    = 'self::$locations[\'' . $name . '\']';
			$len          = strlen($lineStart);
			self::$maxLen = max(self::$maxLen, $len);
			
			$line                           = new \stdClass();
			$line->str                      = $lineStart . '[SPACES] = \'' . $file . '\';';
			$line->len                      = $len;
			self::$classLocationFileLines[] = $line;
			self::$collectedFiles[$name]    = $file;
			self::$allCollectedFiles[$file] = true;
		}
	}
	
	private static function addIncludePath($dir, $recursive = false)
	{
		$dir = trim($dir);
		$dir = Path::fix($dir);
		if (is_dir($dir))
		{
			if (isset(self::$settedIncludePaths[$dir]))
			{
				self::error("Path($dir) is already added");
			}
			self::$settedIncludePaths[$dir] = $dir;
			foreach (glob($dir . "*.php") as $file)
			{
				$basename = basename($file);
				$src      = file_get_contents($file);
				if (Regex::isMatch('/namespace (.+)?;/m', $src))
				{
					$matches = [];
					preg_match_all('/namespace (.+)?;/m', $src, $matches);
					self::collect($matches[1][0] . '\\' . $basename, $file);
				}
				else
				{
					if (Regex::isMatch('/^class ([[A-Za-z0-9_]+)/m', $src))
					{
						$matches = [];
						preg_match_all('/^class ([[A-Za-z0-9_]+)/m', $src, $matches);
						self::collect($matches[1][0], $file);
					}
					elseif (Regex::isMatch('/^trait ([[A-Za-z0-9_]+)/m', $src))
					{
						$matches = [];
						preg_match_all('/^trait ([[A-Za-z0-9_]+)/m', $src, $matches);
						self::collect($matches[1][0], $file);
					}
				}
				
				
				if (strpos($basename, '.class') !== false)
				{
					self::collect($basename, $file);
				}
				elseif (strpos($basename, '.int') !== false)
				{
					self::collect($basename, $file);
				}
				elseif (strpos($basename, '.trait') !== false)
				{
					self::collect($basename, $file);
				}
				else
				{
					//self::collect( $basename, $file);
				}
			}
			if ($recursive)
			{
				$handler = scandir($dir);
				if (is_array($handler) and count($handler) > 0)
				{
					unset($handler[0]);
					unset($handler[1]);
					if (is_array($handler) and count($handler) > 0)
					{
						foreach ($handler as $nDir)
						{
							if ($nDir != "..")
							{
								if (!in_array($nDir, [".git", ".svn"]))
								{
									$sDir = Path::fix($dir . $nDir);
									if (is_dir($sDir))
									{
										self::addIncludePath($sDir, true);
									}
								}
							}
						}
					}
				}
			}
		}
		else
		{
			self::error("addIncludePath> $dir is not dir");
		}
	}
	
	private static function error($msg)
	{
		if (self::$updateFromConsole)
		{
			echo('CONSOLE_ERROR:' . $msg . "\n");
		}
		else
		{
			alert($msg);
		}
	}
}

?>