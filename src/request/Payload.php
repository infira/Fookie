<?php

namespace Infira\Fookie\request;

use AppConfig;
use Infira\Utils\Http;
use Infira\Utils\Is;

class Payload
{
	private static $data         = ["payload" => null];
	private static $plainPoutput = false;
	private static $outputsJSON  = false;
	
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
		$dataField = 'payload';
		if (self::haveError())
		{
			$dataField = 'error';
		}
		if (Http::acceptJSON() or self::$outputsJSON)
		{
			self::setJSONHeader();
			self::$data[$dataField] = strip_tags(self::$data[$dataField], '<br>');
		}
		
		if (self::$outputsJSON)
		{
			return json_encode(self::$data);
		}
		elseif (self::$plainPoutput and !self::haveError())
		{
			return self::$data['payload'];
		}
		
		return pre(dump(self::$data));
	}
}

?>