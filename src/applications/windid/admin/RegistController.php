<?php
Wind::import('APPS:windid.admin.WindidBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: RegistController.php 22007 2012-12-18 06:23:13Z gao.wanggao $ 
 * @package 
 */
class RegistController extends WindidBaseController { 
	
	public function run() {
		$service = $this->_getConfigDs();
		$config = $service->getValues('reg');
		//$config['security.ban.username'] = implode(',', $config['security.ban.username']);
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
			->set('security.ban.username', trim($this->getInput('securityBanUsername', 'post')))
		->flush();
		$this->showMessage('WINDID:success');
	}
	
	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
}
?>