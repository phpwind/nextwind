<?php

/**
 * 后台用户服务类
 *
 * 后台用户服务类,职责:<ol>
 * <li>login,用户登录</li>
 * <li>logout,用户退出</li>
 * <li>isLogin,用户是否已登录</li>
 * </ol>
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-17
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AdminUserService.php 21768 2012-12-13 06:39:43Z long.shi $
 * @package admin
 * @subpackage library.service
 */
class AdminUserService {

	const FOUNDER = 'founder';
	const USER = 'user';

	protected $cookieName = 'AdminUser';
	private $_founder = null;

	/**
	 * 根据用户名来判断用户的合法性
	 *
	 * 合法用户返回true，非法用户返回false
	 *
	 * @param string $username        	
	 * @return boolean
	 */
	public function verifyUserByUsername($username) {
		return $this->loadUser()->getUserByName($username);
	}

	/**
	 * 验证用户是否有访问菜单的权限
	 *
	 * @param PwUserBo $user 用户ID
	 * @param string $m 路由信息Module
	 * @param string $c 路由信息Controller
	 * @param string $a 路由信息Action
	 * @return true Error
	 */
	public function verifyUserMenuAuth(PwUserBo $user, $m, $c, $a) {
		$_menus = $this->getAuths($user);
		if ($_menus === '-1') return true;
		if (empty($_menus) || !is_array($_menus)) return new PwError('ADMIN:menu.fail.allow');
		/* @var $menuService AdminMenuService */
		$menuService = Wekit::load('ADMIN:service.srv.AdminMenuService');
		$authStruts = $menuService->getMenuAuthStruts();
		$authKeys = array();
		if (isset($authStruts[$m][$c]['_all'])) $authKeys += $authStruts[$m][$c]['_all'];
		if (isset($authStruts[$m][$c][$a])) $authKeys += $authStruts[$m][$c][$a];
		foreach ($authKeys as $_key)
			if (in_array($_key, $_menus)) return true;
		return new PwError('ADMIN:menu.fail.allow');
	}

	/**
	 * 根据用户ID,获取这个用户的全部后台权限菜单.
	 *
	 * 返回值定义:<pre>
	 * 1. -1 所有权限
	 * 2. array()		没有任何权限
	 * 3. array('home') 只有home菜单权限
	 * </pre>
	 *
	 * @param AdminUserBo $user        	
	 * @return array PwError -1
	 */
	public function getAuths(PwUserBo $user) {
		list($uid, $username) = array($user->uid, $user->username);
		if ($this->isFounder($username)) return '-1';
		
		/* @var $authDS AdminAuth */
		$authService = Wekit::load('ADMIN:service.AdminAuth');
		$userAuths = $authService->findByUid($uid);
		if (empty($userAuths['roles'])) return array();
		
		$roles = explode(',', $userAuths['roles']);
		/* @var $roleService AdminRole */
		$roleService = Wekit::load('ADMIN:service.AdminRole');
		$roles = $roleService->findRolesByNames($roles);
		if ($roles instanceof PwError) return new PwError('ADMIN:fail');
		
		$_tmp = '';
		foreach ($roles as $role) {
			$_tmp .= $role['auths'] . ',';
		}
		return empty($_tmp) ? array() : explode(',', trim($_tmp, ','));
	}

	/**
	 * 后台用户登录服务
	 *
	 * 后台用户登录服务,并返回用户对象.参数信息:<code>
	 * $loginInfo: AdminUser
	 * </code>
	 *
	 * @param string $username 用户名
	 * @param string $password 密码
	 * @return boolean
	 */
	public function login($username, $password) {
		$conf = $this->getFounders();
		if (isset($conf[$username])) {
			list($md5pwd, $salt) = explode('|', $conf[$username], 2);
			if (md5($password . $salt) != $md5pwd) return new PwError('ADMIN:login.fail.user.illegal');
			$cookie = Pw::encrypt(self::FOUNDER . "\t" . $username . "\t" . Pw::getPwdCode($md5pwd));
		} else {
			// [后台登录ip限制]
			if (!$this->ipLegal(Wekit::app()->clientIp)) return new PwError('ADMIN:login.fail.ip');
			$user = $this->loadUserService()->verifyUser($username, $password, 2);
			if ($user instanceof PwError) return new PwError('ADMIN:login.fail.user.illegal');
			/* @var $auth AdminAuth */
			$auth = Wekit::load('ADMIN:service.AdminAuth');
			if (!$auth->findByUid($user['uid'])) return new PwError('ADMIN:login.fail.allow');
	
			$u = Wekit::load('user.PwUser')->getUserByUid($user['uid']);
			$cookie = Pw::encrypt(self::USER . "\t" . $user['uid'] . "\t" . Pw::getPwdCode($u['password']));
		}
		Pw::setCookie($this->cookieName, $cookie, 1800);
		return true;
	}

	/**
	 * 后台用户退出服务
	 *
	 * @return boolean
	 */
	public function logout() {
		return Pw::setCookie($this->cookieName, '', -1);
	}

	/**
	 *
	 * @return PwUserService
	 */
	private function loadUserService() {
		try {
			return Wekit::load('user.srv.PwUserService');
		} catch (Exception $e) {
			throw new PwDependanceException('EXCEPTION:admin.userservice', 
				array(
					'{service}' => __CLASS__, 
					'{userservice}' => Wind::getRealPath('user.srv.PwUserService')));
		}
	}

	/**
	 *
	 * @return PwUser
	 */
	private function loadUser() {
		try {
			return Wekit::load('user.PwUser');
		} catch (Exception $e) {
			throw new PwDependanceException('admin.userservice', 
				array(
					'{service}' => __CLASS__, 
					'{userservice}' => Wind::getRealPath('SRV:user.PwUser')));
		}
	}

	/**
	 * 读取创始人配置文件
	 *
	 * @return PwError array
	 */
	public function getFounders() {
		if ($this->_founder === null) {
			$this->_founder = include Wind::getRealPath('CONF:founder.php', true);
			is_array($this->_founder) || $this->_founder = array();
		}
		return $this->_founder;
	}

	/**
	 * 根据配置文件，查看是否管理员
	 *
	 * @param string $username        	
	 */
	public function isFounder($username) {
		$founders = $this->getFounders();
		return isset($founders[$username]);
	}

	/**
	 * 验证后台登录ip
	 *
	 * @param string $ip        	
	 * @return boolean
	 */
	public function ipLegal($ip) {
		$ips = Wekit::C('admin', 'ip.allow');
		if (empty($ips)) return true;
		$ipArray = explode(',', $ips);
		$result = false;
		$ip = trim($ip);
		foreach ($ipArray as $v) {
			$v = trim($v);
			if ($v && strpos(",$ip.", ",$v.") !== false) {
				$result = true;
				break;
			}
		}
		return $result;
	}
}
?>