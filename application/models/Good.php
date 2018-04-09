<?php

class GoodModel extends BaseModel
{
	protected $db = 'db_pai';
	protected $pk = 'id';
	protected $table = 'tbl_good';

	public function prepareData($parameters)
	{
	}


	public function getLimit($columns, $where, $order, $page, $pagesize)
	{
		$list = parent::getLimit($columns, $where, $order, $page, $pagesize);
		if (!$list) return array();

		foreach ($list as &$r) {
			$r['pic_url'] = ImageModel::getUrl($r['pic']);
		}
		unset($r);

		return $list;
	}
}
