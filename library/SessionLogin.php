<?php
@session_start();
class SessionLogin 
{
	const TOKEN = "";

	public function __construct()
	{
	}

	public function check()
	{
		return isset($_SESSION['uid']) ? $_SESSION['uid'] : false;
	}

	public function login($id, $username)
	{
		$_SESSION['uid'] = $id;	
		$_SESSION['username'] = $username;
	}


	public function logout()
	{
		if (isset($_SESSION['uid'])) { 
			unset($_SESSION['uid']);
			unset($_SESSION['username']);
		}
	}
}
