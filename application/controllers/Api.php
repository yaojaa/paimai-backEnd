<?php
class ApiController extends Yaf_Controller_Abstract {

    public function indexAction() {
		exit("Welcome!");
    }

	public function getGoodListAction()
	{
		$pagesize = 15;
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

		$list = $goodModel->getLimit("id,author_id,title,pic,start_price,last_price", $where, "seqid asc, id desc", $page, $pagesize);

		$authorModel = new AuthorModel();
		foreach ($list as &$r) {
			$author = $authorModel->getRow($r['author_id'], "id,name");
			if (!$author) continue;
			$r['author'] = $author;
		}
		unset($r);



		Response::displayJson(Response::E_SUCCESS, NULL, $list);
	}
}
?>

