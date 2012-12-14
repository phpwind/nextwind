<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

require '../src/wekit.php';
//TODO 时间戳设定
define('WEKIT_TIMESTAMP', time());

/* @var $application WindWebFrontController */
Wind::application('install', 
	WindUtility::mergeArray(include WEKIT_PATH . '../conf/application/default.php', 
		include WEKIT_PATH . '../conf/application/install.php'))->run();
