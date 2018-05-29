<?php 
class SessionMysqlModel extends BaseModel
{
	protected $db = 'db_pai';
	protected $pk = 'id';
	protected $table = 'tbl_session';

	public function prepareData($parameters)
	{
	}

	public function getOpenId($rd3SessionKey)
	{
		$row = $this->scalar('openid', "3rd_session='{$rd3SessionKey}'", "id desc");
		if ($row) return $row['openid'];
		return false;
	}

}
