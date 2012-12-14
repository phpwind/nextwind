<?php

Wind::import('ADMIN:service.srv.userSource.AdminUserSourceInterface');

/**
 * 后台用户的业务对象
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AdminUserSourceDb.php 21549 2012-12-11 07:02:25Z jieyin $
 * @package src.service.user.bo
 */

class AdminUserSourceDb implements AdminUserSourceInterface {
	
	public $username;
	public $info = array();

	public function __construct($uid) {
		$this->info = Wekit::load('user.PwUser')->getUserByUid($uid);
	}

	public function getInfo() {
		return $this->info;
	}
}
?>