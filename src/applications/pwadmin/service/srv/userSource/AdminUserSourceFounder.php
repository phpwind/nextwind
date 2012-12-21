<?php

Wind::import('APPS:pwadmin.service.srv.userSource.AdminUserSourceInterface');

/**
 * 后台用户的业务对象
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AdminUserSourceFounder.php 22151 2012-12-19 09:38:12Z yishuo $
 * @package src.service.user.bo
 */
class AdminUserSourceFounder implements AdminUserSourceInterface {
	public $username;
	public $info = array();

	public function __construct($username) {
		$this->username = $username;
		$founder = Wekit::load('ADMIN:service.srv.AdminFounderService')->getFounders();
		if (isset($founder[$username])) {
			list($md5pwd) = explode('|', $founder[$username], 2);
			$user = Wekit::load('user.PwUser')->getUserByName($username);
			$this->info = array(
				'uid' => $user ? $user['uid'] : 0, 
				'username' => $username, 
				'email' => $user['email'] ? $user['email'] : '', 
				'groupid' => 3, 
				'memberid' => 0, 
				'groups' => $user['groups'] ? $user['groups'] : '', 
				'password' => $md5pwd);
		}
	}

	public function getInfo() {
		return $this->info;
	}
}
?>