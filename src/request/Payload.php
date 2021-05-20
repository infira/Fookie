<?php

namespace Infira\Fookie\request;

use Infira\Utils\Http;
use Infira\Utils\Is;
use Infira\Fookie\facade\Rm;

class Payload
{
	private static $data           = [];
	private static $root           = 'payload';
	private static $payload        = null;
	private static $error          = [];
	private static $plainPoutput   = false;
	private static $outputsJSON    = false;
	private static $jsonEncodeFlag = null;
	
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
	
	/**
	 * @see https://www.php.net/manual/en/function.json-encode.php
	 * @param int $flag
	 */
	public static function setJsonEncodeFlag(int $flag)
	{
		self::$jsonEncodeFlag = $flag;
	}
	
	public static function setField(string $name, $value)
	{
		self::$data[$name] = $value;
	}
	
	public static function setErrorField(string $name, $value)
	{
		self::$error[$name] = $value;
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
		if (Rm::exists('errorReplicateLink'))
		{
			self::setField('errorReplicateLink', Rm::get('errorReplicateLink'));
		}
		http_response_code($httpStatusCode);
		if (!$code)
		{
			$code = 'general';
		}
		self::setErrorField('error', $code);
		if (Is::isClass($error, 'Infira\Error\Error'))
		{
			$convert = ['msg' => 'message', 'title' => 'code'];
			foreach ((array)$error->getStack() as $name => $val)
			{
				if (isset($convert[$name]))
				{
					$name = $convert[$name];
				}
				self::setErrorField($name, $val);
			}
		}
		elseif (is_string($error))
		{
			self::setErrorField('message', $error);
		}
		else
		{
			self::setErrorField('message', $error);
		}
		foreach (self::$data as $k => $v)
		{
			self::setErrorField($k, $v);
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
	
	public static function plainOutput()
	{
		self::$plainPoutput = true;
	}
	
	public static function getOutput(): ?string
	{
		if ((Http::acceptJSON() or self::$outputsJSON) and !self::$plainPoutput)
		{
			self::setJSONHeader();
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
			if (self::$jsonEncodeFlag)
			{
				return json_encode($output, self::$jsonEncodeFlag);
			}
			
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