<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * 后台菜单管理操作类
 * 
 * 后台菜单管理操作类<code>
 * 1. run 后台权限入口
 * </code>
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-21
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AuthController.php 14889 2012-07-27 08:08:42Z xiaoxia.xuxx $
 * @package admin
 * @subpackage controller
 */
class AuthController extends AdminBaseController {
	private $perpage = 10;

	/**
	 * 菜单管理主入口
	 * 
	 * @return void
	 */
	public function run() {
		list($page) = $this->getInput(array('page'), 'get');
		list($count, $list, $page) = $this->_loadAuthService()->findByPage($page, $this->perpage);
		
		$this->setOutput($list, 'list');
		$this->setOutput($page, 'page');
		$this->setOutput($count, 'count');
		$this->setOutput($this->perpage, 'per');
	}

	/**
	 * 删除后台用户操作
	 * 
	 * @return void
	 */
	public function delAction() {
		/* @var $service AdminAuthService */
		$service = Wekit::load('ADMIN:service.srv.AdminAuthService');
		$result = $service->del($this->getInput('id', 'get'));
		if ($result instanceof PwError) $this->showError($result->getError());
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 展示编辑用户操作界面
	 * 
	 * @return void
	 */
	public function editAction() {
		$id = $this->getInput('id', 'get');
		if (!$id) $this->showError('ADMIN:auth.edit.fail.id.illegal');
		$user = $this->_loadAuthService()->findById($id);
		if ($user instanceof PwError) $this->showError('ADMIN:auth.edit.fail.user.exist');
		$roles = Wekit::load('ADMIN:service.AdminRole')->findRoles();
		$_tmp = array();
		foreach ($roles as $role) {
			if (strpos(',' . $user['roles'] . ',', ',' . $role['name'] . ',') === false) continue;
			$_tmp[] = $role;
		}
		$user['roles'] = $_tmp;
		$this->setOutput($user, 'user');
		$this->setOutput($roles, 'roles');
	}

	/**
	 * 编辑用户
	 * 
	 * @return void
	 */
	public function doEditAction() {
		list($id, $roles) = $this->getInput(array('id', 'userRoles'), 'post');
		$result = $this->_loadAuthService()->edit($id, $roles);
		if ($result instanceof PwError) $this->showError($result->getError());
		$this->showMessage('ADMIN:auth.edit.success');
	}

	/**
	 * 搜索用户操作
	 * 
	 * @return void
	 */
	public function addAction() {
		/* @var $role AdminRole */
		$role = Wekit::load('ADMIN:service.AdminRole');
		$roles = $role->findRoles();
		$this->setOutput($roles, 'roles');
	}

	/**
	 * 添加用户权限
	 * 
	 * @return void
	 */
	public function doAddAction() {
		list($username, $roles) = $this->getInput(array('username', 'userRoles'), 'post');
		/* @var $service AdminAuthService */
		$service = Wekit::load('ADMIN:service.srv.AdminAuthService');
		$result = $service->add($username, $roles);
		if ($result instanceof PwError) $this->showError($result->getError());
		
		$this->setOutput($result, 'data');
		$this->showMessage('ADMIN:auth.add.success');
	}

	/**
	 * @return AdminAuth
	 */
	private function _loadAuthService() {
		return Wekit::load('ADMIN:service.AdminAuth');
	}

}

?>