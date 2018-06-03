<?php
ini_set('date.timezone','Asia/Shanghai');
require_once dirname(__FILE__)."/WxpayAPI_php_v3.0.1/lib/WxPay.Api.php";
require_once dirname(__FILE__)."/WxpayAPI_php_v3.0.1/lib/WxPay.JsApiPay.php";
require_once dirname(__FILE__)."/WxpayAPI_php_v3.0.1/example/log.php";

$logHandler = new CLogFileHandler("/data/logs/wechat/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);


/**
 * 
 */
class Wechat 
{
	public static function pay($openId, $orderNumber, $totalFee)
	{
		//①、获取用户openid
		//$tools = new JsApiPay();
		//$openId = $tools->GetOpenid();

		//②、统一下单
		$input = new WxPayUnifiedOrder();
		$input->SetBody("test");
		$input->SetAttach("test");
		$input->SetOut_trade_no($orderNumber);
		$input->SetTotal_fee($totalFee);
		$input->SetTime_start(date("YmdHis"));
		$input->SetTime_expire(date("YmdHis", time() + 86400));
		$input->SetNotify_url("http://test.apa7.cc/pay/wxNotify");
		$input->SetTrade_type("JSAPI");
		$input->SetOpenid($openId);
		$order = WxPayApi::unifiedOrder($input);
		
		if ($order['return_code'] == 'SUCCESS' && $order['result_code'] == 'SUCCESS') {
			return $order;	
		}

		return false;
		
		//$jsApiParameters = $tools->GetJsApiParameters($order);
		//获取共享收货地址js函数参数
		//$editAddress = $tools->GetEditAddressParameters();
	}


	public static function query()
	{
		//https://api.mch.weixin.qq.com/pay/orderquery
	}
}
