<?php
define("ERROR_RECIVER_EMAIL", "gen@infira.ee");
define("UNDEFINDED", "_______UNDEFINED_______");
define("BREAK_", "_______BREAK_______");
define("CONTINUE_", "_______CONTINUE_______");
define("VOID_", "_______VOID_______");
define("SKIP_", "_______SKIP_______");
define("GLOBAL_VAT_PERCENT", 20);
// PHP_VERSION_ID is available as of PHP 5.2.7, if our
// version is lower than that, then emulate it
if (!defined('PHP_VERSION_ID'))
{
	$version = explode('.', PHP_VERSION);
	
	define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
if (PHP_VERSION_ID < 50207)
{
	define('PHP_MAJOR_VERSION', $version[0]);
	define('PHP_MINOR_VERSION', $version[1]);
	define('PHP_RELEASE_VERSION', $version[2]);
	
	// and so on, ...
}
define('NL', PHP_EOL);
define('BR', "<br />");
define('TAB', "\t");
// Ei tööta php 5.3.x - set_magic_quotes_runtime(0);
?>