<?php

class AltoRouterExtendor extends AltoRouter
{
	public $namedRoutesForLinking = FALSE;
	
	public function generate($routeName, array $params = [])
	{
		$url = parent::generate($routeName, $params) . '/';
		/*
		preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $route, $matches, PREG_SET_ORDER);
		if (checkArray($matches))
		{
			addExtraErrorInfo("params", $matches);
			alert("Missing parameters in url generation");
		}
		*/
		//debug("params", $params);
		if (preg_match_all('`(/|\.|)\[([^:\]]*+)(?::([^:\]]*+))?\](\?|)`', $this->namedRoutes[$routeName], $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				[$block, $pre, $type, $param, $optional] = $match;
				
				unset($params[$param]);
			}
		}
		if (checkArray($params))
		{
			$url .= "?" . http_build_query($params, "&");
		}
		
		return $url;
	}
}