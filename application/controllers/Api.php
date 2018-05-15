<?php
class ApiController extends Yaf_Controller_Abstract {

	private $uid = 0;

	public function getUid()
	{
		return 1;
	}

	public function checkLogin()
	{
		$uid = $this->getUid();
		if (!$this->uid) {
			Response::displayJson(Response::E_USER_NO_LOGIN, NULL);
		}

		return $this->uid;
	}

    public function indexAction() {
		exit("Welcome!");
    }


	public function getGoodListAction()
	{
		$pagesize = 3;
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

		$list = $goodModel->getLimit("id,author,title,pic,start_price,last_price,start_time,end_time", $where, "seqid asc, id desc", $page, $pagesize);
		$t = time();
		$authorModel = new AuthorModel();
		foreach ($list as &$r) {
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
		if (!$good) 
			Response::displayJson(Response::E_PARAM, NULL);

		$good['pic_url'] = ImageModel::getUrl($good['pic']);

		Response::displayJson(Response::E_SUCCESS, NULL, $good);
	}


	

	/** 
	 * curl -d "id=146&price=100" http://test.apa7.cc/api/setGoodOffer
	 */
	public function setGoodOfferAction()
	{
		$time = time();
		$id = (int)$this->getRequest()->getPost('id', 0);
		$price = (int)$this->getRequest()->getPost('price', 0);
		if ($id <= 0 || $price <= 0)
			Response::displayJson(Response::E_PARAM, NULL);

		# check good 
		$goodModel = new GoodModel();
		$good = $goodModel->getRow($id, "start_price,incr_price,last_price,last_uid");
		if (!$good) Response::displayJson(Response::E_NO_OBJ);

		# check offer
		if (!$good['last_uid']) {
			if ($price != $good['start_price'] &&  $price < $good['start_price'] + $good['incr_price']) {
				Response::displayJson(Response::E_NO_OBJ, $good['start_price']);
			}
		} else { 
			if ($price < $good['last_price'] + $good['incr_price']) {
				Response::displayJson(Response::E_NO_OBJ, $good['last_price']);
			}
		}

		$offerModel = new OfferModel();
		$data = array('good_id'=>$id, 'offer_time' => $time, 'uid' => $this->getUid(), 'price' => $price);
		$rs = $offerModel -> insert($data);	

		if (!$rs) Response::displayJson(Response::E_FAILURE);
		$rs = $goodModel->update($id, array('last_uid'=>$this->getUid(), 'last_price'=>$price, 'last_time'=>$time));
		if (!$rs) Response::displayJson(Response::E_FAILURE);
		Response::displayJson(Response::E_SUCCESS);
	}


	public function getGoodOfferListAction()
	{
		$pagesize = 30;
		$page = (int)$this->getRequest()->getQuery('page', 0);
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$offerModel = new OfferModel();
		$list = $offerModel -> getLimit("id,uid,offer_time,price", "good_id={$id}", "id desc", $page, $pagesize);
		if (!$list) Response::displayJson(Response::E_SUCCESS);
		
		$userModel = new UserModel();
		foreach ($list as &$l) {
			$u = $userModel->getRow($l['uid'], "nick,mobile");
			$l['u_nick'] = $u['nick']; 
			$l['u_head_fmt'] = $u['mobile'];
			$l['offer_time_fmt'] = date("Y-m-d H:i:s", $l['offer_time']);
		}
		unset($l);

		Response::displayJson(Response::E_SUCCESS, $list);
	}


	public function sendMobileVerifyCodeAction()
	{
		$mobile = $this->getRequest()->getPost('mobile', false);
		if (!$mobile)
			Response::displayJson(Response::E_PARAM, NULL);
		
		$code = mt_rand(100000, 999999);
		$rs = Sms::send($mobile, $code);
		if (!$rs) Response::displayJson(Response::E_USER_MOBILE_CODE, NULL);

		$_SESSION['mobile'] = $mobile;
		$_SESSION['code'] = $code;
		Response::displayJson(Response::E_SUCESS, NULL);

		#$mcModel = new MobileCheckModel();
		#$time = time();
		#$data = array('uid' => $this->uid, 'mobile' => $mobile, 'send_time' => $time, 'code' => $code); 
		#$rs = $mcModel -> insert($data);
		#if ($rs) {
		#	Response::displayJson(Response::E_SUCCESS, NULL);
		#}
	}
	
	public function checkMobileVerifyCodeAction()
	{
		$code = $this->getRequest()->getPost('code', false);
		if (!$code)
			Response::displayJson(Response::E_PARAM, NULL);

		if ($code == $_SESSION['code']) {
			Response::displayJson(Response::E_SUCCESS, NULL);
		}

		Response::displayJson(Response::E_FAILURE, NULL);
	}


	public function addressAction()
	{
		$proviceId = $this->getRequest()->getPost('provice_id', false);
		$cityId = $this->getRequest()->getPost('city_id', false);
		$areaId = $this->getRequest()->getPost('area_id', false);
		$address = $this->getRequest()->getPost('address', false);
		$name = $this->getRequest()->getPost('name', false);
		$telephone = $this->getRequest()->getPost('telephone', false);
	
		$data = array(
			'uid' => $uid,
			'provice_id' => $proviceId,
			'city_id' => $cityId,
			'area_id' => $areaId,
			'address' => $address,
			'name' => $name,
			'telphone' => $telephone,
		);

		$addressModel = new AddressModel();
		$id = $addressModel->insert($data);
		if ($id) 
			Response::displayJson(Response::E_SUCCESS, NULL);

		Response::displayJson(Response::E_FAILURE, NULL);
	}


	public function waitingPayAction()
	{
	}

	public function myPaiAction()
	{
		$uid = $this->getUid();
		$pagesize = 15;
		$status = (int)$this->getRequest()->getQuery('status', 0);
		$page = (int)$this->getRequest()->getQuery('page', 1);
		$begin = ($page-1) * $pagesize;

		$model = new GoodModel();
		$orderBy = "order by o.offer_time desc limit {$begin}, {$pagesize}";
		$where = "where g.id=o.good_id and o.uid={$uid}";

		if ($status == 0) {
			$where .= " and g.status=1"; 
		} else if ($status == 1) {
			$where .= " and (g.status=2 or g.status=3) and g.last_uid={$uid}"; 
		} else if ($status == 2) {
			$where .= " and (g.status=2 or g.status=3) and g.last_uid!={$uid}"; 
		}

		$list = $model->getQuery("select distinct(g.id), author, title, pic, last_uid, last_price, start_price, end_time from tbl_good g, tbl_offer o {$where} {$orderBy}");

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

}
?>

