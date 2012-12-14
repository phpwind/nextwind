<?php
/**
 * 注册模块配置服务
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwModuleService.php 18618 2012-09-24 09:31:00Z jieyin $
 * @package appcenter.service
 */
class PwModuleService {
	
	private $modules = array();
	
	public function __construct() {
		$modules = (array) Wekit::C('site', 'modules');
		foreach ($modules as $p => $value) {
			foreach ($value as $k => $v) {
				$this->modules[$k][$p] = $v;
			}
		}
	}
	
	/**
	 * 获取某个应用的模块配置
	 *
	 * @param string $alias
	 * @return multitype:
	 */
	public function getModule($alias) {
		return $this->modules[$alias];
	}
	
	/**
	 * 注册某个应用的模块配置
	 *
	 * @param unknown_type $alias
	 * @param unknown_type $module
	 * @return Ambigous <boolean, number, rowCount>
	 */
	public function registeModule($alias, $module) {
		$this->modules[$alias] = $module;
		return $this->_configDs()->setConfig('site', 'modules', $this->_toConfig());
	}
	
	/**
	 * 删除某个应用的模块配置
	 *
	 * @param unknown_type $alias
	 * @return Ambigous <boolean, number, rowCount>
	 */
	public function deleteModule($alias) {
		unset($this->modules[$alias]);
		return $this->_configDs()->setConfig('site', 'modules', $this->_toConfig());
	}
	
	/**
	 * @return PwConfig
	 */
	private function _configDs() {
		return Wekit::load('config.PwConfig');
	}
	
	/**
	 * 转化入库格式
	 *
	 * @return array
	 */
	private function _toConfig() {
		$modules = array();
		foreach ($this->modules as $alias => $value) {
			foreach ($value as $k => $v) {
				$modules[$k][$alias] = $v;
			}
		}
		return $modules;
	}
}

?>