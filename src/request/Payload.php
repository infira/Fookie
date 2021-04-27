<?php

namespace Infira\Fookie\request;

use Infira\Utils\Http;
use Infira\Utils\Is;
use AppConfig;
use Infira\Fookie\facade\Variable;

class Payload
{
	private static $data         = [];
	private static $root         = 'payload';
	private static $payload      = null;
	private static $error        = [];
	private static $plainPoutput = false;
	private static $outputsJSON  = false;
	
	/**
	 * @var callable
	 */
	private static $beforSend = null;
	
	public static function setBeforSend(callable $beforSend): void
	{
		self::$beforSend = $beforSend;
	}
	
	public static function setRoot(?string $name)
	{
		self::$root = $name;
	}
	
	public static function setHeader($header)
	{
		header($header);
	}
	
	public static function setCustomHeader(string $name, string $value)
	{
		self::setHeader('X-' . $name . ': ' . $value);
	}
	
	public static function setRequestID(int $ID)
	{
		self::setCustomHeader('requestID', (string)$ID);
	}
	
	public static function setJSONHeader()
	{
		self::setHeader('Content-Type: application/json');
		self::$outputsJSON = true;
	}
	
	public static function setField(string $name, $value)
	{
		self::$data[$name] = $value;
	}
	
	public static function getField(string $name, $returnOnNotFound = null)
	{
		if (!self::existsField($name))
		{
			return $returnOnNotFound;
		}
		
		return self::$data[$name];
	}
	
	public static function existsField(string $name): bool
	{
		return array_key_exists($name, self::$data);
	}
	
	public static function sendError($error, string $code = null, $httpStatusCode = 400)
	{
		http_response_code($httpStatusCode);
		if (!$code)
		{
			$code = 'general';
		}
		self::$error['error'] = $code;
		if (Is::isClass($error, 'Infira\Error\Error'))
		{
			$convert = ['msg' => 'message', 'title' => 'code'];
			foreach ((array)$error->getStack() as $name => $val)
			{
				if (isset($convert[$name]))
				{
					$name = $convert[$name];
				}
				self::$error[$name] = $val;
			}
		}
		elseif (is_string($error))
		{
			self::$error['message'] = $error;
		}
		else
		{
			self::$error['message'] = $error;
		}
		foreach (self::$data as $k => $v)
		{
			self::$error[$k] = $v;
		}
		self::send();
	}
	
	/**
	 * @param mixed $payload
	 */
	public static function send($payload = null)
	{
		if ($payload)
		{
			self::set($payload);
		}
		if (self::$beforSend)
		{
			$cb = self::$beforSend;
			$cb();
		}
		echo self::getOutput();
		exit;
	}
	
	public static function haveError(): bool
	{
		return (bool)self::$error;
	}
	
	public static function set($payload)
	{
		if (self::$root)
		{
			self::setField(self::$root, $payload);
		}
		else
		{
			self::$payload = $payload;
		}
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
				self::$error['message'] = str_replace(['<br />', '<br>', '< br>'], "\n", self::$error['message']);
			}
		}
		if (self::haveError())
		{
			$output = self::$error;
		}
		else
		{
			if (self::$root === null)
			{
				$output = self::$payload;
			}
			else
			{
				if (self::$plainPoutput)
				{
					$output = self::$data[self::$root];
				}
				else
				{
					$output = self::$data;
				}
			}
		}
		Route::saveRequestResponse($output);
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