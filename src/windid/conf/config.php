<?php
/*
return array( 
	'windid' => 'local', 									//client:作为客户端    server.作为服务端   local.独立系统 
	'serverUrl' => 'http://www.phpwind.net',  	//服务端访问地址. 如:http://www.phpwind.net
	'clientId' => '0', 										//该客户端在WindID里的id
	'clientKey' => '',										//通信密钥，请保持与WindID里的一致
	'clientDb' => 'mysql', 									//mysql为本地连接  http远程连接  如为mysql，请同时配置database.php里的数据库设置
	'clientCharser' => 'utf8',								//客户端使用的字符编码
);*/
return include  WINDID_PATH.'/../../conf/windidconfig.php';

?>