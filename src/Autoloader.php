<?php

namespace Infira\fookie;

use Path;

class Autoloader
{
	private static $namespaces = [];
	private static $interfaces = [];
	private static $traits     = [];
	private static $classes    = [];
	
	
	private static $includePaths                = [];
	private static $customClassess              = [];
	private static $installPathsMethod;
	private static $autoloadPhpFilePath;
	private static $voidClassesOnNotExists      = [];
	private static $voidClassPatternOnNotExists = [];
	
	private static $isInited = false;
	
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
			if (is_callable(self::$installPathsMethod))
			{
				$installPaths   = [];
				$installPaths[] = [Path::app(), true];
				$installPaths[] = ['ns:Infira\Fookie', Path::fookie()];
				$installPaths[] = ['ns:Infira\Fookie\facade', Path::fookie('facade/')];
				$installPaths[] = ['ns:Infira\Fookie\request', Path::fookie('request/')];
				$installPaths[] = ['ns:Infira\Fookie\controller', Path::fookie('controller/')];
				$installPaths   = array_merge($installPaths, callback(self::$installPathsMethod));
				foreach ($installPaths as $path)
				{
					if (substr($path[0], 0, 3) == 'ns:')
					{
						$dir = $path[1];
						if (!is_dir($dir))
						{
							self::error($dir . ' is not a dir');
						}
						$namespace = substr($path[0], 3);
						
						$dir = trim($dir);
						$dir = Path::fix($dir);
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
											$cn = str_replace(['.class', '.trait', '.controller', '.interface'], '', pathinfo($f)['filename']);
											$cn = str_replace('.controller', '', $cn);
											$ns = $namespace . '\\' . $cn;
											if ($ns{(strlen($ns) - 1)} == '\\')
											{
												$ns = substr($ns, 0, -1);
											}
											$pi = pathinfo($f);
											if (isset($pi['extension']))
											{
												if ($pi['extension'] == 'php')
												{
													if (isset(self::$namespaces[$ns]))
													{
														self::error("Namespaces($ns) is already added");
													}
													self::$namespaces[$ns] = $f;
												}
											}
										}
									}
								}
							}
						}
						else
						{
							self::error("addIncludePath > $dir is not dir");
						}
					}
					else
					{
						$dir = $path[0];
						if (!is_dir($dir))
						{
							self::error($dir . ' is not a dir');
						}
						self::addIncludePath($path[0], $path[1]);
					}
				}
			}
			
			
			$phpAutoloadFileStr = '<?php' . "\n";
			
			$setted = [];
			
			$add = function ($type, $name, $file) use (&$setted, &$phpAutoloadFileStr)
			{
				$setNames              = [];
				$setNames['class']     = 'classes';
				$setNames['trait']     = 'traits';
				$setNames['interface'] = 'interfaces';
				$setName               = $setNames[$type];
				$name                  = str_replace('.' . $type . '.php', '', $name);
				if (isset($setted[$setName][$name]))
				{
					$msg = 'Cant define autoloader class(' . $name . ') twice = ' . $file . BR;
					$msg .= 'Previousliy declared: ' . $setted[$setName][$name];
					$this->error($msg);
				}
				$phpAutoloadFileStr      .= 'self::$' . $setName . '[\'' . $name . '\'] = \'' . $file . '\';' . "\n";
				$setted[$setName][$name] = $file;
			};
			
			foreach (self::$includePaths as $path)
			{
				foreach (glob($path . "*.php") as $file)
				{
					$basename                   = basename($file);
					$setted['files'][$basename] = str_replace(Path::root(), '', $file);
					if (strpos($basename, '.class') !== false)
					{
						$add('class', $basename, $file);
					}
					elseif (strpos($basename, '.int') !== false)
					{
						$add('interface', $basename, $file);
					}
					elseif (strpos($basename, '.trait') !== false)
					{
						$add('trait', $basename, $file);
					}
				}
			}
			foreach (self::$namespaces as $nsClass => $path)
			{
				$setted['namespaces'][$nsClass] = $path;
				$phpAutoloadFileStr             .= 'self::$namespaces[\'' . $nsClass . '\'] = \'' . $path . '\';' . "\n";
			}
			foreach (self::$interfaces as $nsClass => $path)
			{
				$setted['interfaces'][$nsClass] = $path;
				$phpAutoloadFileStr             .= 'self::$interfaces[\'' . $nsClass . '\'] = \'' . $path . '\';' . "\n";
			}
			foreach (self::$customClassess as $class => $path)
			{
				$add('class', $class, $path);
			}
			$phpAutoloadFileStr .= "\n" . '?>';
			file_put_contents(self::$autoloadPhpFilePath, trim($phpAutoloadFileStr));
			if (self::canDisplay())
			{
				echo "<pre>";
				$tmp = $setted;
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
	
	private static function addIncludePath($dir, $recursive = false)
	{
		$dir = trim($dir);
		$dir = Path::fix($dir);
		if (is_dir($dir))
		{
			if (isset(self::$includePaths[$dir]))
			{
				self::error("Path($dir) is already added");
			}
			self::$includePaths[$dir] = $dir;
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