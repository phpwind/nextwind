<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 全局产品级应用	配置
*/
return array(
	//'isclosed' => '1',

	'components' => array('resource' => 'CONF:components.php'),
	'web-apps' => array(
		'default' => array(
			'charset' => 'utf-8'
		)	
	)
);