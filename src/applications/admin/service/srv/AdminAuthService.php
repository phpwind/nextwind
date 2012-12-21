<?php
/**
 * 后台用户管理服务
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-11-21
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AdminAuthService.php 22325 2012-12-21 08:36:29Z yishuo $
 * @package admin
 * @subpackage service.srv
 */
class AdminAuthService {

	/**
	 * 删除后台用户
	 *
	 * @param int $id
	 * @return int|PwError
	 */
	public function del($id) {
		/* @var $authDs AdminAuth */
		$authDs = Wekit::load('ADMIN:service.AdminAuth');
		$info = $authDs->findById($id);
		if (!$info) return new PwError('ADMIN:auth.del.fail');
		$this->getAdminUserService()->loadUserService()->updateUserStatus($info['uid'], false);
		return $authDs->del($id);
	}

	/**
	 * 添加用户角色定义
	 *
	 * @param string $username
	 * @param array $roles
	 * @return array|PwError
	 */
	public function add($username, $roles) {
		if (empty($username)) return new PwError('ADMIN:auth.add.fail.username.empty');
		if (empty($roles)) return new PwError('ADMIN:auth.add.fail.role.empty');
		
		$userInfo = $this->getAdminUserService()->verifyUserByUsername($username);
		if (!$userInfo) return new PwError('ADMIN:auth.add.fail.username.exist');
		
		/* @var $authService AdminAuth */
		$authService = Wekit::load('ADMIN:service.AdminAuth');
		$result = $authService->add($username, $userInfo['uid'], $roles);
		if ($result instanceof PwError) return $result;
		$this->getAdminUserService()->loadUserService()->updateUserStatus($userInfo['uid'], true);
		return $result;
	}

	/**
	 * @return AdminUserService
	 */
	private function getAdminUserService() {
		return Wekit::load('ADMIN:service.srv.AdminUserService');
	}

	/**
	 * @return AdminAuthDao
	 */
	private function getAdminAuthDao() {
		return Wekit::loadDao('ADMIN:service.dao.AdminAuthDao');
	}
}

?>