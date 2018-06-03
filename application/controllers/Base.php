<?php
class BaseController extends Yaf_Controller_Abstract {

	protected $uid = 0;
	protected $openId = '';

	public function getUid()
	{
		$this->uid = $this->getRequest()->getQuery('uid', 0);
		if ($this->uid) return $this->uid;

		if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
			$rd3session = $_SERVER['HTTP_AUTHORIZATION']; 
		} else {
			$rd3session = $this->getRequest()->getQuery('3rd_session', false);
		}

		if ($rd3session) {
			$sessMysqlModel = new SessionMysqlModel();
			$this->openId = $sessMysqlModel->getOpenId($rd3session);
			if ($this->openId) {
				$userModel = new UserModel();
				$this->uid = $userModel->getUid($this->openId);
			}
		}

		return $this->uid;
	}

	public function checkLogin()
	{
		$uid = $this->getUid();
		if (!$uid) {
			Response::displayJson(Response::E_USER_NO_LOGIN, NULL);
		}

		return $uid;
	}
}

