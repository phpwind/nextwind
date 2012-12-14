<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidConfigApi.php 21658 2012-12-12 07:00:12Z gao.wanggao $ 
 * @package 
 */

class WindidConfigApi {
	
	
	public function getConfig($name) {
		$params = array(
			'name'=>$name,
		);
		return WindidApi::open('config/get', $params);
	}
	
	/**
	 * 设置配置
	 * Enter description here ...
	 * @param string $spacename 命名空间
	 * @param array $keys 
	 */
	public function setConfig($spacename, $key, $value) {
		$params = array(
			'spacename'=>$spacename,
			'key'=>$key,
			'value'=>$value,
		);
		return WindidApi::open('config/set', array(), $params);
	}
	
	/**
	 * 删除配置项
	 * Enter description here ...
	 * @param string $spacename
	 * @param array|string $keys
	 */
	public function deleteConfig($spacename, $keys = '') {
		if (!is_array($keys)) $keys = array($keys);
		$params = array(
			'spacename'=>$spacename,
			'keys'=>implode('_', $keys),
		);
		return WindidApi::open('config/delete', array(), $params);
	}

}
?>