<?php

namespace Infira\Fookie\controller;

use Infira\Utils\Http;
use AppConfig;

class Operation extends Controller
{
	public final function handle($return = false)
	{
		ini_set('memory_limit', '2024M');
		set_time_limit(999);
		$name = Http::getGET("opName");
		if (method_exists($this, $name))
		{
			//define("VOID_PROFILER", TRUE);
			$output = $this->$name();
			if (!$output)
			{
				return 'ok';
			}
		}
		else
		{
			return "Operatsiooni($name) ei leitud";
		}
	}
	
	public function touch()
	{
		return "Session touched";
	}
	
	public function env()
	{
		exit(AppConfig::getENV());
	}
	
	public function testEmail()
	{
		if (Http::getGET("hash") == "asdas89f0sdhgsdg98")
		{
			$Mail = new \KIS\helper\mailer\KIS();
			if (Http::getGET("email"))
			{
				$Mail->addAddress(Http::getGET("email"));
			}
			else
			{
				$Mail->addAddress("gen@infira.ee");
			}
			$Mail->Subject = "Test";
			$Mail->Body    = "Tere gen, testin emaili saatmist, veendumaks et kas kõik on korras";
			debug($Mail->send());
			debug($Mail->ErrorInfo);
		}
		echo "Hello world!";
		echo Prof()->dumpTimers();
	}
	
	public function shortUrl()
	{
		$Db = new TShortUrl();
		$Db->ID(Http::getGET("path"));
		$path = $Db->select("path")->getFieldValue("path", "");
		Http::go301(Path::toUrl($path));
	}
	
	public function viewLog()
	{
		if (Http::getGET("hash") == "1a74df14" && Http::existsGET("ID"))
		{
			$Db       = new TLog();
			$logRowID = Http::getGET("ID");
			if ($logRowID == "last")
			{
				$Db->orderBy("ID DESC");
				$Db->limit(1);
			}
			else
			{
				$Db->ID($logRowID);
			}
			$Obj = $Db->select()->getObject();
			if (is_object($Obj))
			{
				if ($Obj->isSerialized == 1)
				{
					debug(unserialize($Obj->content));
				}
				else
				{
					debug($Obj->content);
				}
			}
		}
	}
	
