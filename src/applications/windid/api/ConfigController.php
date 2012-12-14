<?php
Wind::import('APPS:windid.api.OpenBaseController');
Wind::import('WINDID:api.local.WindidConfigApi');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ConfigController.php 21823 2012-12-13 10:40:29Z gao.wanggao $ 
 * @package 
 */

class ConfigController extends OpenBaseController{
	
	public function getAction() {
		$result = $this->getApi()->getConfig($this->getInput('name', 'get'));
		$this->output($result);
	}
	
	/**
	 * 设置配置
	 * Enter description here ...
	 * @param string $spacename 命名空间
	 * @param array $keys 
	 */
	
	public function setAction() {
		$spacename = $this->getInput('spacename', 'post');
		$key = $this->getInput('key', 'post');
		$value = $this->getInput('value', 'post');
		$result = $this->getApi()->setConfig($spacename, $key, $value);
		$this->output($result);
	}
	
	/**
	 * 删除配置项
	 * Enter description here ...
	 * @param string $spacename
	 * @param array|string $keys
	 */
	public function deleteAction() {
		$spacename = $this->getInput('spacename', 'post');
		$keys = explode('_',$this->getInput('key', 'post'));
		$result = $this->getApi()->deleteConfig($spacename, $keys);
		$this->output($result);
	}
	
	protected function getApi() {
		return new WindidConfigApi();
	}
}
?>