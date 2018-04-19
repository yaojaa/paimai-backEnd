<?php
class Response
{
	const E_SUCCESS = 1000;
	const E_FAILURE = 2000;
	const E_PARAM = 3001;
	const E_NO_PIC = 3002;
	const E_USER_OR_PWD = 4001;
	
	
	public static $errors = array(
		#1000
		self::E_SUCCESS => 'OK',
		#2000
		self::E_FAILURE => 'failure',
		self::E_NO_PIC => '没有图片上传',
		#3000
		self::E_PARAM => '商品不存在',
		#4000
		self::E_USER_OR_PWD => '用户名或密码错误',
	);

	public static function getError($errno)
	{
		return self::$errors[$errno];
	}
	
	public static function displayJson($errno, $msg = NULL, $data = NULL)
	{
		$msg = $msg ? $msg : self::$errors[$errno];
		echo json_encode(array('errno' => $errno, 'message'=>$msg, 'data'=>$data, 'server_timestamp'=>time()));
		exit;
	}	


	public static function redirect($url)
	{
		header("Location:$url");
		exit;
	}

	public static function alert($msg, $gotoUrl)
	{
		echo "<script>";
		echo "alert('{$msg}');";
		echo "location.href='{$gotoUrl}';";
		echo "</script>";
		exit;
	}

	public static function back($msg)
	{
		echo "<script>";
		echo "alert('{$msg}');";
		echo "window.history.back()";
		echo "</script>";
		exit;
	}
}
