<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('APPS:server.admin.WindidBaseController');

/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: IndexController.php 21642 2012-12-12 04:56:39Z gao.wanggao $ 
 * @package 
 */
class IndexController extends WindidBaseController {
	
	public function run() {
		$config = $this->_getConfigDs()->getValues('site');
		$this->setOutput($config, 'config');
	}
	
	public function dorunAction() {
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('site');
		$config->set('timezone', intval($this->getInput('timeTimezone', 'post')))
			->set('timecv', intval($this->getInput('timecv', 'post')))->flush();
		$this->showMessage('ADMIN:success');
	}
	
	public function avatarAction() {
		
	}
	
	/**
	 * 附件存储方式设置列表页
	 */
	public function storageAction() {
		Wind::import('WINDID:service.config.storage.WindidAttacmentService');
		$attService = new WindidAttacmentService('PwAttacmentService_getStorages');
		$storages = $attService->getStorages();
		$service = $this->_getConfigDs();
		$config = $service->getValues('attachment');
		$storageType = 'local';
		if (isset($config['storage.type']) && isset($storages[$config['storage.type']])) {
			$storageType = $config['storage.type'];
		}
		$this->setOutput($config, 'config');
		$this->setOutput($storages, 'storages');
		$this->setOutput($storageType, 'storageType');
	}

	/**
	 * 附件存储方式设置列表页
	 */
	public function dostroageAction() {
		$att_storage = $this->getInput('att_storage', 'post');
		$avatarurl = $this->getInput('avatarurl', 'post');
		
		Wind::import('WINDID:service.config.storage.WindidAttacmentService');
		$attService = new WindidAttacmentService('PwAttacmentService_getStorages');
		$_r = $attService->setStoragesComponents($att_storage);
		
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('attachment');
		$config->set('avatarurl', $this->getInput('avatarurl', 'post'))->flush();
		
		if ($_r === true) $this->showMessage('ADMIN:success');
		/* @var $_r PwError  */
		$this->showError($_r->getError());
	}

	/**
	 * 后台设置-ftp设置
	 */
	public function ftpAction() {
		$service = $this->_getConfigDs();
		$config = $service->getValues('attachment');
		$this->setOutput($config, 'config');
	}

	/**
	 * 后台设置-ftp设置
	 */
	public function doftpAction() {
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('attachment');
		$config->set('ftp.url', $this->getInput('ftpUrl', 'post'))
			->set('ftp.server', $this->getInput('ftpServer', 'post'))
			->set('ftp.port', $this->getInput('ftpPort', 'post'))
			->set('ftp.dir', $this->getInput('ftpDir', 'post'))
			->set('ftp.user', $this->getInput('ftpUser', 'post'))
			->set('ftp.pwd', $this->getInput('ftpPwd', 'post'))
			->set('ftp.timeout', abs(intval($this->getInput('ftpTimeout', 'post'))))
			->flush();
		$this->showMessage('ADMIN:success');
	}
	
	public function registAction() {
		$service = $this->_getConfigDs();
		$config = $service->getValues('reg');
		$config['security.ban.username'] = implode(',', $config['security.ban.username']);
		$this->setOutput($config, 'config');
	}
	
	public function doregistAction() {
		$username_max = abs($this->getInput('securityUsernameMax', 'post'));
		$username_min = abs($this->getInput('securityUsernameMin', 'post'));
		$username_max = max(array($username_max, $username_min));
		$username_max > 15 && $username_max = 15;
		$username_min = min(array($username_max, $username_min));
		$username_min < 1 && $username_min = 1;
		$password_max = abs($this->getInput('securityPasswordMax', 'post'));
		$password_min = abs($this->getInput('securityPasswordMin', 'post'));
		$password_max = max(array($password_max, $password_min));
		$password_min = min(array($password_max, $password_min));
		$password_min < 1 && $password_min = 1;
		$password_security = $this->getInput('securityPassword', 'post');
		
		$ipTime = ceil($this->getInput('securityIp', 'post'));
		if ($ipTime < 0) $ipTime = 1;
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('reg');
		$config->set('security.username.max', $username_max)
			->set('security.username.min', $username_min)
			->set('security.password', $password_security)
			->set('security.password.max', $password_max)
			->set('security.password.min', $password_min)
			->set('security.ban.username', explode(',', trim($this->getInput('securityBanUsername', 'post'))))
		->flush();
		$this->showMessage('WINDID:success');
	}
	
	public function creditAction() {
		$service = $this->_getConfigDs();
		$config = $service->getValues('credit');
		$this->setOutput($config['credits'], 'credits');	
	}
	
	public function docreditAction() {
		$credits = $this->getInput('credits', 'post');
		$newcredits = $this->getInput('newcredits', 'post');
		Wind::import('WINDID:service.config.srv.WindidCreditSetService');
		$srv = new WindidCreditSetService();
		$srv->setCredits($credits, $newcredits);
		$this->showMessage('WINDID:success');
	}
	
	public function doDeletecreditAction() {
		$creditId = (int) $this->getInput("creditId");
		if ($creditId < 5) $this->showError('WINDID:fail');
		Wind::import('WINDID:service.config.srv.WindidCreditSetService');
		
		$srv = new WindidCreditSetService();
		if ((!$srv->deleteCredit($creditId))) {
			$this->showError('WINDID:fail');
		}
		$this->showMessage('WINDID:success');
	}
	
	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
	
}
?>