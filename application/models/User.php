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
		$user = $this->scalar("id", "openid='{$openId}'", "id desc");
		if ($user) return $user['id'];
		return false;
	}

	public function fmtUserHead(&$user)
	{
		$user['pic'] = $user['pic'] ? $user['pic'] : "/static/img/defaut_avatar.png";	
	}

}
