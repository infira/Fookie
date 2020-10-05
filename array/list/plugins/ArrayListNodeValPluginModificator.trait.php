<?php

trait ArrayListNodeValPluginModificator
{
	/**
	 * Round value
	 *
	 * @param int $decimals
	 * @return $this
	 */
	public function round(int $decimals = 2)
	{
		return $this->newValue(round($this->value, $decimals));
	}
	
	/**
	 * Use vsprintf
	 *
	 * @return $this
	 */
	public function sprintf()
	{
		$newVal = vsprintf(str_replace(["%s|", "|%s"], "%s", $this->value), func_get_args());
		
		return $this->newValue($newVal);
	}
	
	/**
	 * Use vsprintf
	 *
	 * @param array $args
	 * @return string
	 */
	public function vsprintf(array $args)
	{
		$newValue = vsprintf($this->value, $args);
		
		return $this->newValue($newValue);
	}
	
	/**
	 * Convert value to timestamp using strtime
	 *
	 * @param string|int $time - when $time IS NULL strtotime($this->value) ELSE strtotime($time,$this->value)
	 * @return $this
	 */
	public function toTime($time = NULL)
	{
		if ($time)
		{
			return $this->newValue(Date::toTime($time, $this->value));
		}
		else
		{
			return $this->newValue(Date::toTime($this->value));
		}
		
	}
	
	public function format($text, $arg1 = NULL)
	{
		if ($this->contains("%val%") OR $this->contains("%value%"))
		{
			$newVal = Variable::assign(["value" => $text, "val" => $text], $this->value);
		}
		else
		{
			$newVal = Variable::assign(["value" => $this->value, "val" => $this->value], $text);
		}
		if ($arg1 !== NULL)
		{
			$newVal = vsprintf($newVal, array_slice(func_get_args(), 1));
		}
		
		return $this->newValue($newVal);
	}
	
	/**
	 * Format price
	 *
	 * @param string $currency
	 * @param bool   $removeZeros same as str_replace(".00",""
	 * @param bool   $removeTenth
	 * @return $this
	 */
	public function formatPrice($currency = "", $removeZeros = FALSE, $removeTenth = FALSE)
	{
		if (defined("ARRAYLISTNOTEVAL_FORMAT_PRICE_REMOVE_ZEROZ"))
		{
			$removeZeros = ARRAYLISTNOTEVAL_FORMAT_PRICE_REMOVE_ZEROZ;
		}
		if (defined("ARRAYLISTNOTEVAL_FORMAT_PRICE_REMOVE_TENTH"))
		{
			$removeTenth = ARRAYLISTNOTEVAL_FORMAT_PRICE_REMOVE_TENTH;
		}
		
		return $this->newValue(Fix::price(Variable::toNumber($this->value), $removeTenth, $removeZeros) . $currency);
	}
	
	/**
	 * Format as eur
	 *
	 * @param string $unit
	 * @return $this
	 */
	public function eur($unit = "€")
	{
		return $this->formatPrice($unit);
	}
	
	
	/**
	 * Format file size
	 *
	 * @return $this
	 */
	public function formatSize()
	{
		$bytes     = intval($this->value);
		$units     = ['B', 'KB', 'MB', 'GB'];
		$converted = $bytes . ' ' . $units[0];
		for ($i = 0; $i < count($units); $i++)
		{
			if (($bytes / pow(1024, $i)) >= 1)
			{
				$converted = round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
			}
		}
		
		return $this->newValue($converted);
	}
	
	/**
	 * Format date value
	 *
	 * @param string $format - defaults to d.m.Y
	 * @return $this
	 */
	public function formatDate($format = "d.m.Y")
	{
		return $this->newValue(Date::toDate($this->value, $format));
	}
	
