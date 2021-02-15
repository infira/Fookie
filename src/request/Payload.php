<?php

namespace Infira\Fookie\request;

use AppConfig;
use Infira\Utils\Http;

class Payload
{
	private static $data         = null;
	private static $plainPoutput = false;
	private static $outputsJSON  = false;
	
	public static function init()
	{
		self::$data = ["payload" => null];
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
			self::setField("errorLink", Route::getOperationLink(['opName' => 'viewErrorLog', 'hash' => 'a12g3fs14g3d5h36gk56hilasd3a', 'ID' => $errorID]));
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
		self::$outputsJSON = true;
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
	
	public static function plainOutput()
	{
		self::$plainPoutput = true;
	}
	
	public static function getOutput(): string
	{
		if (self::$plainPoutput)
		{
			if (self::haveError())
			{
				$output = self::$data['error'];
			}
			else
			{
				$output = self::$data['payload'];
			}
		}
		else
		{
			$output = self::$data;
		}
		if (Http::acceptJSON() or self::$outputsJSON)
		{
			self::setJSONHeader();
			if (self::haveError())
			{
				if (self::$plainPoutput)
				{
					$output = strip_tags($output, '<br>');
				}
				else
				{
					$output['error'] = strip_tags($output['error'], '<br>');
				}
			}
		}
		
		if (self::$outputsJSON)
		{
			return json_encode($output);
		}
		if (self::$plainPoutput)
		{
			return $output;
		}
		
		return pre(dump($output));
	}
}

?>