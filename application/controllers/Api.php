<?php
header("Access-Control-Allow-Origin: *"); 

class ApiController extends BaseController 
{

	const PAGESIZE = 15;

    public function indexAction() {
		exit("Welcome!");
    }


	public function getGoodListAction()
	{
		$status = (int)$this->getRequest()->getQuery('status', 0);
		$page = (int)$this->getRequest()->getQuery('page', 0);
		$t = time();
		$goodModel = new GoodModel();

		$where = "status=1";
		if ($status == 0) {
			$where .= " and start_time < {$t} and end_time > {$t}";
		} else if ($status == 1) {
			$where .= " and start_time > {$t}";
		} else if ($status == 2) {
			$where .= " and end_time < {$t}";
		} else {
			$where .= " and 1=0";
		}

		$list = $goodModel->getLimit("id,author,title,pic,start_price,last_price,start_time,end_time", $where, "seqid asc, id desc", $page, self::PAGESIZE);
		$t = time();
		$authorModel = new AuthorModel();
		foreach ($list as &$r) {
			$r['start_time_fmt'] = date("Y-m-d H:i:s", $r['start_time']);
			$r['end_time_fmt'] = date("Y-m-d H:i:s", $r['end_time']);
			$r['pai_status'] = $status;
			if ($status == 1) $r['remain_time'] = $r['start_time'] -  $t;
		}
		unset($r);

		Response::displayJson(Response::E_SUCCESS, NULL, $list);
	}



	public function getGoodInfoAction()
	{
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$goodModel = new GoodModel();
		$good = $goodModel->getRow($id, "*");

		if (!$good || ($good['status'] != 1 && $good['status'] != 2 && $good['status'] != 3)) 
			Response::displayJson(Response::E_PARAM, NULL);

	
		$currtime = time();
		$good['pai_status'] = 2;	
		if ($good['status'] == 1) {
			if ($currtime < $good['start_time']) {
				$good['pai_status'] = 1;
				$good['remain_time'] = $good['start_time'] - $currtime;
			} else if ($currtime < $good['end_time']) {
				$good['pai_status'] = 0;
				$good['remain_time'] = $good['end_time'] - $currtime;
			}
		}

		$good['start_time_fmt'] = date("Y-m-d H:i:s", $good['start_time']);
		$good['end_time_fmt'] = date("Y-m-d H:i:s", $good['end_time']);
		$good['pic_url'] = ImageModel::getUrl($good['pic']);

		Response::displayJson(Response::E_SUCCESS, NULL, $good);
	}


	

	/** 
	 * curl -d "id=146&price=100" http://test.apa7.cc/api/setGoodOffer
	 */
	public function setGoodOfferAction()
	{
		$uid = $this->checkLogin();
		$id = (int)$this->getRequest()->getPost('id', 0);
		$price = (int)$this->getRequest()->getPost('price', 0);
		$currtime = time();

		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/application.ini", "good");	
		$securityDeposit = $ini->get('security_deposit');
		$delaySeconds = $ini->get('pai_delay_seconds');

		# check good exists
		$goodModel = new GoodModel();
		$good = $goodModel->getRow($id, "start_time,end_time,security_deposit,start_price,incr_price,last_price,last_uid,last_time,status");
		if (!$good) Response::displayJson(Response::E_NO_OBJ);

		# check good status
		if ($good['status'] != GoodModel::GOOD_STATUS_ONLINE) {
			Response::displayJson(Response::E_GOOD_PAI_STOP);
		}

		# check pai end time
		if ($good['end_time'] < $currtime) {
			Response::displayJson(Response::E_GOOD_PAI_STOP);
		}

		# security_deposit
		if ($good['security_deposit']) {
			$userModel = new UserModel();
			$user = $userModel->getRow($uid, "balance");

			if ($user['balance'] < $securityDeposit) {
				Response::displayJson(Response::E_SECURITY_DEPOSIT, NULL, array('security_deposit'=>$securityDeposit));
			}
			//if ($user['security_deposit'] < $good['security_deposit']) {
			//	Response::displayJson(Response::E_SECURITY_DEPOSIT, NULL, array('security_deposit'=>$good['security_deposit']));
			//}
		}

		# check offer
		$startPrice = $good['last_price'] ? $good['last_price'] + $good['incr_price'] : $good['start_price'];
		if ($price < $startPrice) {
			Response::displayJson(Response::E_PRICE_ILL, "当前出价不能低于{$startPrice}元", array('not_smaller_than'=>$startPrice));
		}

		$offerModel = new OfferModel();
		$data = array('good_id'=>$id, 'offer_time' => $currtime, 'uid' => $uid, 'price' => $price);
		$rs = $offerModel -> insert($data);	
		if (!$rs) Response::displayJson(Response::E_MYSQL);

		$rs = $goodModel->update($id, array('last_uid'=>$this->getUid(), 'last_price'=>$price, 'last_time'=>$currtime));
		if (false === $rs) Response::displayJson(Response::E_MYSQL);

		// delay end time	
		if ($currtime + $delaySeconds > $good['end_time']) {
			$rs = $goodModel->update($good['id'], array('end_time' => $currtime + $delaySeconds));
			if (false === $rs) Response::displayJson(Response::E_MYSQL);
			Response::displayJson(Response::E_SUCCESS, NULL, array("end_time_fmt"=> date("Y-m-d H:i:s", $currtime + $delaySeconds)));
		}
			

		Response::displayJson(Response::E_SUCCESS);
	}


