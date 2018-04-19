<?php

class GoodModel extends BaseModel
{
	protected $db = 'db_pai';
	protected $pk = 'id';
	protected $table = 'tbl_good';

	public function prepareData($parameters)
	{
	}


	public static function getStatusName($status, $stime=0, $etime=0)
	{
		$t = time();
		switch ($status) {
			case -1:
				return '已删除';
			case 0:
				return '待上架';
			case 1:
				return $t < $stime ? '预拍' : ($t > $etime ? '已拍结' : '热拍中');
			case 2:
				return '已下架';
			case 3:
				return '已拍结';
			case 4:
				return '已支付';
			default:
				return 'Unknown';
		}

	}


	public function getLimit($columns, $where, $order, $page, $pagesize)
	{
		$list = parent::getLimit($columns, $where, $order, $page, $pagesize);
		if (!$list) return array();

		foreach ($list as &$r) {
			if (isset($r['pic'])) $r['pic_url'] = ImageModel::getUrl($r['pic']);
		}
		unset($r);

		return $list;
	}
}
