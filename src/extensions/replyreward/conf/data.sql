--安装或更新时需要注册的sql写在这里--

CREATE TABLE `pw_app_replyreward` (
 `tid` int(10) unsigned NOT NULL,
 `credittype` varchar(30) NOT NULL DEFAULT '',
 `creditnum` int(10) unsigned NOT NULL DEFAULT '0',
 `rewardtimes` smallint(5) unsigned NOT NULL DEFAULT '0',
 `repeattimes` tinyint(3) unsigned NOT NULL DEFAULT '0',
 `chance` tinyint(3) unsigned NOT NULL DEFAULT '0',
 `lefttimes` smallint(5) unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`tid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


CREATE TABLE `pw_app_replyreward_record` (
 `id`  int(10) unsigned NOT NULL AUTO_INCREMENT,
 `tid` int(10) unsigned NOT NULL DEFAULT '0',
 `pid` int(10) unsigned NOT NULL DEFAULT '0',
 `uid` int(10) unsigned NOT NULL DEFAULT '0',
 `credittype` varchar(30) NOT NULL DEFAULT '',
 `creditnum` int(10) unsigned NOT NULL DEFAULT '0',
 `rewardtime` int(10) unsigned NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`),
 UNIQUE KEY `idx_tid_pid` (`tid`,`pid`),
 KEY `idx_uid` (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