	public function getGoodOfferListAction()
	{
		$page = (int)$this->getRequest()->getQuery('page', 0);
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$offerModel = new OfferModel();
		$list = $offerModel -> getLimit("id,uid,offer_time,price", "good_id={$id}", "id desc", $page, self::PAGESIZE);
		if (!$list) Response::displayJson(Response::E_SUCCESS);
		
		$userModel = new UserModel();
		foreach ($list as &$l) {
			$u = $userModel->getRow($l['uid'], "nick,pic");
			$l['u_nick'] = $u['nick']; 
			$l['u_head_fmt'] = $u['pic'];
			$l['offer_time_fmt'] = date("Y-m-d H:i:s", $l['offer_time']);
		}
		unset($l);

		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/application.ini", "good");	
		$delaySeconds = $ini->get('pai_delay_seconds');
		$currtime = time();
		$goodModel = new GoodModel();
		$g = $goodModel -> getRow($id, "end_time");
		if ($g['end_time'] > $currtime && $g['end_time'] < $currtime + $delaySeconds) {
			$list[0]['end_time_fmt'] = date("Y-m-d H:i:s", $g['end_time']);
		}

		Response::displayJson(Response::E_SUCCESS, NULL, $list);
	}


	public function sendMobileVerifyCodeAction()
	{
		$uid = $this->checkLogin();
		$mobile = $this->getRequest()->getPost('mobile', false);
		if (!$mobile)
			Response::displayJson(Response::E_PARAM, NULL);
		
		$code = mt_rand(100000, 999999);
		$rs = Sms::sendOne($mobile, $code);
		if (!$rs) Response::displayJson(Response::E_USER_MOBILE_CODE, NULL);

		$mcModel = new MobileVerifyCodeModel();
		$time = time();
		$data = array('uid' => $uid, 'mobile' => $mobile, 'send_time' => $time, 'code' => $code); 
		$rs = $mcModel -> insert($data);

		if ($rs)
			Response::displayJson(Response::E_SUCCESS, NULL);

		Response::displayJson(Response::E_FAILURE, NULL);
	}
	
