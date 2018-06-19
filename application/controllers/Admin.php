<?php
session_start();
class AdminController extends Yaf_Controller_Abstract {
	private $adminId = 0;
	public function init()
	{
		$sLogin = new SessionLogin();
		$this->adminId = $sLogin->check();
	}

    public function indexAction() {
		if (!$this->adminId) Response::redirect('/admin/login');
		$this->getView()->display("admin_index.html");
		exit;
    }

	public function loginAction()
	{
		if ($this->getRequest()->getMethod() == 'POST') {
			$username = $this->getRequest()->getPost('username');
			$password = $this->getRequest()->getPost('password');
			$vcode = $this->getRequest()->getPost('vcode');
			#if ($vcode || $vcode != $_SESSION['vcode']) 
			#	Response::displayJson(Response::E_VCODE);
			$password = md5($password);
			$adminModel = new AdminModel();
			$user = $adminModel->scalar("id,password", "username='$username'", "id asc");
			if (!$user || $user['password'] != $password) Response::displayJson(Response::E_USER_OR_PWD);
			$sLogin = new SessionLogin();
			$sLogin->login($user['id'], $username);
			Response::displayJson(Response::E_SUCCESS);
		}
		
		$this->getView()->display("admin_login.html");
		exit;
	}

	public function logoutAction()
	{
		$sLogin = new SessionLogin();
		$sLogin->logout();
		Response::redirect("/admin/login");
	}

	public function verifycodeAction()
	{
		$vc = new VerifyCode();  //实例化一个对象
		$_SESSION['vcode'] = $vc->randrsi();  
		$vc->draw();
		exit;
	}
}

