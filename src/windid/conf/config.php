<?php
/*
return array( 
	'windid' => '2', //client:作为客户端    server.作为服务端   local.独立系统 
	'serverUrl' => 'http://localhost/nextwindsvn/www/',  //服务端访问地址. 如:http://www.phpwind.net/  以"/"结尾
	'clientId' => '0', //
	'clientKey' => '',
	'clientDb' => 'msyql', //mysql为本地连接  http远程连接
	'clientCharser' => 'utf8',
);*/
return include  WINDID_PATH.'../../conf/windidConfig.php';

?>