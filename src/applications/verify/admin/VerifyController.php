<?php
Wind::import('ADMIN:library.AdminBaseController');

/**
 * 后台设置-验证机制配置
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: VerifyController.php 22570 2012-12-25 09:25:59Z gao.wanggao $
 * @package 
 */
class VerifyController extends AdminBaseController {
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		Wind::import('SRV:verify.srv.PwVerifyService');
		$srv =  new PwVerifyService('PwVerifyService_getVerifyType');
		$verifyType = $srv->getVerifyType();
		$service = $this->_loadConfigService();
		$config = $service->getValues('verify');
		$this->setOutput($config, 'config');
		$this->setOutput($verifyType, 'verifyType');
	}

	/**
	 * 配置增加表单处理器
	 *
	 * @return void
	 */
	public function dorunAction() {
		$questions = $this->getInput('contentQuestions', 'post');
		$_questions = array();
		foreach ($questions as $key => $value) {
			if (empty($value['ask']) && empty($value['answer'])) continue;
			if ($value['ask'] && empty($value['answer'])) $this->showError('ADMIN:verify.answer.empty');
			$_questions[] = $value;
		
		}
		$type = $this->getInput('type', 'post');
		if ($type == 'flash') {
			if (!class_exists('SWFBitmap')) $this->showError('ADMIN:verify.flash.not.allow');
		}
		$config = new PwConfigSet('verify');
		$config->set('type', $this->getInput('type', 'post'))
			->set('randtype', $this->getInput('randtype', 'post'))
			->set('content.type', $this->getInput('contentType', 'post'))
			->set('content.length', $this->getInput('contentLength', 'post'))
			->set('content.questions', $_questions)
			->set('width', 240)
			->set('height', 60)
			->set('content.showanswer', $this->getInput('contentShowanswer', 'post'))
			->set('voice', $this->getInput('voice', 'post'))
			->flush();
		$this->showMessage('ADMIN:success');
	}
	
	/**
	 * 站点设置
	 *
	 * @return void
	 */
	public function setAction() {
		$service = $this->_loadConfigService();
		$config = $service->getValues('verify');
		$this->setOutput($config, 'config');
	}
			
	/**
	 * 全局配置增加表单处理器
	 *
	 * @return void
	 */
	public function dosetAction() {
		$config = new PwConfigSet('verify');
		$verify = $this->getInput('showverify', 'post');
		$config->set('showverify', $verify ? $verify : array())->flush();	
		$this->showMessage('ADMIN:success');
	
	}

	private function _loadConfigService() {
		 return Wekit::load('config.PwConfig');
	}
}