<?php
Class CookieToken 
{
	const TOKEN = "MOBILE!#&!*!**^((";	

	public static $domain = '';
	public static $path = '/';
	public static $expire = -1;

	private $id = 0;
	private $time = 0;

	public function __construct($token = false)
	{
		$data = false;
		if ($token) {
			$data = self::decode($token);
		} else if (isset($_COOKIE['token'])) {
			$data = self::decode($_COOKIE['token']);
		}

		if ($data) {
			$this->id = $data['id'];
			$this->openid = $data['openid'];
			$this->time = $data['time'];
		}
	}


	public function login($id, $openid, $time)
	{
		$token = self::encode($id, $openid, $time);
		setcookie('token', $token, self::$expire, self::$path, self::$domain);
	}

	public function getId()
	{
		return $this->id;
	}


	public static function encode($id, $openid, $time)
	{
		$data['dust'] = "";
	  	$apsid = pack('a32a64Ia32', $id, $openid, $time, self::TOKEN); 
		
		$apsid = base64_encode(strtr( $apsid, array( '+' => '-', '/' => '~' ) ));
		$int_mod = 2;
		$str_key_char = array();
		$str_key_char[0] = $apsid[0];
		$str_key_char[1] = $apsid[1];
	
		$len_apsid = strlen( $apsid );
	
		foreach ( range( 0,$len_apsid - $int_mod - 1 ) as $i ) {
			$apsid[$i] = $apsid[$i + $int_mod];
		}
		
		$apsid[$len_apsid - 2] = $str_key_char[0];
		$apsid[$len_apsid - 1] = $str_key_char[1];

		return $apsid;	
	}
	
	public static function decode( $apsid ) {
		$apsid = trim( $apsid );
		$len_apsid = strlen( $apsid );
		if ( $len_apsid < 176) {
			return false;
		}
	
		$str_key_char = array();
		$int_mod = 0;
	
		foreach ( range( 0, $len_apsid - 1 ) as $i ) {
			$int_mod += ( ord( $apsid[$i] ) + $i );
		}
	
		//$int_mod = $int_mod % 2 + 1;
		$int_mod = 2;
	
		$str_key_char[0] = $apsid[$len_apsid - 2];
		$str_key_char[1] = $apsid[$len_apsid - 1];
	
		foreach ( range( $len_apsid - $int_mod, 0 ) as $i ) {
			$apsid[$i + $int_mod] = $apsid[$i];
		}
	
		switch( $int_mod ) {
			case 1:
				$apsid[0] = $str_key_char[1];
				break;
			case 2:
				$apsid[0] = $str_key_char[0];
				$apsid[1] = $str_key_char[1];
				break;
			default:
				return false;
		}
	    $apsid = base64_decode( strtr( $apsid, array( '-' => '+', '~' => '/' ) ) );
	    if(!empty($apsid)){
	    	$data = unpack( 'a32id/a64openid/Itime/a32token', $apsid );
	    }
	
	    return $data;
	}

}