	/**
	 * Formate to SQL date(Y-m-d)
	 *
	 * @see https://www.php.net/manual/en/function.date.php
	 * @return $this
	 */
	public function formatSqlDate()
	{
		return $this->formatDate("Y-m-d");
	}
	
	
	/**
	 * Formate date using format = d.m.Y H:i:s
	 *
	 * @see https://www.php.net/manual/en/function.date.php
	 * @return $this
	 */
	public function formatDateTime()
	{
		return $this->formatDate("d.m.Y H:i:s");
	}
	
	/**
	 * Formate date using format = j. F Y
	 *
	 * @see https://www.php.net/manual/en/function.date.php
	 * @return $this
	 */
	public function formatDateNice()
	{
		return $this->formatDate('j. F Y');
	}
	
	/**
	 * Formate date using format = j. F Y H:i:s
	 *
	 * @see https://www.php.net/manual/en/function.date.php
	 * @return $this
	 */
	public function formatDateTimeNice()
	{
		return $this->formatDate('j. F Y H:i:s');
	}
	
	/**
	 * Formate date using format = H:i
	 *
	 * @see https://www.php.net/manual/en/function.date.php
	 * @return $this
	 */
	public function formatTimeNice()
	{
		return $this->formatDate('H:i');
	}
	
	/**
	 * Format phone
	 *
	 * @param string $prefix
	 * @return $this
	 */
	public function formatPhone($prefix = "")
	{
		return $this->newValue(Fix::phone($this->value, $prefix));
	}
	
	/**
	 * Slice value to first $char
	 *
	 * @param string $char
	 * @return mixed
	 */
	public function sliceToChar(string $char)
	{
		return $this->newValue(substr($this->value, 0, strpos($this->value, $char) + 1));
	}
	
	/**
	 * Fix comma and spaces, comma in text is textText, textText
	 *
	 * @return $this
	 */
	public function fixCommaSpaces()
	{
		$val = str_replace(" , ", ",", $this->value);
		$val = str_replace(", ", ",", $val);
		$val = str_replace(" ,", ",", $val);
		$val = str_replace(",", ", ", $val);
		
		return $this->newValue($val);
	}
	
	/**
	 * Convert encoding to utf8
	 *
	 * @return $this
	 */
	public function toUTF8()
	{
		return $this->newValue(Variable::toUTF8($this->value));
	}
	
	/**
	 * Use parseStr to convert value to array
	 *
	 * @return $this
	 */
	public function parseStr()
	{
		return $this->newValue(parseStr($this->value));
	}
	
	/**
	 * Concat value
	 *
	 * @param $val
	 * @return $this
	 */
	public function concat($val)
	{
		return $this->newValue($this->value . $val);
	}
	
	/**
	 * Transfrom value to uppercae
	 *
	 * @return $this
	 */
	public function ucFirst()
	{
		return $this->newValue(Variable::ucFirst($this->value));
	}
	
	/**
	 * Transfrom value to uppercae
	 *
	 * @return $this
	 */
	public function urlEncode()
	{
		return $this->newValue(urlencode($this->value));
	}
	
	/**
	 * Fix url name
	 *
	 * @return $this
	 */
	public function urlName()
	{
		return $this->newValue(Fix::urlName($this->value));
	}
	
	/**
	 * Add string to end of the value
	 *
	 * @param mixed $value
	 * @return $this
	 */
	public function prefix($value)
	{
		return $this->newValue($value . $this->value);
	}
	
	/**
	 * Add string to begining of the value
	 *
	 * @param mixed $value
	 * @return $this
	 */
	public function suffix($value)
	{
		return $this->newValue($this->value . $value);
	}
	
	/**
	 * allias to php substr
	 *
	 * @return $this
	 */
	public function substr($start = NULL, $end = NULL)
	{
		if (is_string($start) and !is_numeric($start))
		{
			$start = strpos($this->value, $start) + 1;
		}
		if (is_string($end) and !is_numeric($end))
		{
			$end = strpos($this->value, $end);
		}
		if ($start !== NULL and $end !== NULL)
		{
			$val = substr($this->value, $start, $end);
		}
		elseif ($start === NULL and $end !== NULL)
		{
			$val = substr($this->value, 0, $end);
		}
		elseif ($start !== NULL and $end === NULL)
		{
			$val = substr($this->value, $start);
		}
		
		return $this->newValue($val);
	}
	
