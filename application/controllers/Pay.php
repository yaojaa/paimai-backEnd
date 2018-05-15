<?php
class PayController extends Yaf_Controller_Abstract 
{
	public function getUid()
	{
		return 1;
	}

	/**
	 * curl -d "good_id=146" http://test.apa7.cc/pay/createOrder
	 */
	public function createOrderAction()
	{
		$gid = (int)$this->getRequest()->getPost('good_id');
		$uid = $this->getUid();

		$goodModel = new GoodModel();
		$good = $goodModel -> getRow($gid, "title, last_uid, last_price, status");

		if ($good && $good['last_uid'] == $uid && $good['status'] == 2) {
			$data = array(
				'appid' => '',
				'mch_id' => '',
				'nonce_str' => '',
				'sign' => '',
				'body' => $good['title'],
				'out_trade_no' => '',
				'total_fee' => '',
				'spbill_create_ip' => '',
				'notify_url' => '/pay/wxNotify',
				'trade_type' => 'JSAPI',
			);
			Response::displayJson(Response::E_SUCCESS, NULL, $data);
			
		} else {
			Response::displayJson(Response::E_FAILURE, NULL);
		}
	}


	function wxNotifyAction()
	{
		echo 'wx notify url';
		exit;
	}
}
