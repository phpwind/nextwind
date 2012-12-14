<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * 
 */

return array(
	'web-apps' => array(
		'windidadmin' => array(
			'root-path' => 'APPS:admin', 
			'modules' => array(
				'pattern' => array(
					'controller-path' => 'APPS:windid.{m}.admin', 
					'template-path' => 'TPL:windid.{m}.admin', 
					'compile-path' => 'DATA:compile.template'), 
				'default' => array(
					'controller-path' => 'ADMIN:controller', 
					'controller-suffix' => 'Controller', 
					'error-handler' => 'ADMIN:controller.MessageController', 
					'template-path' => 'TPL:admin', 
					'compile-path' => 'DATA:compile.template.admin', 
					'theme-package' => 'THEMES:')
			)
		)
	)
);