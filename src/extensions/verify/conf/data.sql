DROP TABLE IF EXISTS `pw_appverify_verify`;
CREATE TABLE IF NOT EXISTS `pw_appverify_verify` (
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '认证类型',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '认证时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户认证表';

DROP TABLE IF EXISTS `pw_appverify_verify_check`;
CREATE TABLE `pw_appverify_verify_check` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户uid',
  `username` char(15) NOT NULL DEFAULT '' COMMENT '用户名',
  `type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '认证类型',
  `data` text COMMENT '认证审核数据',
  `created_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '提交审核时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_uid_type` (`uid`,`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户认证审核表';