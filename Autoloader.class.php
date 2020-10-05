<?php

class Autoloader
{
	private static $namespaces = [];
	private static $interfaces = [];
	private static $traits     = [];
	private static $classes    = [];
	
	
	private static $includePaths                 = [];
	private static $customClassess               = [];
	private static $installPathsMethod;
	private static $autoloadPhpFilePath;
	private static $voidedAutoloadersOnNotExists = [];
	
	public static function init()
	{
		self::$autoloadPhpFilePath = Path::temp('autoloadClasslocations.php');
		$path                      = pathinfo(self::$autoloadPhpFilePath, PATHINFO_DIRNAME);
		if (!is_dir($path))
		{
			exit("TEMP path for installing autoloader not existing");
			exit;
		}
		if (!is_writable($path))
		{
			exit("TEMP path for installing autoloader is not writable");
		}
		
		if (self::isInstall(true))
		{
			if (!isset($_GET["minOutput"]) and self::isInstall(false))
			{
				$r = '<button type="button" onclick="window.location=\'/controlpanel/\'">Go to control panel</button>';
				echo $r;
			}
			if (is_callable(self::$installPathsMethod))
			{
				$installPaths   = [];
				$installPaths[] = ['ns:IFW', Path::infiraFW()];
				$installPaths[] = ['ns:IFW\facade', Path::infiraFW('facade/')];
				$installPaths   = array_merge($installPaths, callback(self::$installPathsMethod));
				foreach ($installPaths as $path)
				{
					if (substr($path[0], 0, 3) == 'ns:')
					{
						self::addNamespace($path[1], substr($path[0], 3));
					}
					else
					{
						self::addIncludePath($path[0], $path[1]);
					}
				}
			}
			
			
			$phpAutoloadFileStr = '<?php' . "\n";
			
			$debugOutput = [];
			foreach (self::$includePaths as $path)
			{
				foreach (glob($path . "*.php") as $file)
				{
					$nasename               = basename($file);
					$debugOutput[$nasename] = str_replace(Path::root(), '', $file);
					$source                 = file_get_contents($file);
					$matches                = [];
					
					if (strpos($nasename, '.class') !== false)
					{
						$class              = str_replace('.class.php', '', $nasename);
						$phpAutoloadFileStr .= 'self::$classes[\'' . $class . '\'] = \'' . $file . '\';' . "\n";
					}
					elseif (strpos($nasename, '.int') !== false)
					{
						$class              = str_replace('.int.php', '', $nasename);
						$phpAutoloadFileStr .= 'self::$interfaces[\'' . $class . '\'] = \'' . $file . '\';' . "\n";
					}
					elseif (strpos($nasename, '.trait') !== false)
					{
						$class              = str_replace('.trait.php', '', $nasename);
						$phpAutoloadFileStr .= 'self::$traits[\'' . $class . '\'] = \'' . $file . '\';' . "\n";
					}
				}
			}
			foreach (self::$namespaces as $nsClass => $path)
			{
				$debugOutput[$nsClass] = $path;
				$phpAutoloadFileStr    .= 'self::$namespaces[\'' . $nsClass . '\'] = \'' . $path . '\';' . "\n";
			}
			$phpAutoloadFileStr .= "\n" . '?>';
			file_put_contents(self::$autoloadPhpFilePath, trim($phpAutoloadFileStr));
			if (self::isInstall(false))
			{
				if (!isset($_GET["minOutput"]))
				{
					echo "<pre>";
					print_r($debugOutput);
					echo "<pre>";
					exit("ok");
				}
				else
				{
					echo "autloader generated";
					exit;
				}
			}
		}
		Prof()->startTimer("Autoloader->loadClassLocations");
		if (!file_exists(self::$autoloadPhpFilePath))
		{
			exit(self::$autoloadPhpFilePath . " does not exists");
		}
		require_once self::$autoloadPhpFilePath;
		Prof()->stopTimer("Autoloader->loadClassLocations");
		
		
		spl_autoload_register(['Autoloader', 'loader'], true);
	}
	
	public static function setInstallPathGetter(callable $callback)
	{
		self::$installPathsMethod = $callback;
	}
	
	public static function voidOnNotExists($className)
	{
		if (!isset(self::$voidedAutoloadersOnNotExists))
		{
			self::$voidedAutoloadersOnNotExists = [];
		}
		self::$voidedAutoloadersOnNotExists[$className] = $className;
	}
	
	private static function isInstall($checkFile = false)
	{
		if (!file_exists(self::$autoloadPhpFilePath) and $checkFile)
		{
			return true;
		}
		
		return (isset($_GET["generateAutoloader"]));
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
				alert("Autoloader: class '$className file '$requireFile' not found");
			}
			else
			{
				require_once($requireFile);
			}
		}
		else
		{
			if (isset(self::$voidedAutoloadersOnNotExists[$className]))
			{
				return true;
			}
			else
			{
				alert("Autoloader: class '$className found");
			}
		}
		Prof()->stopTimer("Autoloader->load");
	}
	
	public static function addCustomClass($className, $classFileLocation)
	{
		self::$customClassess[$className] = $classFileLocation;
	}
	
	private static function addNamespace($dir, $namespace)
	{
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
							$cn                                        = str_replace(['.class'], '', pathinfo($f)['filename']);
							self::$namespaces[$namespace . '\\' . $cn] = $f;
						}
					}
				}
			}
		}
		else
		{
			alert("addIncludePath > $dir is not dir");
		}
	}
	
	private static function addIncludePath($dir, $recursive = false)
	{
		$dir = trim($dir);
		$dir = Path::fix($dir);
		if (is_dir($dir))
		{
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
										self::$includePaths[$sDir] = $sDir;
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
			alert("addIncludePath> $dir is not dir");
		}
	}
}

?>