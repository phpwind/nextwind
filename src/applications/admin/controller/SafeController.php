<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * 后台安全 - ip限制
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: SafeController.php 23697 2013-01-15 05:17:30Z jieyin $
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
		$this->setOutput(Wind::getComponent('request')->getClientIp(), 'clientIp');
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