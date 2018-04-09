<?php
class Response
{
	const E_SUCCESS = 1000;
	const E_FAILURE = 2001;
	
	public static $errors = array(
		self::E_SUCCESS => 'OK',
		self::E_FAILURE => 'failure',
	);
	
	public static function displayJson($errno, $msg = NULL, $data = NULL)
	{
		$msg = $msg ? $msg : self::$errors[$errno];
		echo json_encode(array('errno' => $errno, 'message'=>$msg, 'data'=>$data, 'server_timestamp'=>time()));
		exit;
	}	
}
