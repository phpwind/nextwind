<?php
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('SRV:user.dm.PwUserInfoDm');
/**
 * 后台创始人管理相关操作类
 * 
 * 创始人管理相关操作<code>
 * 1. run 创始人管理首页
 * 2. add 添加创始人
 * 3. del 删除创始人
 * </code>
 * @author Qiong Wu <papa0924@gmail.com> 2011-11-10
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: FounderController.php 22109 2012-12-19 08:07:09Z long.shi $
 * @package admin
 * @subpackage library
 */
class FounderController extends AdminBaseController {
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$founder = $this->loadFounderService()->getFounders();
		$isWriteable = $this->loadFounderService()->isWriteable();
		$this->setOutput($isWriteable, 'is_writeable');
		$this->setOutput(array_keys($founder), 'list');
	}

	/**
	 * 添加创始人
	 */
	public function addAction() {
		$username = $this->getInput('username', 'post');
		if ($this->loadFounderService()->isFounder($username)) $this->showError(
			'ADMIN:founder.add.fail.username.duplicate');
		$args = array('username' => $username);
		$this->showMessage('success', 'founder/show?' . WindUrlHelper::argsToUrl($args));
	}

	/**
	 * 展示创始人添加页
	 */
	public function showAction() {
		$username = rawurldecode($this->getInput('username', 'get'));
		$user = $this->loadAdminUserService()->verifyUserByUsername($username);
		$this->setOutput($username, 'username');
		$this->setOutput(isset($user['email']) ? $user['email'] : '', 'email');
		$this->setOutput(isset($user['uid']) ? $user['uid'] : 0, 'uid');
		
		$this->setTemplate('founder_add');
	}

	/**
	 * 添加创始人
	 */
	public function doAddAction() {
		list($username, $password, $email) = $this->getInput(array('username', 'password', 'email'), 
			'post');
		$r = $this->loadFounderService()->add($username, $password, $email);
		if ($r instanceof PwError) $this->showError($r->getError());
		$this->showMessage('success', 'founder/run');
	}

	/**
	 * 编辑创始人
	 */
	public function editAction() {
		$username = $this->getInput('username', 'get');
		if (!$this->loadFounderService()->isFounder($username)) $this->showError(
			'ADMIN:founder.edit.fail');
		$user = $this->loadAdminUserService()->verifyUserByUsername($username);
		
		$this->setOutput($username, 'username');
		$this->setOutput(isset($user['email']) ? $user['email'] : '', 'email');
	}

	/**
	 * 修改创始人
	 */
	public function doEditAction() {
		list($username, $password, $email) = $this->getInput(array('username', 'password', 'email'), 
			'post');
		$r = $this->loadFounderService()->edit($username, $password, $email);
		if ($r instanceof PwError) $this->showError($r->getError());
		$this->showMessage('success', 'founder/run');
	}

	/**
	 * 删除创始人
	 */
	public function delAction() {
		$username = $this->getInput('username', 'get');
		if ($this->adminUser->username == $username) $this->showError('ADMIN:founder.del.fail.self');
		$result = $this->loadFounderService()->del($username);
		if ($result instanceof PwError) $this->showError($result->getError());
		$this->showMessage('success');
	}

	/**
	 * @return AdminUserService
	 */
	private function loadAdminUserService() {
		return Wekit::load('ADMIN:service.srv.AdminUserService');
	}

	/**
	 * @return AdminFounderService
	 */
	private function loadFounderService() {
		return Wekit::load('ADMIN:service.srv.AdminFounderService');
	}
}
?>