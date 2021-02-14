<?php

namespace Infira\fookie;

use Path;
use File;
use Infira\Utils\Regex;

class Autoloader
{
	private static $namespaces = [];
	private static $traits     = [];
	private static $classes    = [];
	
	
	private static $settedIncludePaths          = [];
	private static $customClassess              = [];
	private static $installPathsMethod;
	private static $autoloadPhpFilePath;
	private static $voidClassesOnNotExists      = [];
	private static $voidClassPatternOnNotExists = [];
	
	private static $isInited = false;
	
	private static $collectedFiles     = [];
	private static $allCollectedFiles  = [];
	private static $phpAutoloadFileStr = '';
	
	public static function init()
	{
		if (self::$isInited)
		{
			return true;
		}
		self::$isInited            = true;
		self::$autoloadPhpFilePath = Path::temp('autoloadClasslocations.php');
		$path                      = pathinfo(self::$autoloadPhpFilePath, PATHINFO_DIRNAME);
		if (!is_dir($path))
		{
			self::error("TEMP path for installing autoloader not existing");
		}
		if (!is_writable($path))
		{
			self::error("TEMP path for installing autoloader is not writable");
		}
		
		
		if (self::isInstall(true))
		{
			if (self::canDisplay())
			{
				$r = '<button type="button" onclick="window.location=\'/controlpanel/\'">Go to control panel</button>';
				echo $r;
			}
			
			self::$allCollectedFiles  = [];
			self::$collectedFiles     = [];
			self::$phpAutoloadFileStr = '<?php' . "\n";
			if (is_callable(self::$installPathsMethod))
			{
				$installPaths   = [];
				$installPaths[] = [Path::app(), true];
				$installPaths[] = [Path::fookie(), true];
				$installPaths   = array_merge($installPaths, callback(self::$installPathsMethod));
				foreach ($installPaths as $install)
				{
					if (substr($install[0], 0, 3) == 'ns:')
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
						else
						{
							self::error("init > $dir is not dir");
						}
					}
					else
					{
						$dir = $install[0];
						if (!is_dir($dir))
						{
							self::error($dir . ' is not a dir');
						}
						self::addIncludePath($dir, $install[1]);
					}
				}
			}
			
			foreach (self::$customClassess as $class => $path)
			{
				self::collect('classes', $class, $path);
			}
			
			self::$phpAutoloadFileStr .= "\n" . '?>';
			file_put_contents(self::$autoloadPhpFilePath, trim(self::$phpAutoloadFileStr));
			if (self::canDisplay())
			{
				echo "<pre>";
				$tmp = self::$collectedFiles;
				unset($tmp['files']);
				print_r($tmp);
				echo "<pre>";
				exit("ok");
			}
			if (self::isInstall(false) and !isset($_GET["minOutput"]))
			{
				exit("autloader generated");
			}
		}
		Prof()->startTimer("Autoloader->loadClassLocations");
		if (!file_exists(self::$autoloadPhpFilePath))
		{
			self::error(self::$autoloadPhpFilePath . " does not exists");
		}
		require_once self::$autoloadPhpFilePath;
		Prof()->stopTimer("Autoloader->loadClassLocations");
		
		spl_autoload_register(['\Infira\fookie\Autoloader', 'loader'], true);
	}
	
	public static function setInstallPathGetter(callable $callback)
	{
		self::$installPathsMethod = $callback;
	}
	
	public static function voidOnNotExists($pattern)
	{
		if ($pattern{0} == '/')
		{
			self::$voidClassPatternOnNotExists[$pattern] = $pattern;
		}
		else
		{
			self::$voidClassesOnNotExists[$pattern] = $pattern;
		}
	}
	
	private static function isInstall($checkFile = false)
	{
		if (!file_exists(self::$autoloadPhpFilePath) and $checkFile)
		{
			return true;
		}
		
		return (isset($_GET["generateAutoloader"]));
	}
	
	private static function canDisplay()
	{
		if (\AppConfig::isLocalEnv())
		{
			return true;
		}
		if (\AppConfig::isLiveENV())
		{
			return (self::isInstall(false) and !isset($_GET["minOutput"]) and isTestIp());
		}
		else
		{
			return (self::isInstall(false) and !isset($_GET["minOutput"]));
		}
	}
	
	private static function loader($className)
	{
		Prof()->startTimer("Autoloader->load");
		if (in_array($className, ["Memcached", "Memcache"]))
		{
			return true;
		}
		$requireFile = null;
		if (array_key_exists($className, self::$customClassess))
		{
			$requireFile = self::$customClassess[$className];
		}
		elseif (isset(self::$namespaces[$className]))
		{
			$requireFile = self::$namespaces[$className];
		}
		elseif (isset(self::$classes[$className]))
		{
			$requireFile = self::$classes[$className];
		}
		elseif (isset(self::$traits[$className]))
		{
			$requireFile = self::$traits[$className];
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
			if (isset(self::$voidClassesOnNotExists[$className]))
			{
				return true;
			}
			else
			{
				if (count(self::$voidClassPatternOnNotExists) > 0)
				{
					foreach (self::$voidClassPatternOnNotExists as $pattern)
					{
						if (\Infira\Utils\Regex::isMatch($pattern, $className))
						{
							return true;
						}
					}
				}
			}
			self::error("Autoloader: class '$className' not found");
		}
		Prof()->stopTimer("Autoloader->load");
	}
	
	public static function setPath($className, $classFileLocation)
	{
		self::$customClassess[$className] = $classFileLocation;
	}
	
	public static function collect($type, $name, $file)
	{
		if (!isset(self::$allCollectedFiles[$file]))
		{
			$name = explodeAt('.', basename($name), 0);
			if (!in_array($type, ['classes', 'classes2', 'interfaces', 'traits', 'files', 'namespaces']))
			{
				//self::error('Unknown collect type');
			}
			if (isset(self::$collectedFiles[$type][$name]))
			{
				$msg = BR . 'Cant define autoloader class(' . $name . '),type(' . $type . ') twice = ' . $file . BR;
				$msg .= 'Previousliy declared: <pre>' . dump(self::$collectedFiles[$type]) . '</pre>';
				//exit($msg);
			}
			self::$phpAutoloadFileStr           .= 'self::$' . $type . '[\'' . $name . '\'] = \'' . $file . '\';' . "\n";
			self::$collectedFiles[$type][$name] = $file;
			
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
					self::collect('namespaces', $matches[1][0] . '\\' . $basename, $file);
				}
				else
				{
					if (Regex::isMatch('/^class ([[A-Za-z0-9_]+)/m', $src))
					{
						$matches = [];
						preg_match_all('/^class ([[A-Za-z0-9_]+)/m', $src, $matches);
						self::collect('classes', $matches[1][0], $file);
					}
					elseif (Regex::isMatch('/^trait ([[A-Za-z0-9_]+)/m', $src))
					{
						$matches = [];
						preg_match_all('/^trait ([[A-Za-z0-9_]+)/m', $src, $matches);
						self::collect('traits', $matches[1][0], $file);
					}
				}
				
				
				if (strpos($basename, '.class') !== false)
				{
					self::collect('classes', $basename, $file);
				}
				elseif (strpos($basename, '.int') !== false)
				{
					self::collect('interfaces', $basename, $file);
				}
				elseif (strpos($basename, '.trait') !== false)
				{
					self::collect('traits', $basename, $file);
				}
				else
				{
					//self::collect('files', $basename, $file);
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
		alert($msg);
	}
}

?>