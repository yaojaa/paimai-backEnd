<?php
/**
 * wechat web authorize 
 * https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1421140842
 */

class PassportController extends Yaf_Controller_Abstract 
{

	// wx auth
	public function wxLoginAction()
	{
		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/wechat.ini", "product");	
		$authUrl = $ini->get('auth_url');
		$appid = $ini->get('appid'); 
		$redirectUri = $ini->get('redirect_uri'); 
		$url = sprintf($authUrl, $appid, $redirectUri);
		header("Location:{$url}");
		exit;
	}

	// wx callback
	public function wxcallbackAction()
	{
		//获取code 拼接成url
		$code = $this->getRequest()->getQuery('code');
		$currtime = time();

		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/wechat.ini", "product");	
		$appid = $ini->get('appid'); 
		$secret = $ini->get('secret');
		$accessTokenUrl = $ini->get('access_token_url');
		$url = sprintf($accessTokenUrl, $appid, $secret, $code);

		/*
		{
		"access_token":"ACCESS_TOKEN",
		"expires_in":7200,
		"refresh_token":"REFRESH_TOKEN",
		"openid":"OPENID",
		"scope":"SCOPE" 
		}
		*/
		$oneArr       = json_decode(file_get_contents($url), TRUE);
		$accessToken = $oneArr['access_token'];
		$openId       = $oneArr['openid'];

		$userModel = new UserModel();
		$user = $userModel->scalar("id", "openid='$openId'", "id desc");
		// has logined
		if ($user) goto U_LOGIN; 

		$userInfo = $this->_wxUserInfo($accessToken, $openId);

		if (isset($userInfo['openid'])) {
			$user = array(
				'openid'=>$userInfo['openid'],
				'nick' => $userInfo['nickname'],
				'sex' => $userInfo['sex'],
				'pic' => $userInfo['headimgurl'],
				'access_token' => $accessToken,
				'access_token_expireat' => $currtime + $oneArr['expires_in'],
				'create_time' => $currtime,
			);
			$uid = $userModel->insert($user);
			if (!$uid) {
				Response::displayJson(Response::E_MYSQL, NULL);
			}
			$user['id'] = $uid;
		} else {
			Response::displayJson(Response::E_WX_REQ, NULL);
		}
U_LOGIN:
		Response::displayJson(Response::E_SUCCESS, NULL, $user);
	}

	private function _wxUserInfo($accessToken, $openId)
	{
		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/wechat.ini", "product");	
		$userInfoUrl = $ini->get('userinfo_url');
		$url = sprintf($userInfoUrl, $accessToken, $openId);
		return json_decode(file_get_contents($url), TRUE);
	}








	public function wxVerifyTokenAction()
	{

		$echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }

	}

    private function checkSignature()
    {
		$ini = new Yaf_Config_Ini(ROOT_PATH . "/conf/wechat.ini", "product");	
		$token = $ini->get('token'); 
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

}
