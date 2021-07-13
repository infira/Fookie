<?php
$baseDir = __DIR__ . "/";
$js = "";
$addedDeps = array();
function getFileContent($pathName, $fName)
{
	global $baseDir, $addedDeps;
	$f = "$baseDir$pathName/$fName.js";
	if (isset($addedDeps[$f]))
	{
		return "";
	}
	$addedDeps[$f] = "";
	$str = "";
	if (file_exists($f))
	{
		$fc = trim(file_get_contents($f));
		$lines = explode("\n", $fc);
		foreach ($lines as $line)
		{
			if (strpos($line, "//depend:") !== false)
			{
				$depends = explode(",", trim(str_replace("//depend:", "", trim($line))));
				foreach ($depends as $dep)
				{
					$n = explode("/", $dep);
					$str .= getFileContent($n[0], $n[1]);
				}
			}
		}
		$str = preg_replace('/\/\/depend:.+/', '', $str);
		$str .= $fc;

		$len = strlen($str) - 1;
		if ($len > 0)
		{
			if ($str[$len] != ";")
			{
				$str .= ";";
			}
		}
	}
	else
	{
		$str = "\n\n" . 'alert("js.php faili ' . $f . ' ei leitud");' . ';' . "\n\n";;
	}

	return $str . "\n";
}

foreach ($_GET as $pathName => $names)
{
	if ($pathName != "version")
	{
		$js .= getFileContent($pathName, "general");
		$names = trim("$names");
		if ($names == "all")
		{
			$scan = scandir($baseDir . $pathName);
			foreach ($scan as $f)
			{
				if (!in_array($f, array(".", "..")))
				{
					$f = explode(".", $f);
					$js .= getFileContent($pathName, trim($f[0]));
				}
			}
		}
		else
		{
			$ex = explode(",", $names);
			foreach ($ex as $f)
			{
				$js .= getFileContent($pathName, $f);
			}
		}
	}
}
header('Content-Type: application/javascript');
echo $js;
?>