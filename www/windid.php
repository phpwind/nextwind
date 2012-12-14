<?php
error_reporting(E_ERROR | E_PARSE);
define('WIND_DEBUG', 1);
require '../src/wekit.php';
$components = array('router' => array());
Wekit::run('inform', $components);
?>