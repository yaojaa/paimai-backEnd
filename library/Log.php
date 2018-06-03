<?php

define("WX_NOTIFY_LOG_PATH", "/data/logs/wechat/");

class Log
{
	

	public static function wxNotify($item, $file, $line, $msg)
	{
		$msg = date("Y-m-d H:i:s") . "\t{$item}\t{$file}:{$line}\t{$msg}\n";
		error_log($msg, 3, WX_NOTIFY_LOG_PATH . "/" . date("Ymd") . ".log");
	}
}
