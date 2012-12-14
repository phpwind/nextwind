<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 配置管理
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwConfigBo.php 18620 2012-09-24 10:01:58Z xiaoxia.xuxx $
 * @package src
 * @subpackage service.config.bo
 */
class PwConfigBo {
	
	public function __construct($config) {
		if (!$config) return null;
		foreach ($config as $key => $value) {
			$this->$key = new PwConfigIniBo($value);
		}
	}

	public function __get($name) {
		$data = Wekit::load('config.PwConfig')->getValues($name);
		$config = new PwConfigIniBo($data);
		$this->$name = $config;
		return $config;
	}
}

class PwConfigIniBo {
	
	protected $_config;

	public function __construct($config) {
		$this->_config = $config;
	}

	public function get($name, $defaultValue = '') {
		return isset($this->_config[$name]) ? $this->_config[$name] : $defaultValue;
	}

	public function toArray() {
		return $this->_config;
	}

	public function __get($name) {
		return $this->get($name);
	}
}
?>