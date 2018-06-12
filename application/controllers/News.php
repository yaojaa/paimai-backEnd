<?php
class NewsController extends BackendController {
	
	protected $model = null;
	const PAGE_SIZE = 15;

	public function init()
	{
		parent::init();
		$this->model = new NewsModel();
	}


	public function listAction()
	{
		$page = (int)$this->getRequest()->getQuery("page", 0);
		$search = $this->getRequest()->getQuery('search', '');
		$where = 'status > -1';
		if ($search) $where .= " and title like '%{$search}%'";

		$list = $this->model->getLimit("id,title,source,status,create_time", $where, "id desc", $page, self::PAGE_SIZE);
		if ($list) {
			foreach ($list as &$r) {
				$r['status_name'] = NewsModel::getStatusName($r['status']);
				$r['create_time_fmt'] = date("Y-m-d H:i:s", $r['create_time']);
			}
			unset($r);
		}

		$total = $this->model->getCount($where);
		$pageHtml = Pager::default_pager($total, self::PAGE_SIZE, $page, ceil($total/self::PAGE_SIZE));

		$this->getView()->assign('list', $list);
		$this->getView()->assign('pageHtml', $pageHtml);
		$this->getView()->display('admin_news_list.html');
		exit;
	}

    public function addAction() {
		$error = '';

		if ($this->getRequest()->getMethod() == 'POST') {
			$id = (int)$this->getRequest()->getPost('id', 0);
			$title = $this->getRequest()->getPost('title', '');
			$pic = $this->getRequest()->getPost('pic', '');
			$source = $this->getRequest()->getPost('source', '');
			$content = $this->getRequest()->getPost('content', '');
			$currtime = time();

			if (!$title) {
				$error = '标题不能为空'; 
				goto GOTO_ERROR;
			}

			if (!$source) {
				$error = '来源不能为空'; 
				goto GOTO_ERROR;
			}

			if (!$pic) {
				$error = '图片不能为空'; 
				goto GOTO_ERROR;
			}

			if (0 === strpos($pic, 'http')) {
				$pic = end(explode("/", $pic));
			}
			
			$data = array(
						'admin_id'=>$this->adminId,
						'title' => $title,
						'pic' => $pic,
						'source' => $source,
						'content' => $content,
						'status' => 1,
						'create_time' => $currtime,
					);

			if ($id) {
				$rs = $this->model->update($id, $data);
			} else {
				$rs = $this->model->insert($data);
			}

			if (false !== $rs) Response::alert('提交成功!', '/admin/news/list');

GOTO_ERROR:
			$this->getView()->assign('error', $error);
		} else {
			$id = (int)$this->getRequest()->getQuery('id', 0);
			if ($id > 0) {
				$news = $this->model->getRow($id, "*");
				$news['pic_url'] = ImageModel::getUrl($news['pic']);
				$this->getView()->assign("news", $news);
			}
		}

		$this->getView()->display('admin_news_add.html');
		exit;
    }


	public function deleteAction()
	{
		$id = (int)$this->getRequest()->getQuery('id', 0);
		$this->model = new NewsModel();
		$rs = $this->model->delete($id);
		if (false === $rs)
			Response::back("操作失败");
		Response::back("操作成功");
	}



}

