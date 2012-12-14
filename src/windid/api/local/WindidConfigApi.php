<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidConfigApi.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package 
 */

class WindidConfigApi {
	
	
	public function getConfig($name) {
		$key = '';
		if (strpos($name, ':') !== false) {
			list($spacename, $key) = explode(':', $name);
		} else {
			$spacename = $name;
		}
		$config = $this->_getConfigDs()->getValues($spacename);
		return $key ? $config[$key] : $config;
	}
	
	/**
	 * 设置配置
	 * Enter description here ...
	 * @param string $spacename 命名空间
	 * @param array $keys 
	 */
	public function setConfig($spacename, $key, $value) {
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet($spacename);
		$config->set($key, $value)->flush();
		return WindidError::SUCCESS;
	}
	
	/**
	 * 删除配置项
	 * Enter description here ...
	 * @param string $spacename
	 * @param array|string $keys
	 */
	public function deleteConfig($spacename, $keys = '') {
		$ds =  $this->_getConfigDs();
		if ($keys) {
			$result = $ds->deleteConfig($namespace);
		} else {
			if (!is_array($keys)) $keys = array($keys);
			foreach ($keys AS $v) {
				$result = $ds->deleteConfigByName($namespace, $v);
			}
		}
		return (int)$result;
	}
	
	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
}
?>