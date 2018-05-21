<?php

class ImageModel
{
	public static $config = NULL;

	public static function getUrl($name)
	{
		if (!self::$config)
			self::$config = new Yaf_Config_Ini(ROOT_PATH."/conf/image.ini", "product");

		$c = self::$config->toArray();
		return $c['url'] . $name;
	}


	public static function fullGoodsUrl($goods)
	{
		if (!self::$config)
			self::$config = new Yaf_Config_Ini(ROOT_PATH."/conf/image.ini", "product");
		$c = self::$config->toArray();
		foreach ($goods as &$r) {
			$r['pic_url'] = $c['url'] . $r['pic'];	
		}
		unset($r);

		return $goods;
	}
}
