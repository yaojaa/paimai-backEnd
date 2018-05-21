<?php
ini_set('date.timezone', 'Asia/Shanghai');
//error_reporting(E_ERROR);
define("WXPAY_LOG_PATH", "/data/log/wxpay/");
define("WXPAY_LIB", dir(__FILE__)."/WxpayAPI_php_v3.0.1/");
require_once WXPAY_LIB . "/lib/WxPay.Api.php";
require_once WXPAY_LIB . "/WxPay.JsApiPay.php";
require_once WXPAY_LIB . '/log.php';


//初始化日志
$logHandler = new CLogFileHandler(WXPAY_LOG_PATH . date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class Pay
{
	public function wxJsApiPay()
	{
		$tools = new JsApiPay();
		$openId = $tools->GetOpenid();
	}
}
