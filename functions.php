<?php
function isInternetExplorer()
{
	if (!isset($_SERVER['HTTP_USER_AGENT']))
	{
		return false;
	}
	
	return (preg_match('~MSIE|Internet Explorer~i', $_SERVER['HTTP_USER_AGENT']) || (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false));
}