	public function checkMobileVerifyCodeAction()
	{
		$uid = $this->checkLogin();
		$code = $this->getRequest()->getPost('code', 708077);
		$mobile = $this->getRequest()->getPost('mobile', 13718188699);

		if (!$code || !$mobile)
			Response::displayJson(Response::E_PARAM, NULL);

		$mcModel = new MobileVerifyCodeModel();
		$vc = $mcModel->scalar("id, send_time", "uid={$uid} and mobile='{$mobile}' and code=$code and status=0", "id desc");
		if (!$vc) Response::displayJson(Response::E_SMS_ERROR, NULL);
		if (time() - $vc['send_time'] > 600) Response::displayJson(Response::E_SMS_EXPIRED, NULL);
		
		$mcModel->update($vc['id'], array('status'=>1));

		$userModel = new UserModel();
		$rs = $userModel -> update($uid, array('mobile'=>$mobile));
		if ($rs) 
			Response::displayJson(Response::E_SUCCESS, NULL);

		Response::displayJson(Response::E_FAILURE, NULL);
	}


	public function addressAction()
	{
		$uid = $this->checkLogin();
		if ($this->getRequest()->getMethod() != 'POST') {
			$addressModel = new AddressModel();
			$list = $addressModel->getAll("id, province_id, city_id, area_id, address, name, telephone", "uid={$uid}", "id desc");
			if ($list) $addressModel->patch($list);
			Response::displayJson(Response::E_SUCCESS, NULL, $list);
		}

		$addressId = (int)$this->getRequest()->getPost('address_id', false);
		$orderNumber = $this->getRequest()->getPost('order_number', false);

		if ($orderNumber && $addressId) {
			$orderModel = new OrderModel();
			$order = $orderModel->scalar("id", "order_number='{$orderNumber}'", "id desc");

			if (!$order) 
				Response::displayJson(Response::E_PARAM,  NULL);

			$rs = $orderModel->update($order['id'], array('address_id'=>$addressId));
			if (false === $rs)
				Response::displayJson(Response::E_MYSQL,  NULL);
			Response::displayJson(Response::E_SUCCESS);
		}

		$provinceId = (int)$this->getRequest()->getPost('province_id', false);
		$cityId = (int)$this->getRequest()->getPost('city_id', false);
		$areaId = (int)$this->getRequest()->getPost('area_id', false);
		$address = $this->getRequest()->getPost('address', false);
		$name = $this->getRequest()->getPost('name', false);
		$telephone = $this->getRequest()->getPost('telephone', false);

		if (!$provinceId) {
			Response::displayJson(Response::E_ADDRESS_PROVINCE,  NULL);
		}

		if (!$cityId) {
			Response::displayJson(Response::E_ADDRESS_CITY,  NULL);
		}

		if (!$areaId) {
			Response::displayJson(Response::E_ADDRESS_AREA,  NULL);
		}

		if (!$address) {
			Response::displayJson(Response::E_ADDRESS_ADDR,  NULL);
		}

		if (!$name) {
			Response::displayJson(Response::E_ADDRESS_NAME,  NULL);
		}

		if (!$telephone) {
			Response::displayJson(Response::E_ADDRESS_TEL,  NULL);
		}

		$data = array(
			'uid' => $uid,
			'province_id' => $provinceId,
			'city_id' => $cityId,
			'area_id' => $areaId,
			'address' => $address,
			'name' => $name,
			'telephone' => $telephone,
		);

		$addressModel = new AddressModel();
		$id = $addressModel->insert($data);
		if ($id) { 
			Response::displayJson(Response::E_SUCCESS, NULL, array('address_id' => $id));
		} else {
			Response::displayJson(Response::E_FAILURE, NULL);
		}
	}


