<?php 
defined('WEKIT_VERSION') or exit(403);
/**
 * 全局配置
 */
return array(

/**=====配置开始于此=====**/

'dbcache' => '0',				//开启数据库数据缓存，当开启mem(或redis)时，开启此项，将使用mem(或redis)缓存数据库数据
'distributed' => '0',			//是否使用分布式架构，当开启此项时，将仅使用支持分布式的缓存策略

/*-----通用缓存开启-----*/

'mem.isopen' => 0,				//开启memcache缓存，请确保服务器上已安装 memcache 服务，并已作好相应配置
'mem.server' => 'MemCache',		//memcache服务名，有MemCache和MemCached两种，看当前php扩展安装的是哪个
'mem.servers' => array(
	'default' => array(
		array(
			'host' => 'localhost',
			'port' => 11211,
			'pconn' => false,
			'weight' => 1,
			'timeout' => 15,
			'retry' => 15,
			'status' => true,
			'fcallback' => null,
		),
	),
),
'mem.key.prefix' => 'pw',


'redis.isopen' => 0,			//开启redis缓存，请确保服务器上已安装 redis 服务，并已作好相应配置
'redis.servers' => array(
	'default' => array(
		array(
			'host' => '10.12.83.10',
			'port' => 6379,
			'pconn' => false,
			'timeout' => 0,
		),
	),
),
'redis.key.prefix' => 'pw',

'apc.isopen' => 0,				//开启apc缓存，请确保服务器上已安装 apc 服务


/*-----预设缓存键值-----*/

'precache' => array(
	'default/index/run' => array(
		array('hot_tags', array(0, 10)), 'medal_auto', 'medal_all'
	),
	'bbs/index/run' => array(
		array('hot_tags', array(0, 10)), 'medal_auto', 'medal_all'
	),
	'bbs/forum/run' => array(
		array('hot_tags', array(0, 10))
	),
	'bbs/cate/run' => array(
		array('hot_tags', array(0, 10))
	),
	'bbs/thread/run' => array(
		array('hot_tags', array(0, 10)), 'medal_auto', 'medal_all'
	),
	'bbs/read/run' => array('level', 'group_right', 'medal_all'),
),


/*-----预设钩子键值-----*/

'prehook' => array(
	'ALL' => array('s_header_nav', 's_footer'),
	'LOGIN' => array('s_header_info_1', 's_header_info_2'),
	'UNLOGIN' => array(),

	'default/index/run' => array('c_index_run', 'm_PwThreadList'),
	'bbs/index/run' => array('c_index_run', 'm_PwThreadList'),
	'bbs/cate/run' => array('c_cate_run', 'm_PwThreadList'),
	'bbs/thread/run' => array('c_thread_run', 'm_PwThreadList', 's_PwThreadType'),
	'bbs/read/run' => array('c_read_run', 'm_PwThreadDisplay', 's_PwThreadType', 's_PwUbbCode_convert', 's_PwThreadsHitsDao_add'),
	'bbs/post/doadd' => array('c_post_doadd', 'm_PwTopicPost', 's_PwThreadsDao_add', 's_PwThreadsIndexDao_add', 's_PwThreadsCateIndexDao_add', 's_PwThreadsContentDao_add', 's_PwForumStatisticsDao_update', 's_PwForumStatisticsDao_batchUpdate', 's_PwTagRecordDao_add', 's_PwTagRelationDao_add', 's_PwTagDao_update', 's_PwTagDao_add', 's_PwThreadsContentDao_update', 's_PwFreshDao_add', 's_PwUserDataDao_update', 's_PwUser_update', 's_PwAttachDao_update', 's_PwThreadAttachDao_update', 's_PwCreditOperationConfig'),
	'bbs/post/doreply' => array('c_post_doreply', 'm_PwReplyPost', 's_PwPostsDao_add', 's_PwForumStatisticsDao_update', 's_PwForumStatisticsDao_batchUpdate', 's_PwThreadsDao_update', 's_PwThreadsIndexDao_update', 's_PwThreadsCateIndexDao_update', 's_PwThreadsDigestIndexDao_update', 's_PwUserDataDao_update', 's_PwUser_update', 's_PwCreditOperationConfig'),
	'u/login/dorun' => array('c_login_dorun', 's_PwUserDataDao_update', 's_PwUser_update'),
	'u/login/welcome' => array('s_PwUserDataDao_update', 's_PwUser_update', 'm_login_welcome', 's_PwCronDao_update'),
),

/**=====配置结束于此=====**/
);