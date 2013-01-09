<?php
define('WEKIT_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('WEKIT_VERSION', '0.3.9');
define('WINDID_VERSION', '0.0.2');
define('NEXT_VERSION', '9.0');
define('NEXT_RELEASE', '20130107');
defined('WIND_DEBUG') || define('WIND_DEBUG', 0);

require WEKIT_PATH . '../wind/Wind.php';


/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: wekit.php 23388 2013-01-09 07:23:34Z liusanbian $
 * @package wekit
 */
class Wekit {

	protected static $_config;
	protected static $_cache;
	protected static $_var = array();
	protected static $_app;

	/**
	 * 运行当前应用
	 *
	 * @param string $name 应用名称默认‘phpwind’
	 * @param array $components 组建配置信息 该组建配置将会覆盖原组建配置，默认为空
	 */
	public static function run($name = 'phpwind', $components = array()) {
		$config = WindUtility::mergeArray(include WEKIT_PATH . '../conf/application/default.php', 
			include WEKIT_PATH . '../conf/application/' . $name . '.php');
		if (!empty($components)) $config['components'] = (array) $components + $config['components'];
		
		/* @var $application WindWebFrontController */
		$application = Wind::application($name, $config);
		$application->registeFilter(new PwFrontFilters($application));
		$application->run();
	}

	/**
	 * phpwind初始化
	 *
	 * @return void
	 */
	public static function init() {
		function_exists('set_magic_quotes_runtime') && @set_magic_quotes_runtime(0);
		$_conf = include WEKIT_PATH . '../conf/directory.php';
		foreach ($_conf as $namespace => $path) {
			$realpath = realpath(WEKIT_PATH . $path);
			Wind::register($realpath, $namespace);
			define($namespace . '_PATH', $realpath . DIRECTORY_SEPARATOR);
		}
		Wind::register(WEKIT_PATH, 'WEKIT');
		self::_loadBase();
	}

	/**
	 * 获取当前应用
	 *
	 * @return phpwindBoot
	 */
	public static function app() {
		return self::$_app;
	}

	/**
	 * 创建当前应用实例
	 */
	public static function createapp($appName) {
		if (!is_object(self::$_app)) {
			self::$_var = include CONF_PATH . 'baseconfig.php';
			self::$_cache = new PwCache();
			if (self::$_var['dbcache'] && self::$_cache->isDbCache()) {
				PwLoader::importCache(include CONF_PATH . 'cacheService.php');
			}
			$class = Wind::import('SRC:bootstrap.' . $appName . 'Boot');
			self::$_app = new $class();
			self::$_config = self::$_app->getConfig();
			define('WEKIT_TIMESTAMP', self::$_app->getTime());
		}
	}

	/**
	 * 获取实例
	 *
	 * @param string $path 路径
	 * @param string $loadway 加载方式
	 * @param array $args 参数
	 * @return object
	 */
	public static function getInstance($path, $loadway = '', $args = array()) {
		switch ($loadway) {
			case 'loadDao':
				return Wekit::loadDao($path);
			case 'load':
				return Wekit::load($path);
			case 'static':
				return Wind::import($path);
			default:
				$reflection = new ReflectionClass(Wind::import($path));
				return call_user_func_array(array($reflection, 'newInstance'), $args);
		}
	}

	/**
	 * 加载类库(单例)
	 *
	 * @param string $path 路径
	 * @return object
	 */
	public static function load($path) {
		return PwLoader::load($path);
	}

	/**
	 * 加载Dao(单例)
	 *
	 * @param string $path 路径
	 * @return object
	 */
	public static function loadDao($path) {
		return PwLoader::loadDao($path);
	}

	/**
	 * 获取Dao组合(单例)
	 *
	 * @param int $index 索引键
	 * @param array $daoMap dao列表
	 * @param string $vkey 区分符
	 * @return object
	 */
	public static function loadDaoFromMap($index, $daoMap, $vkey) {
		return PwLoader::loadDaoFromMap($index, $daoMap, $vkey);
	}

	/**
	 * 设置全局变量
	 *
	 * @param array|string|object $data
	 * @param string $key
	 * @see WindWebApplication::setGlobal
	 */
	public static function setGlobal($data, $key = '') {
		if ($key)
			$_G[$key] = $data;
		else {
			if (is_object($data)) $data = get_object_vars($data);
			$_G = $data;
		}
		Wind::getApp()->getResponse()->setData($_G, 'G', true);
	}

	/**
	 * 获得全局变量
	 *
	 * @return array|string|object
	 * @see WindWebApplication::getGlobal
	 */
	public static function getGlobal() {
		$_args = func_get_args();
		array_unshift($_args, 'G');
		return call_user_func_array(array(Wind::getApp()->getResponse(), 'getData'), $_args);
	}

	/**
	 * 获取当前登录用户
	 *
	 * @return PwUserBo
	 */
	public static function getLoginUser() {
		return self::$_app->getLoginUser();
	}

	/**
	 * 获取全局基本配置
	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function V($var) {
		return self::$_var[$var];
	}

	/**
	 * 获取应用配置 config
	 *
	 * @param string $namespace 配置域
	 * @param string $key 配置键值
	 * @return mixted
	 */
	public static function C($namespace = '', $key = '') {
		if ($namespace) {
			return $key ? self::$_config->$namespace->get($key) : self::$_config->$namespace->toArray();
		}
		return self::$_config;
	}

	/**
	 * 获取通用缓存服务
	 *
	 * @return object
	 */
	public static function cache() {
		return self::$_cache;
	}

	/**
	 * 预加载相关类文件
	 *
	 * @return void
	 */
	protected static function _loadBase() {
		Wind::import('WIND:utility.WindFolder');
		Wind::import('WIND:utility.WindJson');
		Wind::import('WIND:utility.WindFile');
		Wind::import('WIND:utility.WindValidator');
		Wind::import('WIND:utility.WindCookie');
		Wind::import('WIND:utility.WindSecurity');
		Wind::import('WIND:utility.WindString');
		Wind::import('WIND:utility.WindConvert');
		
		Wind::import('LIB:base.*');
		Wind::import('LIB:engine.extension.viewer.*');
		Wind::import('LIB:engine.component.*');
		Wind::import('LIB:engine.error.*');
		Wind::import('LIB:engine.exception.*');
		Wind::import('LIB:engine.hook.*');
		Wind::import('LIB:Pw');
		Wind::import('LIB:PwLoader');
		Wind::import('LIB:filter.PwFrontFilters');
		
		Wind::import('SRV:cache.PwCache');
		Wind::import('SRV:config.bo.PwConfigBo');
		Wind::import('SRV:config.srv.PwConfigSet');
		Wind::import('WINDID:library.Windid');
		Wind::import('WINDID:WindidApi');
		Wind::import('SRV:user.bo.PwUserBo');
	}
}
Wekit::init();
