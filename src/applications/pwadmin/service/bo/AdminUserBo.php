<?php
Wind::import('ADMIN:service.bo.IAdminUserBo');
/**
 * 后台用户的业务对象
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AdminUserBo.php 21850 2012-12-14 03:28:45Z long.shi $
 * @package src.service.user.bo
 */
class AdminUserBo extends PwUserBo implements IAdminUserBo {

	public function __construct(AdminUserSourceInterface $us) {
		$this->info = $us->getInfo();
		if ($this->info) {
			$this->uid = $this->info['uid'];
			$this->username = $this->info['username'];
			$this->gid = ($this->info['groupid'] == 0) ? $this->info['memberid'] : $this->info['groupid'];
			$this->ip = $this->info['lastloginip'];
			if ($this->info['groups']) $this->groups = explode(',', $this->info['groups']);
			$this->groups[] = $this->gid;
		} else {
			$this->reset();
		}
	}

	public function isExists() {
		return $this->gid != 2;
	}

	public function getUsername() {
		return $this->username;
	}

	public function getUid() {
		return $this->uid;
	}
}

?>