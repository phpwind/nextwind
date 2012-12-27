<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidStoreService.php 22500 2012-12-25 03:54:47Z gao.wanggao $ 
 * @package 
 */

class WindidStoreService {
	
	public function getStore() {
		$ds = Windid::load('config.WindidConfig');
		$stores = $ds->getValues('storage');
		$config = $ds->getValues('attachment');
		$config = $config['storage.type'];
		if (!$config || !isset($stores[$config])) {
			$cls = 'WINDID:library.storage.WindidStorageLocal';
		} else {
			$store = unserialize($stores[$config]);
			$cls = $store['components']['path'];
		}
		$srv = Wind::import($cls);
		return new $srv();
		//$this->store = Wind::getComponent($this->bhv->isLocal ? 'windidLocalStorage' : 'windidStorage');
	}
	
	public function setStore($key, $storage) {
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('storage');
		$config->set($key, serialize($storage))->flush();
		return true;
	}
	
}
?>