<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * windid
 */

return array(
	'web-apps' => array(
		'windid' => array(
			'root-path' => 'APPS:windid',
			'modules' => array(
				'default' => array(
					'controller-path' => 'APPS:windid.controller', 
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:windid', 
					'compile-path' => 'DATA:compile.template.windid',
				),
				'api' => array(
					'controller-path' => 'APPS:windid.api', 
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:windid.api', 
					'compile-path' => 'DATA:compile.template.windid.api',
				),
				'queue' => array(
					'controller-path' => 'APPS:windid.queue', 
					'controller-suffix' => 'Controller',
					'template-path' => 'TPL:windid.queue', 
					'compile-path' => 'DATA:compile.template.windid.queue',
				),
			),
		)
	)
);