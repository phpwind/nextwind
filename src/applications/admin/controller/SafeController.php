<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * 后台安全 - ip限制
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: SafeController.php 22315 2012-12-21 08:05:38Z yishuo $
 * @package admin.controller
 */
class SafeController extends AdminBaseController {
	
	/*
	 * (non-PHPdoc) @see WindController::run()
	 */
	public function run() {
		$ips = $this->_loadSafeService()->getAllowIps();
		$ips = implode(',', $ips);
		$this->setOutput($ips, 'ips');
		$this->setOutput(Wekit::app()->clientIp, 'clientIp');
	}

	/**
	 * 保存设置
	 */
	public function addAction() {
		$ips = $this->getInput('ips', 'post');
		$r = $this->_loadSafeService()->setAllowIps($ips);
		if ($r instanceof PwError) $this->showError($r->getError());
		$this->showMessage('success');
	}

	/**
	 * @return AdminSafeService
	 */
	private function _loadSafeService() {
		return Wekit::load('ADMIN:service.srv.AdminSafeService');
	}
}

?>