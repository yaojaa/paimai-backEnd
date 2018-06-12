<?php
class BackendController extends Yaf_Controller_Abstract {

	protected $adminId = 0;

	public function init()
	{
		$sLogin = new SessionLogin();
		$this->adminId = $sLogin->check();
		if (!$this->adminId) Response::redirect('/admin/login');
	}
}


