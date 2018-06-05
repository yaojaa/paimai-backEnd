<?php

define("WX_NOTIFY_LOG_PATH", "/data/logs/wechat/");
define("ORDER_LOG_PATH", "/data/logs/");
define("API_LOG_PATH", "/data/logs/");

class Log
{
	

	public static function wxNotify($item, $file, $line, $msg)
	{
		$msg = date("Y-m-d H:i:s") . "\t{$item}\t{$file}:{$line}\t{$msg}\n";
		error_log($msg, 3, WX_NOTIFY_LOG_PATH . "/" . date("Ymd") . ".log");
	}

	public static function createOrder($item, $file, $line, $msg)
	{
		$msg = date("Y-m-d H:i:s") . "\t{$item}\t{$file}:{$line}\t{$msg}\n";
		error_log($msg, 3, ORDER_LOG_PATH. "/order_" . date("Ymd") . ".log");
	}

	public static function api($item, $file, $line, $msg)
	{
		$msg = date("Y-m-d H:i:s") . "\t{$item}\t{$file}:{$line}\t{$msg}\n";
		error_log($msg, 3, ORDER_LOG_PATH. "/api_" . date("Ymd") . ".log");
	}

}
