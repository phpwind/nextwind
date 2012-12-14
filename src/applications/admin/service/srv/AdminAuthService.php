<?php
/**
 * 后台用户管理服务
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-11-21
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AdminAuthService.php 21138 2012-11-29 03:07:29Z xiaoxia.xuxx $
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
		
		//将对应的用户状态更新：去除后台标志
		/* @var $userDs PwUser */
		$userDs = Wekit::load('SRV:user.PwUser');
		$user = $userDs->getUserByUid($info['uid'], PwUser::FETCH_MAIN);
		if ($user && Pw::getstatus($user['status'], PwUser::STATUS_ALLOW_LOGIN_ADMIN)) {
			Wind::import('SRV:user.dm.PwUserInfoDm');
			$dm = new PwUserInfoDm();
			$dm->setUid($info['uid'])->setAllowLoginAdmin(false);
			$userDs->editUser($dm, PwUser::FETCH_MAIN);
		}
		
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
		
		$userInfo = $this->getAdminUserService()->verifyUserByUsername($username);
		if (!$userInfo) return new PwError('ADMIN:auth.add.fail.username.exist');
		
		/* @var $authService AdminAuth */
		$authService = Wekit::load('ADMIN:service.AdminAuth');
		$result = $authService->add($username, $userInfo['uid'], $roles);
		
		//更新该用户状态：添加后台人员标志
		if ((!$result instanceof PwError) && !Pw::getstatus($userInfo['status'], PwUser::STATUS_ALLOW_LOGIN_ADMIN)) {
			Wind::import('SRV:user.dm.PwUserInfoDm');
			$dm = new PwUserInfoDm();
			$dm->setUid($userInfo['uid'])->setAllowLoginAdmin(true);
			/* @var $userDs PwUser */
			$userDs = Wekit::load('SRV:user.PwUser');
			$userDs->editUser($dm, PwUser::FETCH_MAIN);
		}
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