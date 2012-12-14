<?php

/**
 * 首页
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: IndexController.php 18548 2012-09-19 07:59:26Z xiaoxia.xuxx $
 * @package 
 */
class IndexController extends PwBaseController {

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$this->forwardRedirect(WindUrlHelper::createUrl('bbs/index/run'));
		//echo 'welcome back: ' . $this->loginUser->username;
		//exit();
	}
	
	/**
	 * 全局积分变动，请求及清理
	 */
	public function showcreditAction() {
		if (!$this->loginUser->isExists()) {
			$this->showError("login.not");
		}
		$log = $this->loginUser->info['last_credit_affect_log'];
		if ($log) {
			Wind::import('SRV:user.dm.PwUserInfoDm');
			$dm = new PwUserInfoDm();
			$dm->setUid($this->loginUser->uid)->setLastCreditAffectLog('');
			/* @var $userDs PwUser */
			$userDs = Wekit::load('user.PwUser');
			$userDs->editUser($dm, PwUser::FETCH_DATA);
			$_log = unserialize($log);
			$log = array('name' => $_log[0], 'credit' => $_log[1]);
		}
		$this->setOutput($log, 'data');
		$this->showMessage();
	}
}