<?php

class UploadController extends Yaf_Controller_Abstract {

	public function init()
	{
		$sLogin = new SessionLogin();
		$uid = $sLogin->check();
		//if (!$uid) Response::redirect('/admin/login');
	}

    public function picAction() {
		if (!isset($_FILES['pic'])) Response::displayJson(Response::E_NO_PIC);
		$ini = new Yaf_Config_Ini(ROOT_PATH."/conf/image.ini","product");
		$c = $ini->toArray();
	 	Upload::$sNameing = 'md5';                                                                              
		$up = new Upload($_FILES['pic'],  $c['save_path'], array('gif', 'jpg', 'png', 'jpeg'));                                                      
		$succ = $up->upload();
		if ($succ < 1) Response::displayJson(Response::E_NO_PIC);
		$infos = $up->getSaveInfo();
		$url = $infos[0]['saveas'];
		Response::displayJson(Response::E_SUCCESS, NULL, $url);
    }

    public function simditorAction() {
		$return = array('success'=>false, 'msg'=>'', 'file_path'=> '');
		if (!isset($_FILES['pic'])) {
			$return['msg'] = '没有图片被上传';
			echo json_encode($return);
			exit;
		}

		$ini = new Yaf_Config_Ini(ROOT_PATH."/conf/image.ini","product");
		$c = $ini->toArray();
	 	Upload::$sNameing = 'md5';                                                                              
		$up = new Upload($_FILES['pic'],  $c['save_path'], array('gif', 'jpg', 'png', 'jpeg'));                                                      
		$succ = $up->upload();
		if ($succ < 1) {
			$return['msg'] = '图片保存失败';
			echo json_encode($return);
			exit;
		}

		$infos = $up->getSaveInfo();
		$url = $infos[0]['saveas'];
		$return['success'] = true; 
		$return['file_path'] = $c['url'].$url;
		echo json_encode($return);
		exit;
    }



}
?>

