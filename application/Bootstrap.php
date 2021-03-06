<?php

/**
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_Dispatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {

	public function _initConfig(Yaf_Dispatcher $dispatcher) {
       $config = Yaf_Application::app()->getConfig();
       Yaf_Registry::set("config", $config);
    }

	public function _initRoute(Yaf_Dispatcher $dispatcher)
	{
        $router = Yaf_Dispatcher::getInstance()->getRouter();

        $route = new Yaf_Route_Rewrite('admin/good/clone', array('controller' => 'good', 'action' => 'clone'));
        $router->addRoute('admin_good_clone', $route);

        $route = new Yaf_Route_Rewrite('admin/good/online', array('controller' => 'good', 'action' => 'online'));
        $router->addRoute('admin_good_online', $route);

        $route = new Yaf_Route_Rewrite('admin/good/offline', array('controller' => 'good', 'action' => 'offline'));
        $router->addRoute('admin_good_offline', $route);

        $route = new Yaf_Route_Rewrite('admin/good/delete', array('controller' => 'good', 'action' => 'delete'));
        $router->addRoute('admin_good_delete', $route);

        $route = new Yaf_Route_Rewrite('admin/good/viewonly', array('controller' => 'good', 'action' => 'viewonly'));
        $router->addRoute('admin_good_viewonly', $route);

        $route = new Yaf_Route_Rewrite('admin/good/add', array('controller' => 'good', 'action' => 'add'));
        $router->addRoute('admin_good_add', $route);

        $route = new Yaf_Route_Rewrite('admin/good/edit', array('controller' => 'good', 'action' => 'edit'));
        $router->addRoute('admin_good_edit', $route);

        $route = new Yaf_Route_Rewrite('admin/good/list', array('controller' => 'good', 'action' => 'list'));
        $router->addRoute('admin_good_list', $route);

        $route = new Yaf_Route_Rewrite('admin/order/list', array('controller' => 'order', 'action' => 'list'));
        $router->addRoute('admin_order_list', $route);

        $route = new Yaf_Route_Rewrite('admin/order/view', array('controller' => 'order', 'action' => 'view'));
        $router->addRoute('admin_order_view', $route);

        $route = new Yaf_Route_Rewrite('admin/order/cancel', array('controller' => 'order', 'action' => 'cancel'));
		$router->addRoute('admin_order_cancel', $route);

        $route = new Yaf_Route_Rewrite('admin/news/add', array('controller' => 'news', 'action' => 'add'));
        $router->addRoute('admin_news_add', $route);

        $route = new Yaf_Route_Rewrite('admin/news/delete', array('controller' => 'news', 'action' => 'delete'));
        $router->addRoute('admin_news_delete', $route);

        $route = new Yaf_Route_Rewrite('admin/news/list', array('controller' => 'news', 'action' => 'list'));
        $router->addRoute('admin_news_list', $route);

	}
}
