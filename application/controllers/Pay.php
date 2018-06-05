<?php
class PayController extends BaseController 
{

    public static function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

	public function createOrderAction()
	{
		$uid = $this->checkLogin();
		$type  = (int)$this->getRequest()->getPost('type', -1);
		$securityDepositModel = new SecurityDepositModel();

		//script auto create
		if ($type === 0 ) {
			$goodId = (int)$this->getRequest()->getPost('good_id', -1);
			exit();
		} else if ($type === 1) { 
			$orderNumber = date("YmdHis") . "1" . rand(100, 999);
			$order = array('uid' => $uid, 'amount' => 1, 'order_number'=>$orderNumber); 
			$id = $securityDepositModel -> insert($order);
			if (!$id) 
				Response::displayJson(Response::E_MYSQL, NULL);
			
			Response::displayJson(Response::E_SUCCESS, NULL, array('order_number'=>$orderNumber));
		}
	}


	public function getWxPayParamAction()
	{
		$uid = $this->checkLogin();
		$orderNumber = $this->getRequest()->getQuery('order_number', false);

		if (!$orderNumber || strlen($orderNumber) != 18) 
			Response::displayJson(Response::E_PARAM, NULL);

		$flag = (int)substr($orderNumber, 14, 1);

		$title = "保证金";
		if ($flag === 0) {
			$orderModel = new OrderModel();
			$order = $orderModel->scalar("good_id, (pay_price + fee) * 100 as total_fee", "order_number='{$orderNumber}'", "id desc");
			if ($order) {
				$goodModel = new GoodModel();
				$good = $goodModel->getRow($order['good_id'], "title");
				if ($good) {
					$title = $good['title'];
				} else {
					Response::displayJson(Response::E_NO_OBJ, NULL);
				}
						
			}
		} else {
			$securityDepositModel = new SecurityDepositModel();
			$order = $securityDepositModel->scalar("amount*100 as total_fee", "order_number='{$orderNumber}'", "id desc");
		}

		if (!$order)
			Response::displayJson(Response::E_NO_OBJ, NULL);
		
		//$rs = Wechat::pay($this->openId, $orderNumber, $order['total_fee']);
		$rs = Wechat::pay($this->openId, $orderNumber, 1);
		if (!$rs) {
			Response::displayJson(Response::E_WX_REQ, NULL);
		}



		$currtime = time();
		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/wechat.ini", "miniprogram");	
		$appid = $ini->get('appid'); 
		$key = $ini->get('pay_key');

		$nonceStr = self::getNonceStr();
		$data = array(
			'appId' => $appid,
			'timeStamp' => $currtime,
			'nonceStr' => $nonceStr, 
			'package' => 'prepay_id='.$rs['prepay_id'],
			'signType' => 'MD5',	
		);

		$query = sprintf("appId=%s&nonceStr=%s&package=prepay_id=%s&signType=%s&timeStamp=%d&key=%s", $appid, $nonceStr, $rs['prepay_id'], 'MD5', $currtime, $key); 
		$data['paySign'] =  strtoupper(md5($query));
		$data['order'] = $order;
		Response::displayJson(Response::E_SUCCESS, NULL, $data);
	}

	/**
	 * curl -d "good_id=146" http://test.apa7.cc/pay/createOrder
	 */
	/*
	public function createOrderAction()
	{
		$gid = (int)$this->getRequest()->getPost('good_id');
		$uid = $this->getUid();

		$goodModel = new GoodModel();
		$good = $goodModel -> getRow($gid, "title, last_uid, last_price, status");

		if ($good && $good['last_uid'] == $uid && $good['status'] == 2) {
			$data = array(
				'appid' => '',
				'timeStamp' => '',
				'nonceStr' => '',
				'package' => '',
				'signType' => 'MD5',	
			);
			Response::displayJson(Response::E_SUCCESS, NULL, $data);
			
		} else {
			Response::displayJson(Response::E_FAILURE, NULL);
		}
	}
	*/

	/**
	 * curl -d "status=1&order_number=201805300348131133" http://test.apa7.cc/pay/updateOrder?3rd_session=AGLrj9NRAms0bKI3c_qXTtWKJatDfqld
	 */
	public function updateOrderAction()
	{
		/*
		$uid = $this->checkLogin();
		$orderNumber = $this->getRequest()->getPost('order_number', false);
		$payStatus = (int)$this->getRequest()->getPost('status', 0);

		$flag = substr($orderNumber, 14, 1);
		$currtime = time();

		if ($flag == '0') {
			$orderModel = new OrderModel();
			$where = "uid={$uid} and order_number='{$orderNumber}' and pay_status=0";
			$order = $orderModel -> scalar("id,good_id,status,pay_status", $where, "id desc");
			if (!$order)
				Response::displayJson(Response::E_PARAM, NULL);
	
			$rs = $orderModel->update($order['id'], array('pay_status'=>$payStatus));	
			if (!$rs)
				Response::displayJson(Response::E_MYSQL, NULL);

			$goodModel = new GoodModel();	
			$rs = $goodModel->update($order['good_id'], array('status'=> 3));
			if (false === $rs)
				Response::displayJson(Response::E_MYSQL, NULL);
		} else {
			$securityDepositModel = new SecurityDepositModel();
			$where = "uid={$uid} and order_number='{$orderNumber}' and pay_time=0";
			$order = $securityDepositModel -> scalar("id,amount", $where, "id desc");
			if (!$order)
				Response::displayJson(Response::E_PARAM, NULL);
	
			$rs = $securityDepositModel ->update($order['id'], array('pay_time'=>$currtime));	
			if (!$rs)
				Response::displayJson(Response::E_MYSQL, NULL);

			$userModel = new UserModel();	
			$rs = $userModel->update($uid, array('balance'=>$order['amount']));
			if (!$rs)
				Response::displayJson(Response::E_MYSQL, NULL);
		}

		Response::displayJson(Response::E_SUCCESS, NULL);
		*/
	}


	function wxNotifyAction()
	{
		$notify = new PayNotifyCallBack();
		$notify->Handle(false);
		exit;
	}
}
