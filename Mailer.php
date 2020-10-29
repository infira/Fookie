<?php

namespace Infira\Fookie;

use Infira\Fookie\facade\Is;

class Mailer extends \PHPMailer\PHPMailer\PHPMailer
{
	protected $isMailTypeHtml = false;
	
	public function addAddress($address, $name = null)
	{
		return $this->addEmailsCaller($address, $name, "addAddress");
	}
	
	public function addBCC($address, $name = null)
	{
		return $this->addEmailsCaller($address, $name, "addBCC");
	}
	
	public function addCC($address, $name = null)
	{
		return $this->addEmailsCaller($address, $name, "addCC");
	}
	
	public function addReplyTo($address, $name = null)
	{
		return $this->addEmailsCaller($address, $name, "addReplyTo");
	}
	
	private function addEmailsCaller($address, $name = null, $functionName)
	{
		if ($address && $name === null)
		{
			$emails = preg_split('/,|;|:|\|/', $address);
			if (checkArray($emails))
			{
				foreach ($emails as $email)
				{
					$email = trim($email);
					if (!checkArray($email))
					{
						if (Is::email($email))
						{
							parent::$functionName($email);
						}
					}
				}
			}
		}
		else
		{
			return parent::$functionName($address, $name);
		}
	}
	
	public function isHTML($isHtml = true)
	{
		parent::isHTML($isHtml);
		$this->isMailTypeHtml = $isHtml;
	}
	
	public function send()
	{
		//alternative body email must be defined as well
		if ($this->isMailTypeHtml)
		{
			$this->msgHTML($this->Body);
		}
		
		return parent::send();
	}
	
	public function setAttachements($arr)
	{
		$this->attachment = $arr;
	}
	
	public function addAttachment($path, $name = '', $encoding = self::ENCODING_BASE64, $type = '', $disposition = 'attachment')
	{
		if (!file_exists($path))
		{
			addExtraErrorInfo("path", $path);
			alertEmail("File does not exists");
		}
		parent::addAttachment($path, $name, $encoding, $type, $disposition);
	}
	
}

?>