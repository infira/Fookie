<?php

namespace Infira\Fookie\request;

use Infira\Utils\Http;
use Infira\Utils\Is;
use AppConfig;

class Payload
{
	private static $data         = ["payload" => null];
	private static $plainPoutput = false;
	private static $outputsJSON  = false;
	
	public static function setField(string $name, $value)
	{
		self::$data[$name] = $value;
	}
	
	protected static function getField(string $name, $returnOnNotFound = null)
	{
		if (!self::existsField($name))
		{
			return $returnOnNotFound;
		}
		
		return self::$data[$name];
	}
	
	protected static function existsField(string $name): bool
	{
		return array_key_exists($name, self::$data);
	}
	
	public static function setConsoleError($error)
	{
		self::setField("__consoleError", $error);
	}
	
	public static function setError($error, $errorID = null)
	{
		http_response_code(400);
		if (Is::isClass($error, 'Infira\Error\Error'))
		{
			foreach ((array)$error->getStack() as $name => $val)
			{
				if ($name == 'msg')
				{
					$name = 'error';
				}
				self::setField($name, $val);
			}
		}
		else
		{
			self::setField("error", $error);
		}
		if ($errorID)
		{
			self::setField("errorLink", Route::getOperationLink(['opName' => 'viewErrorLog', 'hash' => 'a12g3fs14g3d5h36gk56hilasd3a', 'ID' => $errorID]));
		}
	}
	
	public static function getError()
	{
		return self::getField("error");
	}
	
	/**
	 * Exits the code and outputs data
	 */
	public static function sendError(string $msg)
	{
		self::setError($msg);
		self::send();
	}
	
	/**
	 * Send output to browser immediately
	 */
	public static function send()
	{
		echo self::getOutput();
		exit;
	}
	
	public static function haveError(): bool
	{
		return (bool)self::getField("error");
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
	
	public static function getOutput(): ?string
	{
		if (Http::acceptJSON() or self::$outputsJSON)
		{
			self::setJSONHeader();
			if (self::haveError())
			{
				self::$data['error'] = strip_tags(self::$data['error'], '<br>');
			}
		}
		$output = self::$data;
		if (self::$plainPoutput)
		{
			if (!self::haveError())
			{
				$output = self::$data['payload'];
			}
		}
		
		if (self::$outputsJSON)
		{
			return json_encode($output);
		}
		if (is_string($output))
		{
			return $output;
		}
		
		return pre(dump($output));
	}
}

?>