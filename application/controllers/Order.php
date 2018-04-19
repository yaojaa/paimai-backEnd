<?php
class OrderController extends Yaf_Controller_Abstract {

    public function listAction() {
		$this->getView()->display('admin_order_list.html');
		exit;
    }
    public function viewAction() {
		$this->getView()->display('admin_order_view.html');
		exit;
    }
}
?>