	/**
	 * Add,substract, multiply or devide value,
	 *
	 * @param string $op
	 * @param mixed  $val
	 * @return $this
	 * @example ->math('+',10) or ->math('+10')
	 */
	public function math(string $op, $val = NULL)
	{
		if ($op AND $val === NULL)
		{
			$op  = trim($op);
			$val = substr(str_replace(" ", "", $op), 1);
		}
		else
		{
			$val = str_replace(" ", "", $val);
		}
		$op        = $op{0};
		$mathValue = Variable::toNumber($val);
		$newValue  = $this->value;
		if ($op == "+")
		{
			$newValue = $this->value + $mathValue;
		}
		elseif ($op == "*")
		{
			$newValue = $this->value * $mathValue;
		}
		elseif ($op == "-")
		{
			$newValue = $this->value - $mathValue;
		}
		elseif ($op == "/" or $op == ":")
		{
			$newValue = $this->value / $mathValue;
		}
		else
		{
			alert("operation not implemented");
		}
		
		return $this->newValue($newValue);
	}
	
	/**
	 * Increment value by
	 *
	 * @param int|float $by
	 * @return $this
	 */
	public function increment($by = 1)
	{
		$newValue = $this->value;
		$newValue += $by;
		
		return $this->newValue($newValue);
	}
	
	/**
	 * Use ceil
	 *
	 * @return $this
	 */
	public function ceil()
	{
		return $this->newValue(ceil($this->value));
	}
	
	/**
	 * Use floor
	 *
	 * @return $this
	 */
	public function floor()
	{
		return $this->newValue(floor($this->value));
	}
	
	/**
	 * Alias to split
	 *
	 * @param string $delimiter
	 * @see $this->split()
	 * @return $this
	 */
	public function explode(string $delimiter = ",")
	{
		return $this->split($delimiter);
	}
	
	/**
	 * Convert value to array using explode
	 *
	 * @param string $delimiter
	 * @return $this
	 */
	public function split(string $delimiter = ",")
	{
		$ex = explode($delimiter, $this->value);
		
		return $this->newValue($ex);
	}
	
	/**
	 * use htmlspecialchars
	 *
	 * @return $this
	 */
	public function htmlspecialchars()
	{
		return $this->newValue(htmlspecialchars($this->value));
	}
	
	/**
	 * Convert nl to <br>
	 *
	 * @return $this
	 */
	public function nl2br()
	{
		return $this->newValue(Fix::nl2br($this->value));
	}
	
	/**
	 * Replace <br> to nl
	 *
	 * @return $this
	 */
	public function br2nl()
	{
		return $this->newValue(str_replace("<br />", "\n", Fix::nl2br($this->value, TRUE)));
	}
	
	/**
	 * Convert current value to <img src="$value"..
	 *
	 * @param string $title
	 * @return string
	 */
	public function img($title = "")
	{
		return sprintf('<img src="%s" alt="%s" />', $this->value, $title);
	}
	
	/**
	 * Get file base name
	 *
	 * @return $this
	 */
	public function basename()
	{
		return $this->newValue(basename($this->value));
	}
	
	/**
	 * Fix file name
	 *
	 * @return $this
	 */
	public function fixFileName()
	{
		return $this->newValue(Fix::fileName($this->value));
	}
	
