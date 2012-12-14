<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: bootstrap.php 21493 2012-12-10 08:30:59Z jieyin $
 * @package wekit
 */
class bootstrap {

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
		return time();
	}

	/**
	 * 初始化应用信息
	 */
	public function init() {
		$this->_initUrl();
		$this->runApps();
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
}