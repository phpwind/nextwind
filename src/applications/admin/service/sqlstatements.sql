-- TableName pw_admin_auth
-- Fields id 			主键,自动增长
-- Fields uid 			用户ID
-- Fields username 		用户名
-- Fields roles 		用户拥有的角色,序列存储
-- Fields created_time 	用户权限创建时间
-- Fields modified_time 	用户权限修改时间
-- Idx	  idx_uid		索引,根据用户id查找用户权限
DROP TABLE IF EXISTS pw_admin_auth;
CREATE TABLE pw_admin_auth (
id SMALLINT(6) NOT NULL AUTO_INCREMENT,
uid INT(10) NOT NULL DEFAULT '0',
username VARCHAR(15) NOT NULL DEFAULT '',
roles VARCHAR(255) NOT NULL DEFAULT '',
created_time INT(10) NOT NULL DEFAULT '0',
modified_time INT(10) NOT NULL DEFAULT '0',
PRIMARY KEY (id),
KEY idx_uid (uid)
)ENGINE=MYISAM;

-- TableName pw_admin_role
-- Fields id 			角色ID,自增主键索引
-- Fields name	 		角色名称,表主键
-- Fields auths			角色权限值序列
-- Fields created_time	创建角色时间
-- Fields modified_time    角色最后修改时间	  
DROP TABLE IF EXISTS pw_admin_role;
CREATE TABLE pw_admin_role (
id SMALLINT(6) NOT NULL AUTO_INCREMENT,
name VARCHAR(15) NOT NULL default '',
auths TEXT NOT NULL default '',
created_time INT(10) NOT NULL default '0',
modified_time INT(10) NOT NULL default '0',
PRIMARY KEY (id),
KEY idx_name (name)
)ENGINE=MYISAM;

-- TableName pw_admin_founder
-- Fields uid           用户ID
-- Fields username      用户名称
-- Fields createe_time    创建时间
DROP TABLE IF EXISTS pw_admin_founder;
CREATE TABLE pw_admin_founder (
uid INT(10) unsigned NOT NULL default '0',
username VARCHAR(15) NOT NULL default '',
created_time INT(10) NOT NULL default '0',
PRIMARY KEY (uid)
)ENGINE=MyISAM;