	/**
	 * Get as youtube embed link
	 *
	 * @param string $urlParams
	 * @return $this
	 */
	public function youtubeEmbed($urlParams = "")
	{
		if (!Is::match('%http://www\.youtube\.com/embed/%i', $this->value))
		{
			$val = str_replace("https://www.youtube.com/watch?v=", "", $this->value);
			$val = str_replace("http://youtu.be/", "", $val);
			if (strpos($val, "?") !== FALSE)
			{
				$sp = explode("?", $val);
				if (array_key_exists(1, $sp))
				{
					$val = $sp[1];
					$sp  = explode("&", $val);
					for ($i = 0; $i < count($sp); $i++)
					{
						if (substr($sp[$i], 0, 2) == "v=")
						{
							$val = substr($sp[$i], 2);
							break;
						}
					}
				}
			}
			if ($urlParams)
			{
				$val .= "?" . $urlParams;
			}
			
			return $this->newValue("https://www.youtube.com/embed/$val");
		}
		
		return $this;
	}
	
	/**
	 * Convert to int
	 *
	 * @return $this
	 */
	public function int()
	{
		return $this->newValue(intval($this->value));
	}
	
	/**
	 * Convert to bool
	 *
	 * @param bool $parseAlsoString - if set to true, then (string)"true" is converted to (bool)true, and (string)"false" ==> (bool)false
	 * @return $this
	 */
	public function bool(bool $parseAlsoString)
	{
		return $this->newValue(Variable::toBool($this->value, $parseAlsoString));
	}
	
	/**
	 * Convert to float
	 *
	 * @return $this
	 */
	public function float()
	{
		return $this->newValue(floatval($this->value));
	}
	
	/**
	 * Convert value to number
	 *
	 * @return $this
	 */
	public function toNumber()
	{
		return $this->newValue(Variable::toNumber($this->value));
	}
	
	/**
	 * Convert value to negative number
	 *
	 * @return $this
	 */
	public function toNegative()
	{
		return $this->newValue(Variable::toNegative($this->value));
	}
	
	/**
	 * Convert value to positive number
	 *
	 * @return $this
	 */
	public function toPositive()
	{
		return $this->newValue(Variable::toPositive($this->value));
	}
	
	/**
	 * Assign variables to value
	 *
	 * @param array $vars
	 * @return $this
	 */
	public function assignVars(array $vars)
	{
		return $this->newValue(Variable::assign($vars, $this->value));
	}
	
	/**
	 * Assign variable
	 *
	 * @param string $name
	 * @param mixed  $value
	 * @return $this|mixed|string
	 */
	public function assignVar($name, $value)
	{
		return $this->assignVars([$name => $value]);
	}
	
	/**
	 * Strip html tags
	 *
	 * @param string/array $voidTags - html tags to void on stripping, see http://php.net/manual/en/function.strip-tags.php-
	 * @return $this
	 */
	public function clean($voidTags = FALSE)
	{
		return $this->newValue(Variable::htmlToText($this->value, $voidTags));
	}
	
	/**
	 * Convert value to md5
	 *
	 * @return $this
	 */
	public function md5()
	{
		return $this->newValue(md5($this->value));
	}
	
	/**
	 * Same as str_replace(",00","",$value)
	 *
	 * @return $this
	 */
	public function removeCommaNull()
	{
		return $this->newValue(str_replace([",00", ".00"], "", $this->value));
	}
	
	/**
	 * Round up 5 cents
	 *
	 * @return $this
	 */
	public function roundUpTo5Cents()
	{
		return $this->newValue(Variable::roundUpTo5Cents($this->value));
	}
	
	/**
	 * truncate number
	 *
	 * @param $decmals
	 * @return $this
	 */
	public function truncateNumber($decmals)
	{
		return $this->newValue(Variable::truncateNumber($this->value, $decmals));
	}
	
	/**
	 * Replace part of string
	 *
	 * @param string $search
	 * @param string $replace
	 * @return $this
	 */
	public function replace($search, $replace = "")
	{
		return $this->newValue(str_replace($search, $replace, $this->value));
	}
	
