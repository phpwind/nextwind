<?php
/**
 * 配置信息服务接口
 * 
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: WindidConfigBo.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package windid.config
 */
class WindidConfigBo {

	protected static $_instance = array();
	protected $_data = array();

	/**
	 * 构造函数
	 *
	 * @param array $array
	 */
	public function __construct($array = array()) {
		$sub = array();
		foreach ($array as $key => $value) {
			if (strpos($key, '.') !== false) {
				list($k1, $k2) = explode('.', $key, 2);
				if (strlen($k1) && strlen($k2)) {
					isset($sub[$k1]) || $sub[$k1] = array();
					$sub[$k1][$k2] = $value;
				}
			} else {
				$this->_data[$key] = $value;
			}
		}
		foreach ($sub as $key => $value) {
			$this->_data[$key] = new self($value);
		}
	}

	/**
	 * 将配置信息转换为数组
	 *
	 */
	public function toArray() {
		$array = array();
		foreach ($this->_data as $key => $value) {
			if ($value instanceof self) {
				$array[$key] = $value->toArray();
			} else {
				$array[$key] = $value;
			}
		}
		return $array;
	}

	/**
	 * 魔术方法
	 *
	 * @param string $name 制定配置项
	 * @return mixed
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * 获得配置信息中制定配置项的值
	 *
	 * @param string $name 待获取的配置项
	 * @param mixed $default 配置项不存在值时返回的缺省值，默认为null
	 * @return mixed
	 */
	public function get($name, $default = null) {
		$result = $default;
		if (array_key_exists($name, $this->_data)) {
			$result = $this->_data[$name];
		}
		return $result;
	}

	/**
	 * 获取配置信息
	 *
	 * @param string $namespace 获取指定域的配置信息
	 * @return WindidConfig
	 */
	static public function getInstance($namespace = 'global') {
		if (!isset(self::$_instance[$namespace])) {
			$config = self::getConfig($namespace);
			self::$_instance[$namespace] = new self($config);
		}
		return self::$_instance[$namespace];
	}

	/**
	 * 获取配置信息
	 *
	 * @param string $namespace 获取指定域的配置信息
	 * @return array
	 */
	static public function getConfig($namespace) {
		$array = Windid::load('config.WindidConfig')->getConfig($namespace);
		return self::_formatConfig($array);
	}

	/**
	 * 格式化配置项信息
	 *
	 * @param array $array
	 * @return array
	 */
	static private function _formatConfig($array) {
		$temp = array();
		foreach ($array as $key => $value) {
			$temp[$value['name']] = ($value['vtype'] == 'string') ? $value['value'] : unserialize($value['value']);
		}
		return $temp;
	}
}