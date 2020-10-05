<?php

trait LibsMethodExtensions
{
	protected $aiFile = "ai.php";
	
	public function getAdaptiveImageUrl($src, $config, $baseDirSuffix = "", $returnAsPhpSrc = false, $voidCache = false)
	{
		//Prof()->startTimer("getAdaptiveImageUrl");
		$isEditTimeOk = function ($newFile) use ($src)
		{
			$src     = Path::toPath(Path::pseudoUrl($src));
			$newFile = Path::toPath($newFile);
			
			if (!file_exists($newFile) or !file_exists($src))
			{
				return false;
			}
			if (filemtime($newFile) < filemtime($src))
			{
				return false;
			}
			
			return true;
		};
		if (Is::isClass($config, "ArrayObjectProps"))
		{
			$Params = $config;
		}
		else
		{
			$Params = new ArrayObjectProps();
		}
		if (is_string($config))
		{
			$Params->parseStr($config);
		}
		
		if (is_object($src))
		{
			$src = $src->value;
		}
		if ($Params->exists("voidCache"))
		{
			$voidCache = true;
		}
		if (!$voidCache)
		{
			$Cache = Cache::Collection(ADAPTIVE_IMAGE_CACHE_NAME)->setKey([$src, $Params->parseStr(), $baseDirSuffix, $returnAsPhpSrc]);
		}
		if ($voidCache)
		{
			$cacheOk = false;
		}
		else
		{
			if ($Cache->exists())
			{
				if (file_exists(Path::toPath($Cache->get(""))))
				{
					$cacheOk = true;
				}
				else
				{
					$cacheOk = false;
				}
			}
			else
			{
				$cacheOk = false;
			}
			if (!$isEditTimeOk(Path::toPath($Cache->get(""))) && $cacheOk && !$voidCache)
			{
				$cacheOk = false;
			}
		}
		if (Http::getGET("adaptiveImageForceCache"))
		{
			$cacheOk        = false;
			$returnAsPhpSrc = true;
		}
		
		if (!$cacheOk)
		{
			if ($baseDirSuffix)
			{
				$Params->set("bds", $baseDirSuffix);
			}
			if (Http::getGET("adaptiveImageForceCache"))
			{
				$Params->set("forceCache", "1");
			}
			$src = Path::toPath(Path::pseudoUrl($src));
			if (!file_exists($src))
			{
				$r = "";
			}
			else
			{
				$phpSrc = Path::toUrl($this->aiFile) . "?src=" . $src . "" . "&" . $Params->parseStr();
				if ($returnAsPhpSrc)
				{
					$r = $phpSrc;
				}
				else
				{
					if (!$Params->exists("generate"))
					{
						$Params->set("generate", "0");
					}
					$Params->set("sendToBrowser", 0);
					
					$adaptiveImgHandlerName = AppConfig::adaptiveImgHandlerClassName();
					/* @var $Img LibsAdaptiveImgHandlerHelper */
					$Img = new $adaptiveImgHandlerName();
					$Img->setConfig($Params);
					$baseDir = $Img->getCachePath();
					if ($Params->exists("bd"))
					{
						$baseDir = $Params->bd;
					}
					$Img->setCachePath($baseDir);
					$Img->create($src, null, null);
					$newFile = $Img->getFinalPath();
					if ($Params->get("returnFinalPath") == 1)
					{
						$r = Path::toPath($newFile);
					}
					else
					{
						if (file_exists($newFile))
						{
							if (!$isEditTimeOk($newFile))
							{
								$r = $phpSrc;
							}
							else
							{
								$setCache = Path::toUrl($newFile);
								if (!$voidCache)
								{
									$Cache->set($setCache);
								}
								$r = $setCache;;
							}
						}
						else
						{
							$r = $phpSrc;
						}
					}
				}
			}
		}
		else
		{
			$r = $Cache->get("");
		}
		
		//Prof()->stopTimer("getAdaptiveImageUrl");
		
		return $r;
	}
	
}

?>