	/**
	 * @param string $pattern
	 * @param string $replace
	 * @return $this
	 */
	public function pregReplace($pattern, $replace = "")
	{
		return $this->newValue(preg_replace($pattern, $replace, $this->value));
	}
	
	/**
	 * Take parts of text
	 *
	 * @param string - $regex
	 * @return $this
	 */
	public function matches($regex)
	{
		return $this->newValue(Regex::getMatch($regex, $this->value));
	}
	
	/**
	 * Trims value
	 *
	 * @return $this
	 */
	public function trim()
	{
		return $this->newValue(trim($this->value));
	}
	
	/**
	 * Convert to upper case
	 *
	 * @return $this
	 */
	public function toUpper()
	{
		return $this->newValue(Variable::toUpper($this->value));
	}
	
	/**
	 * Convert to lower case
	 *
	 * @return $this
	 */
	public function toLower()
	{
		return $this->newValue(Variable::toLower($this->value));
	}
	
	/**
	 * Converts value to array using explode(",")
	 *
	 * @return $this
	 */
	public function toArray()
	{
		return $this->newValue(Variable::toArray($this->value));
	}
	
	/**
	 * Converts value to ArrayList
	 *
	 * @return $this
	 */
	public function toList()
	{
		$List = new ArrayList($this->toArray()->val());
		$List->construct();
		
		return $this->newValue($List);
	}
	
	/**
	 * Remove VAT from value
	 *
	 * @param int|float|null $vatPercent
	 * @return $this
	 */
	public function removeVat($vatPercent = NULL)
	{
		return $this->newValue(\Infira\Utils\Vat::remove($this->value, $vatPercent));
	}
	
	/**
	 * Add VAT to value
	 *
	 * @param int|float|null $vatPercent
	 * @return $this
	 */
	public function addVat($vatPercent = NULL)
	{
		return $this->newValue(\Infira\Utils\Vat::add($this->value, $vatPercent));
	}
	
	/**
	 * vat value
	 *
	 * @param bool   $priceContainsVat
	 * @param string $vatPercent
	 * @return $this
	 */
	public function vat(bool $priceContainsVat, $vatPercent = NULL)
	{
		return $this->newValue(\Infira\Utils\Vat::get($this->value, $priceContainsVat, $vatPercent));
	}
	
	/**
	 * Add markup
	 *
	 * @param int|float $perecent
	 * @return $this
	 */
	public function addMarkup($perecent)
	{
		return $this->newValue($this->value + ($this->value * ($perecent / 100)));
	}
	
	/**
	 * Get amount of $value by $percent
	 *
	 * @param int|float $percent
	 * @return $this
	 */
	public function getAmmountByPercent($percent)
	{
		$sum     = $this->float(TRUE)->value;
		$percent = floatval($percent);
		$newVal  = ($sum * $percent) / 100;
		
		return $this->newValue($newVal);
	}
	
	/**
	 * Convert value to array and gets a random item fro it
	 *
	 * @return $this
	 */
	public function random()
	{
		$items = $this->toArray()->val();
		
		return $this->newValue($items[array_rand($items)]);
	}
	
	/**
	 * Get adaptive image url
	 *
	 * @param string $config
	 * @param string $baseDirSuffix
	 * @param bool   $voidCache
	 * @return $this
	 */
	public function adaptiveImg(string $config, $baseDirSuffix = "", $voidCache = FALSE)
	{
		return $this->newValue($this->getAdaptiveImageUrl($this->value, $config, $baseDirSuffix, FALSE, $voidCache));
	}
	
	/**
	 * Convert value to array and calls $callback from each item
	 *
	 * @param callable $callback
	 * @param null     $scope
	 * @return array
	 */
	public function each(callable $callback, $scope = NULL)
	{
		$r = [];
		foreach ($this->toArray()->val() as $key => $v)
		{
			$v       = callback($callback, $scope, [$v]);
			$r[$key] = new $this($key, $v);
		}
		
		return $r;
	}
}