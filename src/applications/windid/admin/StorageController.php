<?php
Wind::import('APPS:windid.admin.WindidBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: StorageController.php 22020 2012-12-18 07:13:04Z gao.wanggao $ 
 * @package 
 */
class StorageController extends WindidBaseController { 
/**
	 * 附件存储方式设置列表页
	 */
	public function run() {
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
		
		/*Wind::import('WINDID:service.config.storage.WindidAttacmentService');
		$attService = new WindidAttacmentService('PwAttacmentService_getStorages');
		$_r = $attService->setStoragesComponents($att_storage);*/
		
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('attachment');
		$config->set('avatarurl', $avatarurl)->set('storage.type', $att_storage)->flush();
		$this->showMessage('WINDID:success');
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
		$this->showMessage('WINDID:success');
	}
	
	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
}
?>