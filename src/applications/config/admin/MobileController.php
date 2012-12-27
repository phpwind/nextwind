<?php
Wind::import('ADMIN:library.AdminBaseController');

/**
 * 后台设置 - 手机验证
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class MobileController extends AdminBaseController {

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function setAction() {
		$registerConfig = $this->_getConfig()->getValues('register');
		$loginConfig = $this->_getConfig()->getValues('login');
		$mobileConfig = $this->_getConfig()->getValues('mobile');
		if (!$mobileConfig['plat.type']) {
			$this->showError('USER:mobile.plat.choose.error', 'config/mobile/run', true);
		}
		$mobileService = Wekit::load('SRV:mobile.srv.PwMobileService');
		$restMessage = $mobileService->getRestMobileMessage();
		if ($restMessage instanceof PwError) {
			$this->showError($restMessage->getError());
		}
		$appMobileUrl = $mobileService->platUrl;
		$this->setOutput($appMobileUrl, 'appMobileUrl');
		$this->setOutput($restMessage, 'restMessage');
		$this->setOutput($registerConfig, 'registerConfig');
		$this->setOutput($loginConfig, 'loginConfig');
	}

	/**
	 * 后台设置-手机设置
	 */
	public function dosetAction() {
		$config = new PwConfigSet('register');
		$config->set('active.phone', $this->getInput('activePhone', 'post'))
				->set('mobile.message.content', $this->getInput('mobileMessageContent', 'post'))
				->flush();
		$loginConfig = $this->_getConfig()->getValues('login');
		$ways = $this->getInput('ways', 'post');
		$loginConfigWays = array_flip($loginConfig['ways']);
		unset($loginConfigWays[4]);
		$loginConfigWays = array_flip($loginConfigWays);
		$ways && $loginConfigWays[] = 4;
		$config = new PwConfigSet('login');
		$config->set('ways', $loginConfigWays);
		$config->set('mobieFindPasswd', $this->getInput('mobieFindPasswd', 'post'))
			->flush();
		
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 后台设置-短信平台
	 */
	public function run() {
		Wind::import('SRV:mobile.srv.PwMobileConfigService');
		$service = new PwMobileConfigService('PwMobileService_getPlats');
		$plats = $service->getPlats();
		
		$config = $this->_getConfig()->getValues('mobile');
		$platType = 'aliyun';
		if (isset($config['plat.type']) && isset($plats[$config['plat.type']])) {
			$paltType = $config['plat.type'];
		}
		$this->setOutput($plats, 'plats');
		$this->setOutput($paltType, 'paltType');
	}
	
	/**
	 * 附件存储方式设置列表页
	 */
	public function dorunAction() {
		$mobile_plat = $this->getInput('mobile_plat', 'post');
		/* @var $attService PwAttacmentService */
		Wind::import('SRV:mobile.srv.PwMobileConfigService');
		$service = new PwMobileConfigService('PwMobileService_getPlats');
		$_r = $service->setPlatComponents($mobile_plat);
		
		if ($_r === true) $this->showMessage('ADMIN:success');
		/* @var $_r PwError  */
		$this->showError($_r->getError());
	}
	
	/**
	 * 加载Config DS 服务
	 * 
	 * @return PwConfig
	 */
	private function _getConfig() {
		return Wekit::load('config.PwConfig');
	}
}
