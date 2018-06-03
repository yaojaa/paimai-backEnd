<?php
ini_set('date.timezone','Asia/Shanghai');
require_once dirname(__FILE__)."/WxpayAPI_php_v3.0.1/lib/WxPay.Api.php";
require_once dirname(__FILE__)."/WxpayAPI_php_v3.0.1/lib/WxPay.Notify.php";

class PayNotifyCallBack extends WxPayNotify
{

	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::wxNotify("Tran_id={$transaction_id}", __FILE__, __LINE__,  json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::wxNotify("OrderNumber={$data['out_trade_no']}", __FILE__, __LINE__,  json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}

		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}

		$status = $this->updateOrderStatus($data['out_trade_no'], $data['total_fee']);
		Log::wxNotify("OrderNumber=".$data['out_trade_no'], __FILE__, __LINE__, "updateOrderStatus={$status}");
		if ($status < 0) return false;

		return true;
	}



	public function updateOrderStatus($orderNumber, $totalFee)
	{
		$flag = substr($orderNumber, 14, 1);
		$currtime = time();

		if ($flag == '1') {
			$orderModel = new OrderModel();
			$where = "order_number='{$orderNumber}'";
			$order = $orderModel -> scalar("id,good_id,pay_price,fee,pay_status", $where, "id desc");
			if (!$order) return -1;
			if ($order['pay_price'] + $order['fee'] != $totalFee) return -2;
			if ($order['pay_status'] !== 0) return 1; 
	
			$rs = $orderModel->update($order['id'], array('pay_status'=>1, 'pay_time'=>$currtime));	
			if (!$rs) return -3;

			$goodModel = new GoodModel();	
			$data = array('status'=> 3);
			$rs = $goodModel->update($order['good_id'], $data);
			if (!$rs) Log::DEBUG($orderNumber, __FILE__, __LINE__ , "tbl_good.id={$order['good_id']}\t" . json_encode($data) . "\tFALSE");

		} else {
			$securityDepositModel = new SecurityDepositModel();
			$where = "order_number='{$orderNumber}'";
			$order = $securityDepositModel -> scalar("id,uid,amount,pay_time", $where, "id desc");
			if (!$order) return -1;
			if ($order['amount'] != $totalFee) return -2;
			if ($order['pay_time'] > 0) return 1;
	
			$rs = $securityDepositModel ->update($order['id'], array('pay_time'=>$currtime));	
			if (!$rs) return 1;

			$userModel = new UserModel();	
			$data = array('balance'=>$order['amount']);
			$rs = $userModel->incr($order['uid'], $data);
			if (!$rs) Log::wxNotify($orderNumber, __FILE__, __LINE__,  "tbl_user.id={$order['uid']}\t" . json_encode($data) . "\tFALSE");

		}


		return 0;

	}
}