	public function viewDbLog()
	{
		ini_set('memory_limit', '1G');
		if (Http::getGET("hash") == "1a74df14")
		{
			$Db  = DbLog::Db();
			$dir = (in_array(Variable::toLower(Http::getGET("dir")), ["asc", "desc"])) ? Http::getGET("dir") : "asc";
			$Db->orderBy("ID $dir");
			$whereFields = ["ID", "tableName", "tableRowID", "eventName", "userID", "url"];
			foreach ($whereFields as $field)
			{
				$whereFields[] = "$field-not";
			}
			foreach ($whereFields as $field)
			{
				if (Http::existsGET($field))
				{
					if (substr($field, -4) == "-not")
					{
						$getField = $field;
						$field    = substr($field, 0, -4);
						if ($field == "url")
						{
							$Db->$field->notLike(Http::getGET($getField), true);
						}
						else
						{
							$Db->$field->not(Http::getGET($getField));
						}
					}
					else
					{
						if ($field == "url")
						{
							$Db->$field->like(Http::getGET($field), true);
						}
						else
						{
							$Db->$field(Http::getGET($field));
						}
					}
				}
			}
			
			if (Http::existsGET("date"))
			{
				if (Http::existsGET("dateOp"))
				{
					switch (Http::getGET("dateOp"))
					{
						case ">":
							$Db->insertDateTime->biggerEq(Http::getGET("date"));
						break;
						case "<":
							$Db->insertDateTime->smaller(Http::getGET("date"));
						break;
					}
				}
				else
				{
					$Db->insertDateTime(Http::getGET("date"));
				}
			}
			elseif (Http::existsGET("dateFrom") and Http::existsGET("dateTo"))
			{
				$Db->insertDateTime->between(Http::getGET("dateFrom"), Http::getGET("dateTo"));
			}
			$pos     = (Http::existsGET("pos")) ? Http::getGET("pos") : 0;
			$lastPos = (Http::existsGET("lastPos")) ? Http::getGET("pos") : 0;
			$limit   = (Http::existsGET("limit")) ? Http::getGET("limit") : 50;
			$Db->limit($limit);
			$Db->dontNullFields();
			$query = $Db->getSelectQuery("uncompress(db_log.data) AS _data, db_log.*");
			debug($query);
			$list     = $Db->select("uncompress(db_log.data) AS _data, db_log.*")->getObjects();
			$parseRow = function ($row)
			{
				if (!isset($row->isParsed))
				{
					unset($row->data);
					$row->data           = json_decode($row->_data);
					$row->insertDateTime = Date::toDateTime($row->insertDateTime);
					$row->isParsed       = true;
					unset($row->_data);
				}
				
				return $row;
			};
			$getRow   = function ($pos, $row) use ($list, $parseRow)
			{
				$get = Http::getGET();
				if (isset($_GET["opName"]))
				{
					unset($get["opName"]);
				}
				if (isset($_GET["route"]))
				{
					unset($get["route"]);
				}
				$get["pos"] = $pos;
				if (isset($row->_data))
				{
					$row = $parseRow($row);
				}
				if (isset($list[($pos - 1)]))
				{
					$lget        = $get;
					$lget["pos"] -= 1;
					echo '<a href="' . $this->Router->getLink("op/viewDbLog", $lget) . '">Prev</a> ';
				}
				if (isset($list[($pos + 1)]))
				{
					$lget        = $get;
					$lget["pos"] += 1;
					echo '<a href="' . $this->Router->getLink("op/viewDbLog", $lget) . '">Next</a> ';
				}
				debug($row);
			};
			$collect  = [];
			if (Http::existsGET("fieldContain"))
			{
				$newList = [];
				foreach ($list as $key => $rowO)
				{
					$row       = $parseRow($rowO);
					$fieldName = Http::getGET("fieldContain");
					if (Http::existsGET("fieldValue"))
					{
						if (isset($row->data->fields->$fieldName))
						{
							if ($row->data->fields->$fieldName == Http::getGET("fieldValue"))
							{
								$collect[] = $row->tableRowID;
								$newList[] = $row;
							}
						}
					}
					else
					{
						if (isset($row->data->fields->$fieldName))
						{
							$collect[] = $row->tableRowID;
							$newList[] = $row;
						}
					}
				}
				$list = $newList;
			}
			debug("fieldContains", $collect);
			if (Http::existsGET("whereContain"))
			{
				$newList = [];
				foreach ($list as $key => $rowO)
				{
					$row       = $parseRow($rowO);
					$fieldName = Http::getGET("whereContain");
					if (Http::existsGET("whereValue"))
					{
						if (isset($row->data->where->$fieldName))
						{
							if ($row->data->where->$fieldName == Http::getGET("whereValue"))
							{
								$newList[] = $row;
							}
						}
					}
					else
					{
						if (isset($row->data->where->$fieldName))
						{
							$newList[] = $row;
						}
					}
				}
				$list = $newList;
			}
			if (Http::existsGET("debugAll"))
			{
				$arr = [];
				foreach ($list as $row)
				{
					$row   = $parseRow($row);
					$arr[] = $row;
				}
				debug($arr);
				exit;
			}
			
			if (isset($list[$pos]))
			{
				$getRow($pos, $list[$pos]);
			}
		}
		exit;
	}
	
	public function dumpRedis()
	{
		debug(Cache::$Driver->Redis->getItems());
	}
	
	public function redisCacheSize()
	{
		debug("redis all usage", Cache::$Driver->Redis->getClient()->info()["used_memory_human"]);
		$startMemory = memory_get_usage();
		$all         = Cache::$Driver->Redis->getItems();
		debug("redis current usage", formatSize(memory_get_usage() - $startMemory));
	}
}

?>
