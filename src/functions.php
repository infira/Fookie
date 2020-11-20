<?php
function isInternetExplorer()
{
	if (!isset($_SERVER['HTTP_USER_AGENT']))
	{
		return false;
	}
	
	return (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false));
}

function isTestIp($name = false)
{
	$ips                      = [];
	$ips["zoneVpsLocal"]      = "192.168.1.4";
	$ips["genKodusalu"]       = "178.23.112.53";
	$ips["genLocal"]          = "192.168.33.1";
	$ips["reigoLocal"]        = "127.0.0.1";
	$ips["genTelefon"]        = "37.157.69.218";
	$ips["klavisKontor"]      = "80.235.40.90";
	$ips["vainu"]             = "217.71.46.166";
	$ips["ille4GRuuter"]      = "37.157.79.216";
	$ips["zonePRivateServer"] = "217.146.68.92";
	$ips["elisaMaakodu"]      = "178.23.119.167";
	if ($name !== false)
	{
		if (array_key_exists($name, $ips))
		{
			if ($ips[$name] == getUserIP())
			{
				return true;
			}
		}
		
		return false;
	}
	else
	{
		return in_array($_SERVER["REMOTE_ADDR"], $ips);
	}
}

function isSerializable($value)
{
	if (is_closure($value) or is_resource($value))
	{
		return false;
	}
	if (is_string($value) or is_numeric($value))
	{
		return true;
	}
	$return = true;
	$arr    = [$value];
	
	array_walk_recursive($arr, function ($element) use (&$return)
	{
		if (is_object($element) && get_class($element) == 'Closure')
		{
			$return = false;
		}
	});
	
	return $return;
}