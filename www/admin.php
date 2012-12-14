<?php
error_reporting(E_ERROR | E_PARSE);
/* 二进制:十进制  模式描述
 * 000: 0 关闭
 * 001: 1 window
 * 010: 2 log
 * 011: 3 window|log
 * */
define('WIND_DEBUG', 1);
require '../src/wekit.php';

$components = array('router' => array());
Wekit::run('pwadmin', $components);