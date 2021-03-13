<?php
require_once __DIR__ . '/smurfs/smurfs.php';
if (!isset($smurfs))
{
	$smurfs = $defaultSmurs;
}
else
{
	$smurfs = array_merge($defaultSmurs, $smurfs);
}
foreach ($smurfs as $name => $class)
{
	$app->add(new $class);
}
?>