<?php
ini_set("display_errors", 1);
error_reporting(2047);

define("ROOT_PATH",  realpath(dirname(__FILE__) . '/../'));
define("APP_PATH",  ROOT_PATH);
define("CONFIG_PATH",  ROOT_PATH."/conf");
ini_set("yaf.library", ROOT_PATH . "/library");
$app  = new Yaf_Application(ROOT_PATH . "/conf/application.ini");
$app->execute("main");

function main()
{
	$ini = new Yaf_Config_Ini(CONFIG_PATH . "/application.ini", "good");
	$paiDelaySeconds = $ini->get('pai_delay_seconds');
	$serviceFeeRate = $ini->get('service_fee_rate');

	$currtime = time();
	$orderModel = new OrderModel();
	$goodModel = new GoodModel();	
	$list = $goodModel->getAll("id,end_time,last_uid,last_price,last_time", "status=1 and end_time<={$currtime} and last_uid!=0 and last_price!=0", "id asc");

	foreach ($list as $k=>$g) {
		//Part 1
		if ($g['last_time'] + $paiDelaySeconds > $currtime) {
			continue;
		}

		$orderNumber = date("YmdHis") . "0" . sprintf("%03d", $k);
		$order = array(
			'uid' => $g['last_uid'], 
			'good_id' => $g['id'],
			'order_number' => $orderNumber,
			'pay_price' => $g['last_price'],
			'pay_status' => 0,
			'fee' => $g['last_price'] * $serviceFeeRate, 
		);

		$id = $orderModel -> insert($order);
		$order['id'] = $id;

		if (!$id) {
			Log::createOrder("order:{$orderNumber}", __FILE__, __LINE__, json_encode($order));
			continue;
		}
		
		$rs = $goodModel->update($g['id'], array("status" => 2));
		$order['good_status'] = 2;
		Log::createOrder("order:{$orderNumber}", __FILE__, __LINE__, json_encode($order));

	}
}
