<?php
class GoodController extends Yaf_Controller_Abstract {

	private $adminId = 0;

	public function init()
	{
		$sLogin = new SessionLogin();
		$this->adminId = $sLogin->check();
		if (!$this->adminId) Response::redirect('/admin/login');
	}

    public function listAction() {
		$pagesize = 15;
		$page = (int)$this->getRequest()->getQuery("page", 0);
		$search = $this->getRequest()->getQuery('search', '');
		$where = 'status > -1';
		if ($search) $where .= " and title like '%{$search}%'";
		$goodModel = new GoodModel();
		$list = $goodModel->getLimit("id,title,author,start_time,end_time,seqid,security_deposit,start_price,last_price,status", $where, "id desc", $page, $pagesize);
		if ($list) {
			foreach ($list as &$r) {
				$r['status_name'] = GoodModel::getStatusName($r['status']);
			}
			unset($r);
		}

		$total = $goodModel->getCount("status > -1");
		$pageHtml = Pager::default_pager($total, $pagesize, $page, ceil($total/$pagesize));

		$this->getView()->assign('list', $list);
		$this->getView()->assign('pageHtml', $pageHtml);
		$this->getView()->display('admin_good_list.html');
		exit;
    }

	public function cloneAction()
	{
		$id = $this->getRequest()->getQuery('id', false);
		if (!$id) Response::back('Parameter Error!'); 
		$goodModel = new GoodModel();
		$row = $goodModel->getRow($id, 'title, author_id, author, pic, category, size, format, seal, ecurity_deposit, start_pirce, market_price, incr_price, description'); 
		if (!$row) Response::back('Object not exists!'); 
		$rs = $goodModel->insert($row);
		if ($rs) Response::alert('提交成功', '/admin/good/list');
		Response::back('Operation is fail!');
	}

    public function addAction() {
		$goodModel = new GoodModel();
		$error = '';

		#Submit
		if ($this->getRequest()->getMethod() == 'POST') {
			$id = (int)$this->getRequest()->getPost('id', 0);
			$title = $this->getRequest()->getPost('title', '');
			if (!$title) {
				$error = '标题不能为空'; 
				goto GOTO_ERROR;
			}

			$author = $this->getRequest()->getPost('author', '');
			if (!$author) {
				$error = '作者不能为空'; 
				goto GOTO_ERROR;
			}

			$category = $this->getRequest()->getPost('category', '');
			if (!$category) {
				$error = '类别不能为空'; 
				goto GOTO_ERROR;
			}

			$seqid = (int)$this->getRequest()->getPost('seqid', 0);
			if (!$seqid) {
				$error = '权重不能为空'; 
				goto GOTO_ERROR;
			}

			$size = $this->getRequest()->getPost('size', '');
			$format = $this->getRequest()->getPost('format', '');
			$seal = $this->getRequest()->getPost('seal', '');
			$securityDeposit = (int)$this->getRequest()->getPost('security_deposit', '');
			if (!$securityDeposit) {
				$error = '保证金不能为空'; 
				goto GOTO_ERROR;
			}

			$startPrice = (int)$this->getRequest()->getPost('start_price', 0);
			#if (!$startPrice) {
			#	$error = '起拍价不能为空'; 
			#	goto GOTO_ERROR;
			#}

			$incrPrice = (int)$this->getRequest()->getPost('incr_price', 0);
			$marketPrice = (int)$this->getRequest()->getPost('market_price', 0);
			if (!$marketPrice) {
				$error = '参考价不能为空'; 
				goto GOTO_ERROR;
			}
			
			$startTime = $this->getRequest()->getPost('start_time', '');
			if (!$startTime) {
				$error = '开始时间不能为空'; 
				goto GOTO_ERROR;
			}
			$startTime = strtotime($startTime);
			$endTime = $this->getRequest()->getPost('end_time', '');
			if (!$endTime) {
				$error = '结束时间不能为空'; 
				goto GOTO_ERROR;
			}
			$endTime = strtotime($endTime);
			$description = $this->getRequest()->getPost('description', '');
			$pic = $this->getRequest()->getPost('pic', '');
			if (!$pic) {
				$error = '封面图不能为空'; 
				goto GOTO_ERROR;
			}

			if (0 === strpos($pic, 'http')) {
				$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/image.ini", "product");
				$c = $ini->toArray();
				$len = strlen($c['url']);
				$pic = substr($pic, $len-1);
			}
			
			$data = array(
						'admin_id'=>$this->adminId,
						'author_id' => 0,
						'title' => $title,
						'author' => $author,
						'category' => $category,
						'seqid' => $seqid,
						'size' => $size,
						'format' => $format,
						'seal' => $seal,
						'security_deposit' => $securityDeposit,
						'start_price' => $startPrice,
						'incr_price' => $incrPrice,
						'market_price' => $marketPrice,
						'start_time' => $startTime,
						'end_time' => $endTime,
						'description' => $description,
						'pic' => $pic,
					);
				if ($id) {
					$rs = $goodModel->update($id, $data);
				} else {
					$rs  = $goodModel->insert($data);
				}

				if (false !== $rs) Response::alert('提交成功!', '/admin/good/list');

	GOTO_ERROR:
				$this->getView()->assign('error', $error);
		} else {
			$id = (int)$this->getRequest()->getQuery('id', 0);
			if ($id > 0) {
				$good = $goodModel->getRow($id, "*");
				$good['pic_url'] = ImageModel::getUrl($good['pic']);
				$this->getView()->assign("good", $good);
			}
		}

		$this->getView()->display('admin_good_add.html');
		exit;
    }

	public function offlineAction()
	{
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$goodModel = new GoodModel();
		$rs = $goodModel->update($id, array('status'=>0));
		Response::back("操作成功");
	}

	public function deleteAction()
	{
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$goodModel = new GoodModel();
		$rs = $goodModel->update($id, array('status'=>-1));
		Response::back("操作成功");
	}
}
?>

