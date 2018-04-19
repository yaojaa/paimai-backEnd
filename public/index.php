<?php
define("ROOT_PATH",  realpath(dirname(__FILE__) . '/../'));
define("APP_PATH",  ROOT_PATH);
define("CONFIG_PATH",  ROOT_PATH."/conf");
ini_set("yaf.library", ROOT_PATH . "/library");
$app  = new Yaf_Application(ROOT_PATH . "/conf/application.ini");
$app->bootstrap()
	->run();
