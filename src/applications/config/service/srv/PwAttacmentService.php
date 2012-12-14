<?php
/**
 * 附件服务
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package config.service.srv
 */
class PwAttacmentService {
	
	/**
	 * 返回附件存储类型
	 *
	 * @return array
	 */
	public function getStorages() {
		$conf = Wind::getRealPath('APPS:config.conf.storages.php', true);
		$tmp = array('name' => '', 'alias' => '', 'managelink' => '', 'description' => '', 'components' => array());
		$storages = @include $conf;
		$storages = PwSimpleHook::getInstance('PwAttacmentService_getStorages')->runWithFilters($storages);
		foreach ($storages as $key => $value) {
			$storages[$key] = array_merge($tmp, $value);
		}
		return $storages;
	}

	/**
	 * 设置storage存储方案到系统
	 * 
	 * @param string $storageType
	 * @return true|pwError
	 */
	public function setStoragesComponents($storageType) {
		$storages = $this->getStorages();
		if (!array_key_exists($storageType, $storages)) return new PwError('ADMIN:att.storage.type.not.exit');
		$storage = $storages[$storageType];
		if (!isset($storage['components']['path'])) return new PwError('ADMIN:att.storage.config.fail');
		/* @var $componentService PwComponentsService */
		$componentService = Wekit::load('hook.srv.PwComponentsService');
		$componentService->setComponent('storage', $storage['components'], $storage['description']);
		
		$config = new PwConfigSet('attachment');
		$config->set('storage.type', $storageType)->flush();
		return true;
	}
}

?>