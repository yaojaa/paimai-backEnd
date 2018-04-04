<?php

define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));
ini_set("yaf.library", APP_PATH . "/library");
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()
	->run();
