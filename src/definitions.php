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

// PHP_VERSION_ID is defined as a number, where the higher the number
// is, the newer a PHP version is used. It's defined as used in the above
// expression:
//
// $version_id = $major_version * 10000 + $minor_version * 100 + $release_version;
//
// Now with PHP_VERSION_ID we can check for features this PHP version
// may have, this doesn't require to use version_compare() everytime
// you check if the current PHP version may not support a feature.
//
// For example, we may here define the PHP_VERSION_* constants thats
// not available in versions prior to 5.2.7


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


// Kas kõik tõlgitav või on iga keele jaoks kõik eraldis
define("ADAPTIVE_IMAGE_CACHE_NAME", "AdaptiveImageUrl");
?>