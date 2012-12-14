<?php
Wind::import('SRC:bootstrap.phpwindBoot');
Wind::import('ADMIN:service.srv.AdminUserService');
Wind::import('ADMIN:service.srv.userSource.AdminUserSourceDb');
Wind::import('ADMIN:service.srv.userSource.AdminUserSourceFounder');
Wind::import('ADMIN:service.bo.AdminUserBo');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: adminBoot.php 21804 2012-12-13 09:35:02Z yishuo $
 * @package wekit
 */
class adminBoot extends phpwindBoot {
	
	/* (non-PHPdoc)
	 * @see phpwind::runApps()
	 */
	public function runApps() {}
	
	/* (non-PHPdoc)
	 * @see phpwind::_initUser()
	 */
	protected function _initUser() {}

	protected function _getLoginUser() {
		if (!($userCookie = Pw::getCookie('AdminUser'))) {
			$password = '';
			$us = new AdminUserSourceDb(0);
		} else {
			list($type, $uid, $password) = explode("\t", Pw::decrypt($userCookie));
			if ($type == AdminUserService::FOUNDER) {
				$us = new AdminUserSourceFounder($uid);
			} else {
				$us = new AdminUserSourceDb($uid);
			}
		}
		Pw::setCookie('AdminUser', $userCookie, 1800);
		
		$user = new AdminUserBo($us);
		if (!$user->isExists() || Pw::getPwdCode($user->info['password']) != $password) {
			$user->reset();
		} else {
			unset($user->info['password']);
		}
		return $user;
	}
}