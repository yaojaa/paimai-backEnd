<?php
class ApiController extends Yaf_Controller_Abstract {

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

		$authorModel = new AuthorModel();
		$author = $authorModel->getRow($good['author_id'], "name");
		if ($author) $good['author'] = $author['name'];

		$good['pic_url'] = ImageModel::getUrl($good['pic']);

		Response::displayJson(Response::E_SUCCESS, NULL, $good);
	}
}
?>

