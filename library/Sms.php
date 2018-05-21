<?php
require __DIR__ . "/qcloudsms_php-master/src/SmsSenderUtil.php";
require __DIR__ . "/qcloudsms_php-master/src/SmsSingleSender.php";

use Qcloud\Sms\SmsSingleSender;

class Sms 
{
	CONST APP_ID = 'AKIDtfF2iEI0zrbDf3BTULEKI9pNj6hBrmqi';
	CONST APP_KEY = 'oU7mJfolfR8PjutkRzajkZFoDTYSAgaM';
		
	public static function sendOne($mobile, $code)
	{
		try {
			$ini = new Yaf_Config_Ini(ROOT_PATH."/conf/sms.ini", "single");
			$appid = $ini->get("appid");
			$appkey = $ini->get("appkey");
			$format = $ini->get("format");
			$content = sprintf($format, $code, 10);
    		$ssender = new SmsSingleSender($appid, $appkey);
    		$result = $ssender->send(0, "86", $mobile, $content, "", "");
    		$rsp = json_decode($result);
			var_dump($rsp);
			return true;
		} catch (\Exception $ex) {
			return false;
		}
	}
}