	public function waitingPayAction()
	{
		$uid = $this->checkLogin();
		$page = (int)$this->getRequest()->getQuery('page', 1);
		$page = $page < 1 ? 1 : $page;
		$orderModel = new OrderModel();
		$orderBy = "id desc";
		$where = "uid={$uid} and pay_status=0 and type=0";
		$orders = $orderModel->getLimit("good_id,address_id,order_number,pay_price,fee", $where, $orderBy, $page, self::PAGESIZE);
		if (!$orders) Response::displayJson(Response::E_SUCCESS, NULL);

		$gids = $aids = array();
		foreach ($orders as $o) {
			$gids[] = $o['good_id'];;
			$aids[] = $o['address_id'];;
		}

		$goodModel = new GoodModel();
		$in = implode(',', $gids);
		$goods = $goodModel -> getAll("id, category, author, title, pic, last_price, start_price", "id in ({$in})", "id asc");
		$goods = ImageModel::fullGoodsUrl($goods);

		$addressModel = new AddressModel();
		$in = implode(',', $aids);
		$addresses = $addressModel -> getAll("id, province_id, city_id, area_id, address, name, telephone", "id in ({$in})", "id asc");

	
		foreach ($orders as &$o) {
			$address = array();
			foreach ($addresses as $a) {
				if ($a['id'] == $o['address_id']) $address = $a;
			}
			$good = array();
			foreach ($goods as $g) {
				if ($g['id'] == $o['good_id']) $good = $g;
			}

			$o = array_merge($good, $o);	
			$o['address'] = $address;
		}
		

		Response::displayJson(Response::E_SUCCESS, NULL, $orders);
	}

	public function myPaiAction()
	{
		$uid = $this->checkLogin();
		$status = (int)$this->getRequest()->getQuery('status', 0);
		$page = (int)$this->getRequest()->getQuery('page', 1);
		$page = $page < 1 ? 1 : $page;
		$begin = ($page-1) * self::PAGESIZE;

		$model = new GoodModel();
		$orderBy = "order by o.offer_time desc limit {$begin}, " . self::PAGESIZE;
		$where = "where g.id=o.good_id and o.uid={$uid}";

		if ($status == 0) {
			$where .= " and g.status=1"; 
		} else if ($status == 1) {
			$where .= " and (g.status=2 or g.status=3) and g.last_uid={$uid}"; 
		} else if ($status == 2) {
			$where .= " and (g.status=2 or g.status=3) and g.last_uid!={$uid}"; 
		}

		$list = $model->getQuery("select distinct(g.id), author, title, pic, last_uid, last_price, start_price, end_time, status from tbl_good g, tbl_offer o {$where} {$orderBy}");

		foreach ($list as &$g) {
			$g['remain_time'] = $g['end_time'] - time();
			$g['pic_url'] = ImageModel::getUrl($g['pic']);

			if ($status == 0) {
				$g['offer_status'] = $g['last_uid'] == $uid ? 1 : 0;
				continue;
			}

			if ($status == 1) {
				$g['pay_status'] = $g['status'] == 3 ? 1 : 0;
				continue;
			}

			if ($status == 2) {
				$g['fail_status'] = 0;
				continue;
			}

		}
		unset($g);

		Response::displayJson(Response::E_SUCCESS, NULL, $list);
	}

	public function userinfoAction()
	{
		$uid = $this->checkLogin();
		$model = new UserModel();
		$user = $model -> getRow($uid, "id,nick,pic,sex,mobile");
		$model->fmtUserHead($user);
		Response::displayJson(Response::E_SUCCESS, NULL, $user);
	}


	public function getNewsListAction()
	{
		$page = (int)$this->getRequest()->getQuery('page', 0);
		$page = $page <= 1 ? 1 : $page;
		$model = new NewsModel();
		$list = $model->getLimit("id,admin_id,title,pic,source,content,create_time", "1=1", "id desc", $page, self::PAGESIZE);
		if (!$list)
			Response::displayJson(Response::E_SUCCESS, NULL, $list);

		foreach ($list as &$r) {
			$r['create_time_fmt'] = date("Y-m-d H:i:s", $r['create_time']);
			$r['content_fmt'] = mb_substr($r['content'], 0, 30);
		}
			
		ImageModel::fullNewsUrl($list);
		Response::displayJson(Response::E_SUCCESS, NULL, $list);
	}


	public function getNewsInfoAction()
	{
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$model = new NewsModel();
		$news = $model->getRow($id);
		if (!$news) 
			Response::displayJson(Response::E_NO_OBJ);

		$news['create_time_fmt'] = date("Y-m-d H:i:s", $news['create_time']);
		ImageModel::fullNewsUrl($news, 0);
		Response::displayJson(Response::E_SUCCESS, NULL, $news);
	}



}

