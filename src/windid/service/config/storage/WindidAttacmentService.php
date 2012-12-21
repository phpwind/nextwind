<?php
/**
 * 附件服务
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidAttacmentService.php 22058 2012-12-19 02:27:25Z gao.wanggao $
 * @package config.service.srv
 */
class WindidAttacmentService {

	/**
	 * @var PwSimpleHook
	 */
	private $hook = null;
	
	public function __construct($hookKey) {
		$this->hook = PwSimpleHook::getInstance($hookKey);
	}
	
	/**
	 * 返回附件存储类型
	 *
	 * @return array
	 */
	public function getStorages() {
		$conf = Wind::getRealPath('WINDID:service.config.storage.storages.php', true);
		$tmp = array('name' => '', 'alias' => '', 'avatarmanagelink' => '', 'description' => '', 'components' => array());
		$storages = @include $conf;
		$storages = $this->hook->runWithFilters($storages);
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
		return true;
		$storages = $this->getStorages();
		if (!array_key_exists($storageType, $storages)) return new PwError('ADMIN:att.storage.type.not.exit');
		$storage = $storages[$storageType];
		if (!isset($storage['components']['path'])) return new PwError('ADMIN:att.storage.config.fail');
		/* @var $componentService PwComponentsService */
		$componentService = Wekit::load('hook.srv.PwComponentsService');
		$componentService->setComponent('storage', $storage['components'], $storage['description']);
		
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('attachment');
		$config->set('storage.type', $storageType)->flush();
		return true;
	}
}

?>