<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: phpwindBoot.php 22985 2013-01-04 08:06:07Z jieyin $
 * @package wekit
 */
class phpwindBoot {
	public $charset; //程序编码
	public $version = '9.0';
	public $baseUrl; //网站地址
	public $res; //res
	public $css;
	public $images;
	public $js;
	public $attach;
	public $themes;
	public $extres;
	public $clientIp; //当前ip
	public $requestUri;
	public $lastRequestUri;
	public $lastvisit;
	private $_loginUser = null;

	/**
	 * 构造函数
	 */
	public function __construct() {
		$this->charset = Wind::getComponent('response')->getCharset();
		$this->clientIp = Wind::getComponent('request')->getClientIp();
		$this->requestUri = Wind::getComponent('request')->getRequestUri();
	}

	/**
	 * 获取全局配置
	 *
	 * @return array
	 */
	public function getConfig() {
		return new PwConfigBo(Wekit::cache()->get('config'));
	}

	/**
	 * 获取当前时间戳
	 *
	 * @return int
	 */
	public function getTime() {
		$timestamp = time();
		if ($cvtime = Wekit::C('site', 'time.cv')) $timestamp += $cvtime * 60;
		return $timestamp;
	}

	/**
	 * 初始化应用信息
	 * @param AbstractWindFrontController $front
	 */
	public function init($front = null) {
		$this->_initUrl();
		$this->_initUser();
		$this->runApps($front);
	}

	/**
	 * 执行acloud的相关
	 * 
	 * @param AbstractWindFrontController $front
	 */
	public function runApps($front = null) {
		Wind::import('LIB:compile.acloud.PwAcloudFilter');
		$front->registeFilter(new PwAcloudFilter());
		
		$controller = Wind::getComponent('router')->getController();
		require_once Wind::getRealPath('ACLOUD:aCloud');
		ACloudAppGuiding::runApps($controller);
	}

	/** 
	 * 获得登录用户信息
	 *
	 * @return PwUserBo
	 */
	public function getLoginUser() {
		if ($this->_loginUser === null) {
			$user = $this->_getLoginUser();
			$user->ip = $this->clientIp;
			$this->_loginUser = $user->uid;
			PwUserBo::pushUser($user);
		}
		return PwUserBo::getInstance($this->_loginUser);
	}

	/**
	 * 在frontBoot的onResponse时被调用
	 * 
	 * @return void
	 */
	public function beforeResponse($front = null) {}

	/**
	 * 获得大概年前登录用户对象
	 *
	 * @return PwUserBo
	 */
	protected function _getLoginUser() {
		if (!($userCookie = Pw::getCookie('winduser'))) {
			$uid = $password = '';
		} else {
			list($uid, $password) = explode("\t", Pw::decrypt($userCookie));
		}
		$user = new PwUserBo($uid);
		if (!$user->isExists() || Pw::getPwdCode($user->info['password']) != $password) {
			$user->reset();
		} else {
			unset($user->info['password']);
		}
		return $user;
	}

	/**
	 * 初始化模板中的各静态路径
	 */
	protected function _initUrl() {
		$_consts = include (Wind::getRealPath('CONF:publish.php', true));
		foreach ($_consts as $const => $value) {
			if (defined($const)) continue;
			if ($const === 'PUBLIC_URL' && !$value) {
				$value = Wind::getComponent('request')->getBaseUrl(true);
				if (defined('BOOT_PATH') && 0 === strpos(BOOT_PATH, PUBLIC_PATH)) {
					$path = substr(BOOT_PATH, strlen(PUBLIC_PATH));
					!empty($path) && $value = substr($value, 0, -strlen($path));
				}
			}
			define($const, $value);
		}
		$this->baseUrl = PUBLIC_URL;
		$this->res = WindUrlHelper::checkUrl(PUBLIC_RES, $this->baseUrl);
		$this->css = WindUrlHelper::checkUrl(PUBLIC_RES . '/css/', $this->baseUrl);
		$this->images = WindUrlHelper::checkUrl(PUBLIC_RES . '/images/', $this->baseUrl);
		//$this->js = WindUrlHelper::checkUrl(PUBLIC_RES . '/js/' . (WIND_DEBUG ? 'dev/' : 'build/'), $this->baseUrl);
		$this->js = WindUrlHelper::checkUrl(PUBLIC_RES . '/js/' . 'dev/', $this->baseUrl);
		$this->attach = WindUrlHelper::checkUrl(PUBLIC_ATTACH, $this->baseUrl);
		$this->themes = WindUrlHelper::checkUrl(PUBLIC_THEMES, $this->baseUrl);
		$this->extres = WindUrlHelper::checkUrl(PUBLIC_THEMES . '/extres/', $this->baseUrl);
	}

	/**
	 * 初始话当前用户
	 */
	protected function _initUser() {
		$_cOnlinetime = Wekit::C('site', 'onlinetime') * 60;
		if (!($lastvisit = Pw::getCookie('lastvisit'))) {
			$this->onlinetime = 0;
			$this->lastvisit = WEKIT_TIMESTAMP;
			$this->lastRequestUri = '';
		} else {
			list($this->onlinetime, $this->lastvisit, $this->lastRequestUri) = explode("\t", $lastvisit);
			($onlinetime = WEKIT_TIMESTAMP - $this->lastvisit) < $_cOnlinetime && $this->onlinetime += $onlinetime;
		}
		$user = $this->getLoginUser();
		if ($user->isExists() && (WEKIT_TIMESTAMP - $user->info['lastvisit'] > min(1800, 
			$_cOnlinetime))) {
			Wind::import('SRV:user.dm.PwUserInfoDm');
			$dm = new PwUserInfoDm($user->uid);
			$dm->setLastvisit(WEKIT_TIMESTAMP)->setLastActiveTime(WEKIT_TIMESTAMP);
			if ($this->onlinetime > 0) {
				$dm->addOnline($this->onlinetime > $_cOnlinetime * 1.2 ? $_cOnlinetime : $this->onlinetime);
			}
			Wekit::load('user.PwUser')->editUser($dm, PwUser::FETCH_DATA);
			$this->onlinetime = 0;
		}
		Pw::setCookie('lastvisit', $this->onlinetime . "\t" . WEKIT_TIMESTAMP . "\t" . $this->requestUri, 31536000);
	}
}