<?php

class NewsModel extends BaseModel
{
	const STATE_OFFLINE = 0;
	const STATE_ONLINE = 1;
	protected $db = 'db_pai';
	protected $pk = 'id';
	protected $table = 'tbl_news';

	public function prepareData($parameters)
	{
	}

	public static function getStatusName($status)
	{
		if ($status == self::STATE_OFFLINE) return '下架';
		if ($status == self::STATE_ONLINE) return '上架';
	}

}
