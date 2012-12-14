<?php
/**
 * 实名认证配置类扩展
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyConfigDo {
	
	public function getAdminMenu($config) {
		$config += array(
			'app_verify' => array('实名认证', 'app/manage/*?app=verify', '', '', 'appcenter'),
			);
		return $config;
	}
	
	public function getProfileMenu($config) {
		$config['profile_left'] += array(
			'verify' => array('title' => '认证', /*'url' => 'app/verify/index/run'*/),
		);
		return $config;
	}
}

?>