<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * 后台安全 - ip限制
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: SafeController.php 18681 2012-09-26 05:48:48Z long.shi $
 * @package admin.controller
 */
class SafeController extends AdminBaseController {
	
	/*
	 * (non-PHPdoc) @see WindController::run()
	 */
	public function run() {
		$conf = Wekit::C('admin');
		$this->setOutput($conf, 'conf');
	}

	/**
	 * 保存设置
	 */
	public function doRunAction() {
		$bo = new PwConfigSet('admin');
		$ips = $this->getInput('ips', 'post');
		//list($question, $ips) = $this->getInput(array('question', 'ips'), 'post');
		$bo->set('ip.allow', $ips)->flush();
		//->set('question.isopen', $question)
		$this->showMessage('success');
	}
}

?>