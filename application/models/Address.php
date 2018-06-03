<?php

class AddressModel extends BaseModel
{
	protected $db = 'db_pai';
	protected $pk = 'id';
	protected $table = 'tbl_address';

	public function prepareData($parameters)
	{
	}


	public function patch(&$list)
	{
		$areaIds = array();
		foreach ($list as $r) {
			$areaIds[] = $r['province_id'];
			$areaIds[] = $r['city_id'];
			$areaIds[] = $r['area_id'];
		}

		$areaModel = new AreaModel();
		$areas = $areaModel->getAll("id,name", "id in (".implode(",", $areaIds).")", "id desc");


		foreach ($list as &$i) {
		
			foreach ($areas as $a) {
				if ($a['id'] == $i['province_id']) 
					$i['province_name'] = $a['name'];
				if ($a['id'] == $i['city_id']) 
					$i['city_name'] = $a['name'];
				if ($a['id'] == $i['area_id']) 
					$i['area_name'] = $a['name'];
			}
		}

		unset($i);
	}

}
