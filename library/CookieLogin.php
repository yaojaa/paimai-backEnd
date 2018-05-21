<?php

class CookieLogin
{
	CONST TOKEN_SECRET = 'EveryThingIsPossible';

	private $uidTag = 'uid';
	private $uid = 0;
	
	private $tokenTag = 'token';
	private $token = '';

	private $timeTag = 'time';
	private $time = 0;

	private $expire = 0;
	private $path = '/';
	private $domain = '';


	public function __construct($expire = -1, $path = '/', $domain = '')
	{
		$this->expire = time() + $expire;
		$this->path = $path;
		$this->domain = ".".$domain;

		if (isset($_COOKIE[$this->tokenTag])) { 
			$this->token = $_COOKIE[$this->tokenTag]; 
		}

		if ($this->token && $this->verifyToken()) {
			$this->uid = $_COOKIE[$this->uidTag]; 
			$this->time = $_COOKIE[$this->timeTag];
		}
	}

	function getUid() 
	{
		return $this->uid;
	}

	function setUid($uid)
	{
		$this->uid = $uid;
		setcookie($this->uidTag, $this->uid, $this->expire, $this->path, $this->domain);
	}

	function setTime()
	{
		$this->time = time();
		setcookie($this->timeTag, $this->time, $this->expire, $this->path, $this->domain);
	}

	function setToken()
	{
		$token = $this->uid . "|" . $this->time . "|" . self::TOKEN_SECRET;
		$this->token = md5($token);
		setcookie($this->tokenTag, $this->token, $this->expire, $this->path, $this->domain);
	}

	private function verifyToken()
	{
		$token = $_COOKIE[$this->uidTag] . "|" . $_COOKIE[$this->timeTag] . "|" . self::TOKEN_SECRET;
		$token = md5($token);
		return $token == $this->token;
	}
}
