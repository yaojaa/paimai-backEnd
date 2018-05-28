<?php
class BaseController extends Yaf_Controller_Abstract {

	private $uid = 0;

	public function getUid()
	{
		$rd3session = $this->getRequest()->getQuery('3rd_session', false);
		if ($rd3session) {
			$sessMysqlModel = new SessionMysqlModel();
			$openId = $sessMysqlModel->getOpenId($rd3session);
			if ($openId) {
				$userModel = new UserModel();
				$this->uid = $userModel->getUid($openId);
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

