<?php
Wind::import('WINDID:api.open.OpenBaseApi');
Wind::import('WINDID:api.local.WindidConfigApi');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ConfigApi.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */

class ConfigApi extends OpenBaseApi{
	
	
	public function getConfig() {
		return $this->getApi()->getConfig($this->getInput('name'));
	}
	
	/**
	 * 设置配置
	 * Enter description here ...
	 * @param string $spacename 命名空间
	 * @param array $keys 
	 */
	
	public function setConfig() {
		$spacename = $this->getInput('spacename');
		$key = $this->getInput('key');
		$value = $this->getInput('value');
		return $this->getApi()->setConfig($spacename, $key, $value);
	}
	
	/**
	 * 删除配置项
	 * Enter description here ...
	 * @param string $spacename
	 * @param array|string $keys
	 */
	public function deleteConfig($spacename, $keys = '') {
		$spacename = $this->getInput('spacename');
		$keys = explode('_',$this->getInput('key'));
		return $this->getApi()->deleteConfig($spacename, $keys);
	}
	
	protected function getApi() {
		return new WindidConfigApi();
	}
}
?>