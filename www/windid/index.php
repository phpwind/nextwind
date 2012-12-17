<?php
error_reporting(E_ERROR | E_PARSE);
define('WIND_DEBUG', 1);
//define('WINDID_PATH', dirname(__FILE__));
require '../../src/wekit.php';

$components = array('router' => array());
Wekit::run('windid', $components);
?>