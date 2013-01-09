<?php
error_reporting(0);
@header("Content-Type:text/html; charset=utf-8");
define('SCR', 'aCloud_index');
define('WIND_DEBUG', 0);
require_once ('../../src/wekit.php');
$front = Wind::application('acloud', WEKIT_PATH . 'aCloud/aCloudConfig.php');
// $front->createApplication();

Wekit::createapp('acloud');
$config = include WEKIT_PATH . '../conf/application/default.php';
Wekit::app()->charset = $config['web-apps']['default']['charset'];

require_once (WEKIT_PATH . 'aCloud/aCloud.php');
$router = new ACloudRouter();
$router->run();