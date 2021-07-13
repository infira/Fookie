<?php

namespace Infira\Fookie\controller;

use Infira\Utils\Http;
use AppConfig;
use Db;
use Infira\Poesis\Poesis;
use Infira\Fookie\request\Route;
use Infira\Fookie\Log;
use Infira\Fookie\facade\Variable;
use Infira\Utils\Date;

class Operation extends Controller
{
	public function touch(): string
	{
		return "Session touched";
	}
	
	public function env()
	{
		exit(AppConfig::getENV());
	}
	
	public function viewLog()
	{
		if (Http::getGET("hash") == '1a74df14' and Http::existsGET('ID'))
		{
			return Log::getContent(Http::getGET('ID'));
		}
		exit;
	}
	
	public function viewDbLog()
	{
		if (Http::getGET("hash") == "1a74df14")
		{
			$dataModelName = Poesis::getLogDataModel();
			/**
			 * @var $dbData \TDbLogData
			 */
			$dbData    = new $dataModelName();
			$modelName = Poesis::getLogModel();
			/**
			 * @var $dbLog \TDbLog
			 */
			$dbLog = new $modelName();
			
			$ID     = Http::getGET("ID", null);
			$dataID = Http::getGET("dataID", null);
			if ($ID == 'last')
			{
				$dbLog->limit(1);
				$dbLog->orderBy('ID DESC');
				$dataID = $dbLog->select('dataID')->getValue('dataID');
			}
			
			
			$dir = (in_array(Variable::toLower(Http::getGET("dir", 'asc')), ["asc", "desc"])) ? Http::getGET("dir") : "asc";
			$dbData->orderBy("ID $dir");
			if ($dataID)
			{
				$dbData->ID($dataID);
			}
			if (Http::existsGET("tableName"))
			{
				$dbData->tableName(Http::getGET("tableName"));
			}
			if (Http::existsGET("tableRowID"))
			{
				$dbData->tableRowID(Http::getGET("tableRowID"));
			}
			if (Http::existsGET("eventName"))
			{
				$dbData->eventName(Http::getGET("eventName"));
			}
			if (Http::existsGET("userID"))
			{
				$dbData->userID(Http::getGET("userID"));
			}
			if (Http::existsGET("url"))
			{
				$dbData->url(Http::getGET("url"));
			}
			if (Http::existsGET("date"))
			{
				if (Http::existsGET("dateOp"))
				{
					switch (Http::getGET("dateOp"))
					{
						case ">":
							$dbData->insertDateTime->biggerEq(Http::getGET("date"));
						break;
						case "<":
							$dbData->insertDateTime->smaller(Http::getGET("date"));
						break;
					}
				}
				else
				{
					$dbData->insertDateTime(Http::getGET("date"));
				}
			}
			elseif (Http::existsGET("dateFrom") and Http::existsGET("dateTo"))
			{
				$dbData->insertDateTime->between(Http::getGET("dateFrom"), Http::getGET("dateTo"));
			}
			$limit = (Http::existsGET("limit")) ? Http::getGET("limit") : 50;
			//$dbData->limit("$pos,1");
			$dbData->limit($limit);
			$dbData->dontNullFields();
			$query = "ID,ts,uncompress(data) AS data,userID,eventName,tableName,rowIDCols,url,ip";
			$dr    = $dbData->select($query);
			debug(['logQuery' => [
				'getParams' => Http::getGET(),
				'query'     => $dr->getQuery(),
			]]);
			$list = $dr->eachCollect(function ($log)
			{
				$log->data     = json_decode($log->data);
				$log->ts       = Date::toDateTime($log->ts);
				$statements    = $log->data->statements;
				$contitionsMet = true;
				
				if (Http::existsGET("setField"))
				{
					$contitionsMet = false;
					foreach ($statements as $statement)
					{
						$clause    = $statement->setClauses;
						$fieldName = Http::getGET("setField");
						if (Http::existsGET("setValue"))
						{
							if (isset($clause->$fieldName))
							{
								if ($clause->$fieldName == Http::getGET("setValue"))
								{
									$contitionsMet = true;
									break;
								}
							}
						}
						else
						{
							if (isset($clause->$fieldName))
							{
								$contitionsMet = true;
								break;
							}
						}
					}
				}
				
				if (Http::existsGET("whereField"))
				{
					$contitionsMet = false;
					foreach ($statements as $statement)
					{
						$fieldName = Http::getGET("whereField");
						foreach ($statement->whereClauses as $clause)
						{
							if (Http::existsGET("whereValue"))
							{
								if (isset($clause->$fieldName))
								{
									if ($clause->$fieldName == Http::getGET("whereValue"))
									{
										$contitionsMet = true;
										break;
									}
								}
							}
							else
							{
								if (isset($clause->$fieldName))
								{
									$contitionsMet = true;
									break;
								}
							}
						}
					}
				}
				if (!$contitionsMet)
				{
					return Poesis::VOID;
				}
				
				return $log;
			});
			
			$show = function ($pos, $row) use (&$list)
			{
				$get        = Http::getGET();
				$get["pos"] = $pos;
				$get["op"]  = "viewDbLog";
				
				if (isset($list[($pos - 1)]))
				{
					$lget        = $get;
					$lget["pos"] -= 1;
					echo '<a href="' . Route::getLink('./', $lget) . '">Prev</a> ';
				}
				if (isset($list[($pos + 1)]))
				{
					$lget        = $get;
					$lget["pos"] += 1;
					echo '<a href="' . Route::getLink('./', $lget) . '">Next</a> ';
				}
				debug($row);
			};
			
			$pos = (Http::existsGET("pos")) ? Http::getGET("pos") : 0;
			
			if (Http::existsGET("debugAll"))
			{
				debug($list);
				exit;
			}
			else
			{
				if (isset($list[$pos]))
				{
					$show($pos, $list[$pos]);
				}
			}
		}
		exit;
	}
	
	public function viewErrorLog()
	{
		if (Http::getGET("hash") == "a12g3fs14g3d5h36gk56hilasd3a")
		{
			$Db = Db::TErrorLog();
			$Db->ID(Http::getGET("ID"));
			$Db->orderBy("ID ASC");
			echo $Db->select('content')->getValue('content');
		}
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
	
	public function testEmail()
	{
		if (Http::getGET("hash") == "asdas89f0sdhgsdg98")
		{
			$Mail = new Infira();
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
}

?>
