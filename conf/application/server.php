<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 
 */

return array(
	'web-apps' => array(
		'server' => array(
			'root-path' => 'APPS:server',
			'modules' => array(
				'default' => array(
					'controller-path' => 'APPS:server.controller',
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:server',
					'compile-path' => 'DATA:compile.template.server',
				)
			)
		)
	)
);