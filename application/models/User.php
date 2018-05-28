<?php
class UserModel extends BaseModel
{
	protected $db = 'db_pai';
	protected $pk = 'id';
	protected $table = 'tbl_user';

	public function prepareData($parameters)
	{
	}


	public function getUid($openId)
	{
		$user = $this->scalar("id", "openid='{$openId}'");
		if ($user) return $row['id'];
		return false;
	}
}
