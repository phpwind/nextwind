<?php
Wind::import('WINDID:library.WindidUtility');
Wind::import('WINDID:library.WindidError');
Wind::import('WINDID:library.base.WindidBaseDao');
Wind::import('WINDID:service.client.bo.WindidClientBo');
class Windid {
	
	protected static $_daoFactory = null;
	protected static $_daoInstances = array();
	private static $_daoMaps = array();
	
	/**
	 * 客户端获取服务端配置，服务端禁用
	 * Enter description here ...
	 * @param unknown_type $name
	 */
	public static function C($name) {
		$windidApi = WindidApi::api('config');
		return $windidApi->getConfig($name);
	}
	
	public static function client() {
		return WindidClientBo::getInstance();
	}
	
	/**
	 * 加载类文件
	 *
	 * @param string $path 类文件
	 * @return object
	 */
	public static function load($path) {
		static $cls = array();
		if (!isset($cls[$path])) {
			$class = Wind::import('WINDID:service.' . $path);
			$cls[$path] = new $class();
		}
		return $cls[$path];
	}

	/**
	 * 加载DAO文件
	 *
	 * @param string $path DAO文件
	 * @return object
	 */
	public static function loadDao($path) {
		if (self::$_daoFactory === null) {
			Wind::import('WIND:dao.WindDaoFactory');
			self::$_daoFactory = new WindDaoFactory();
		}
		$dao = self::$_daoFactory->getDao('WINDID:service.' . $path);
		$dao->setDelayAttributes(array('connection' => array('ref' => 'windiddb')));
		return $dao;
	}

	/**
	 * 获取装饰Dao的工厂方法
	 *
	 * @param int $index 索引键
	 * @param array $daoMap dao列表
	 * @param string $key 区分表
	 * @return object
	 */
	public static function loadDaoMap($index, $daoMap, $vkey) {
		if (isset($daoMap[$index])) {
			return self::loadDao($daoMap[$index]);
		}
		$vkey .= '_' . $index;
		if (!isset(self::$_daoMaps[$vkey])) {
			$instance = null;
			foreach ($daoMap as $key => $value) {
				if ($index & $key) {
					$baseInstance = $instance;
					$instance = self::loadDao($value);
					if ($baseInstance) {
						$instance = clone $instance;
						$instance->setBaseInstance($baseInstance);
					}
				}
			}
			self::$_daoMaps[$vkey] = $instance;
		}
		return self::$_daoMaps[$vkey];
	}
	
	public static function getTime($timestamp = 0) {
		!$timestamp && $timestamp = time();
		if ($cvtime = self::C('site:timecv')) $timestamp += $cvtime * 60;
		return $timestamp;
	}
	
	public static function strlen($string) {
		return WindString::strlen($string,  self::client()->clientCharser);
	}
	
	public static function substrs($string, $length, $start = 0, $dot = false) {
		return WindString::substr($string, $start, $length, self::client()->clientCharser);
	}
	
	public static function resUrl() {
		return self::client()->serverUrl.'/res/';
	}
	
	public static function attachUrl(){
		$config = self::C('attachment');
		if (!$config['storage.type'] || $config['storage.type'] == 'local') {
			$url = self::client()->serverUrl .'/attachment/';	
		} else {
			$url = $config['avatarurl'];
		}
		return $url;
	}
	
	/**
	 * 用户头像存储目录
	 *
	 * @param int $uid
	 * @return string
	 */
	public static function getUserDir($uid) {
		$uid = sprintf("%09d", $uid);
		return substr($uid, 0, 3) . '/' . substr($uid, 3, 2) . '/' . substr($uid, 5, 2);
	}
	
}