<?php

namespace Infira\Fookie\request;

use AppConfig;
use Infira\Fookie\facade\Http;

class Payload
{
	private static $data         = [];
	private static $outputAsHTML = false;
	
	public static function init()
	{
		self::$data = ["payloadQuery" => "", "payload" => []];
	}
	
	public static function setField($name, $value)
	{
		self::$data[$name] = $value;
	}
	
	protected static function getField($name, $returnOnNotFound = null)
	{
		if (!self::existsField($name))
		{
			return $returnOnNotFound;
		}
		
		return self::$data[$name];
	}
	
	protected static function existsField($name)
	{
		return array_key_exists($name, self::$data);
	}
	
	public static function setConsoleError($error)
	{
		self::setField("__consoleError", $error);
	}
	
	public static function setError($error, $errorID = null)
	{
		if ($errorID)
		{
			self::setField("errorLink", Route::getFullLink('OperationControllerStarter', ['opName' => 'viewErrorLog', 'hash' => 'a12g3fs14g3d5h36gk56hilasd3a', 'ID' => $errorID]));
		}
		self::setField("error", $error);
	}
	
	public static function getError()
	{
		return self::getField("error");
	}
	
	/**
	 * Exits the code and outputs data
	 *
	 * @return mixed|null
	 */
	public static function sendError(string $msg)
	{
		self::setError($msg);
		
		echo self::getOutput();
		exit;
	}
	
	public static function haveError()
	{
		return (self::getField("error")) ? true : false;
	}
	
	public static function setJSONHeader()
	{
		self::setHeader('Content-Type: application/json');
	}
	
	public static function setHeader($header)
	{
		header($header);
	}
	
	public static function set($data)
	{
		if (self::haveError())
		{
			return true;
		}
		self::setField("payload", $data);
	}
	
	public static function setLoadQuery($query)
	{
		if (AppConfig::isDevENV())
		{
			self::setField("payloadQuery", $query);
		}
	}
	
	public static function outputHTML()
	{
		self::$outputAsHTML = true;
	}
	
	public static function getOutput(): string
	{
		if (AppConfig::isDevENV())
		{
			self::setField('repLink', str_replace('_sr', '_rr', Http::getCurrentUrl()));
		}
		if (self::$outputAsHTML)
		{
			if (self::haveError())
			{
				return pre(dump(self::$data));
			}
			
			return self::$data['payload'];
		}
		else
		{
			if (Http::acceptJSON())
			{
				return json_encode(self::$data);
			}
			
			return pre(dump(self::$data));
		}
	}
}

?>