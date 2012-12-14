--安装或更新时需要注册的sql写在这里--
CREATE TABLE `pw_app_encryptposts` (
	`tid` int unsigned NOT NULL, 
	`token` varchar(30) NOT NULL,  
	PRIMARY KEY (`tid`)
 ) ENGINE=MyISAM DEFAULT CHARSET=utf8;