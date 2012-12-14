<?php
defined('WEKIT_VERSION') or exit(403);

return array(
	'web-apps' => array(
		'inform' => array(
			'root-path' => 'APPS:inform',
			'modules' => array(
				'default' => array(
					'controller-path' => 'APPS:inform.controller',
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:inform',
					'compile-path' => 'DATA:compile.template.inform',
				)
			)
		)
	)
);