<?php
class OrderController extends BackendController {
	
	protected $model = null;
	const PAGE_SIZE = 15;

	public function init()
	{
		parent::init();
		$this->model = new OrderModel();
	}

    public function listAction() {
		$page = (int)$this->getRequest()->getQuery("page", 0);
		$where = "1=1";
		$list = $this->model->getLimit("id,uid,address_id,order_number,good_id,pay_price,fee,pay_status", $where, "id desc", $page, self::PAGE_SIZE);

		$goodModel = new GoodModel();
		$userModel = new UserModel();
		$addressModel = new AddressModel();

		foreach ($list as &$o) {
			$good = $goodModel->getRow($o['good_id'], "title");
			$o['good_title'] = $good['title'];
			$user = $userModel->getRow($o['uid'], 'nick');
			$o['buyer_nick'] = $user['nick'];
			$address = $addressModel->getRow($o['address_id'], "telephone");
			$o['buyer_telephone'] = $address['telephone'];
		}

		$total = $this->model->getCount($where);
		$pageHtml = Pager::default_pager($total, self::PAGE_SIZE, $page, ceil($total/self::PAGE_SIZE));
		$this->getView()->assign('list', $list);
		$this->getView()->assign('pageHtml', $pageHtml);
		$this->getView()->display('admin_order_list.html');
		exit;
    }
    public function viewAction() {
		$id = (int)$this->getRequest()->getQuery("id", 0);
		$order = $this->model->getRow($id, "*");

		$goodModel = new GoodModel();
		$good = $goodModel->getRow($order['good_id'], "start_price,title,pic,end_time,last_price");
		$good['pic_url'] = ImageModel::getUrl($good['pic']);

		$userModel = new UserModel();
		$user = $userModel->getRow($order['uid'], 'nick');

		if ($order['address_id']) {
			$addressModel = new AddressModel();
			$address = $addressModel->getRow($order['address_id'], "province_id,city_id,area_id,address,name,telephone");
	
			$areaModel = new AreaModel();
			$area = $areaModel->getRow($address['province_id'], "name");
			$address['province_name'] = $area['name'];
			$area = $areaModel->getRow($address['city_id'], "name");
			$address['city_name'] = $area['name'];
			$area = $areaModel->getRow($address['area_id'], "name");
			$address['area_name'] = $area['name'];
			$this->getView()->assign("address", $address);
		}

		$this->getView()->assign("order", $order);
		$this->getView()->assign("good", $good);
		$this->getView()->assign("user", $user);

		$this->getView()->display('admin_order_view.html');
		exit;
    }

	public function cancelAction()
	{
		$id = (int)$this->getRequest()->getQuery("id", 0);
		$rs = $this->model->update($id, array('pay_status'=>-1));
		Response::back("操作成功");
	}
}
?>

