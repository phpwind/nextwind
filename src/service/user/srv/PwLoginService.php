<?php
Wind::import('SRV:user.PwUser');
Wind::import('SRV:user.dm.PwUserInfoDm');
Wind::import('SRV:credit.bo.PwCreditBo');
Wind::import('WIND:utility.WindValidator');
Wind::import('SRV:user.validator.PwUserValidator');

/**
 * 用户登录服务
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwLoginService.php 23331 2013-01-08 10:04:34Z xiaoxia.xuxx $
 * @package src.service.user.srv
 */
class PwLoginService extends PwBaseHookService {
	
	private $loginConfig = array();
	private $ipLimit = 100;
	
	/**
	 * 尝试次数达到最高次数之后，一段时间30分钟内不能再登录
	 * 
	 * @var int $trySpace 
	 */
	private $trySpace = 1800;
	
	public function __construct() {
		$this->loginConfig = Wekit::C('login');
	}
	
	/**
	 * 用户登录
	 *
	 * @param string $username 用户登录的帐号
	 * @param string $password 用户登录的密码
	 * @param string $ip 登录IP
	 * @param string $safeQuestion 安全问题
	 * @param string $safeAnswer 安全问题答案
	 * @return boolean|int
	 */
	public function login($username, $password, $ip, $safeQuestion = null, $safeAnswer = '') {
		$checkQ = !is_null($safeQuestion) ? true : false;
		list($status, $info) = $this->auth($username, $password, $checkQ, $safeQuestion, $safeAnswer);
		switch ($status) {
			case 1://用户信息正常
				if (true !== ($r = $this->allowTryAgain($info['uid'], $ip))) {
					return $r;
				}
				break;
			case -1://用户不存在
				return new PwError('USER:user.error.-14');
			case -2://用户密码错误
				Wind::import('SRV:log.PwLogLogin');
				Wind::import('SRV:log.dm.PwLogLoginDm');
				$dm = new PwLogLoginDm($info['uid']);
				$dm->setUsername($info['username'])
					->setTypeid(PwLogLogin::ERROR_PWD)
					->setIp($ip)
					->setCreatedTime(Pw::getTime());
				Wekit::load('SRV:log.PwLogLogin')->addLog($dm);
				return $this->updateTryLog($info['uid'], $ip, 'pwd');
			case -3://用户安全问题错误
				Wind::import('SRV:log.PwLogLogin');
				Wind::import('SRV:log.dm.PwLogLoginDm');
				$dm = new PwLogLoginDm($info['uid']);
				$dm->setUsername($info['username'])
					->setIp($ip)
					->setCreatedTime(Pw::getTime())
					->setTypeid(PwLogLogin::ERROR_SAFEQ);
				Wekit::load('SRV:log.PwLogLogin')->addLog($dm);
				return $this->updateTryLog($info['uid'], $ip, 'question');
		}
		
		if (($result = $this->runWithVerified('afterLogin', $info)) instanceof PwError) return $result;
		return $info;
	}
	
	/** 
	 * 检查安全问题和密码
	 *
	 * @param int $uid 用户信息
	 * @param string $question 安全问题
	 * @param string $answer 安全问题答案
	 * @return PwError
	 */
	public function checkQuestion($uid, $question, $answer, $ip) {
		$info = $this->_getWindid()->getUser($uid, 1);
		if ($this->_getWindid()->checkQuestion($uid, $question, $answer) > 0) {
			return $this->allowTryAgain($info['uid'], $ip, 'question');
		}
		return $this->updateTryLog($info['uid'], $ip, 'question');
	}
	
	/**
	 * 同步用户数据
	 * 
	 * 如果本地有用户数据
	 * 如果本地没有用户数据，则将用户数据从windid同步过来
	 *
	 * @param int $userid
	 */
	public function sysUser($userid) {
		$info = $this->_getUserDs()->getUserByUid($userid, PwUser::FETCH_MAIN);
		if (!$info) {
			//从windid这边将数据同步到论坛
			if (!$this->_getUserDs()->activeUser($userid)) return false;
			$pwUserInfoDm = new PwUserInfoDm($userid);
			//【用户同步】计算memberid
			/* @var $groupService PwUserGroupsService */
			$groupService = Wekit::load('usergroup.srv.PwUserGroupsService');
			$strategy = Wekit::C('site', 'upgradestrategy');
			$_credit = $this->_getUserDs()->getUserByUid($userid, PwUser::FETCH_MAIN | PwUser::FETCH_DATA);
			$credit = $groupService->calculateCredit($strategy, $_credit);
			$memberid = $groupService->calculateLevel($credit, 'member');
			$pwUserInfoDm->setMemberid($memberid);
			
			$this->_getUserDs()->editUser($pwUserInfoDm);
			//添加到注册审核表中
			/* @var $registerCheckDs PwUserRegisterCheck */
			$registerCheckDs = Wekit::load('user.PwUserRegisterCheck');
			$registerCheckDs->addInfo($userid, 1, 1);
			
			$info = $this->_getUserDs()->getUserByUid($userid, PwUser::FETCH_MAIN);
			/* @var $userSrv PwUserService */
			//$userSrv = Wekit::load('SRV:user.srv.PwUserService');
			//$userSrv->restoreDefualtAvatar($userid);
		}
		return $info;
	}
	
	/** 
	 * 获得登录用户信息
	 *
	 * @param string $username 用户名
	 * @param string $password 密码
	 * @param boolean $checkQ 是否验证安全问题
	 * @param string $safeQuestion 安全问题
	 * @param string $safeAnswer 安全问题答案
	 * @return array
	 */
	public function auth($username, $password, $checkQ = false, $safeQuestion = '', $safeAnswer = '') {
		$r = array(-14, array());
		//手机号码登录
		if (PwUserValidator::isMobileValid($username) === true && in_array(4, $this->loginConfig['ways'])) {
			$mobileInfo = Wekit::load('user.PwUserMobile')->getByMobile($username);
			if (!$mobileInfo) return array(-1, array());
			$r = $this->_getWindid()->login($mobileInfo['uid'], $password, 1, $checkQ, $safeQuestion, $safeAnswer);
		}
		//UID登录
		if ($r[0] == -14 && is_numeric($username) && in_array(1, $this->loginConfig['ways'])) {
			$r = $this->_getWindid()->login($username, $password, 1, $checkQ, $safeQuestion, $safeAnswer);
		}
		
		//email登录
		if ($r[0] == -14 && WindValidator::isEmail($username) && in_array(2, $this->loginConfig['ways'])) {
			$r = $this->_getWindid()->login($username, $password, 3, $checkQ, $safeQuestion, $safeAnswer);
		}
		//用户名登录
		if ($r[0] == -14 && in_array(3, $this->loginConfig['ways'])) {
			$r = $this->_getWindid()->login($username, $password, 2, $checkQ, $safeQuestion, $safeAnswer);
		}
		switch ($r[0]) {
			case 1://用户信息正常
				return array(1, $r[1]);
			case -13://用户密码错误
				return array(-2, $r[1]);
			case -20://用户安全问题错误
				return array(-3, $r[1]);
			case -14://用户不存在
			default:
				return array(-1, array());
		}
	}

	/** 
	 * 检查用户是否已经超过尝试设置的次数
	 *
	 * @param int $uid
	 * @return boolean|PwError
	 */
	public function allowTryAgain($uid, $ip, $type = 'pwd') {
		$now = Pw::getTime();
		//Ip限制添加
		/* @var $ipDs PwUserLoginIpRecode */
		$ipDs = Wekit::load('user.PwUserLoginIpRecode');
		$ipInfo = $ipDs->getRecode($ip);
		if ($ipInfo && $ipInfo['error_count'] >= $this->ipLimit && $ipInfo['last_time'] == Pw::time2str($now, 'Y-m-d')) {
			return new PwError('USER:login.error.ip.tryover', array('{num}' => $this->ipLimit));
		}
		//密码次数测试
		$info = $this->_getUserDs()->getUserByUid($uid, PwUser::FETCH_DATA);
		if (!$info || !$info['trypwd']) {
			$num = $lastTry = 0;
		} else {
			list($lastTry, $num) = explode('|', $info['trypwd']);
		}
		$totalTry = intval($this->loginConfig['trypwd']);
		//尝试次数达到上限同时帐号还在被冻结状态
		if ($num >= $totalTry && ($now - $lastTry) <= $this->trySpace) {
			return new PwError('USER:login.error.tryover.' . $type, array('{totalTry}' => $totalTry, '{min}' => $this->trySpace / 60));
		}
		return true;
	}
	
	/**
	 * 登录扩展点 m_login_welcome
	 * 
	 * @see PwUserLoginDoBase::welcome()
	 */
	public function welcome(PwUserBo $userBo, $ip) {
		//登录成功，将用户该次登录的尝试密码记录清空
		$this->_updateTryRecode($userBo->uid, '');
		/* @var $userService PwUserService */
		$userService = Wekit::load('user.srv.PwUserService');
		$userService->updateLastLoginData($userBo->uid, $ip);
		$userService->createIdentity($userBo->uid, $userBo->info['password']);
		/* @var $creditBo PwCreditBo */
		$creditBo = PwCreditBo::getInstance();
		$creditBo->operate('login', $userBo);
		
		$hookService = new PwHookService('login_welcome', 'PwUserLoginDoBase');
		return $hookService->runDo('welcome', $userBo, $ip);
	}
	
	/**
	 * 创建登录标识
	 *
	 * @param array $userInfo 用户信息
	 * @return string
	 */
	public static function createLoginIdentify($userInfo) {
		$code = Pw::encrypt($userInfo['uid'] . "\t" . Pw::getPwdCode($userInfo['password']) . "\t" . Pw::getTime());
		return rawurlencode($code);
	}
	
	/**
	 * 解析登录标识
	 *
	 * @param string $identify 需要解析的标识
	 * @return array array($uid, $password)
	 */
	public static function parseLoginIdentify($identify) {
		$args = explode("\t", Pw::decrypt(rawurldecode($identify)));
		if ((Pw::getTime() - $args[2]) > 300) {
			return array(0, '');
		} else {
			return $args;
		}
	}
	
	/** 
	 * 跟新用户的尝试信息
	 *
	 * @param int $uid 用户ID
	 * @param string $ip 登录的IP地址
	 * @param string $type 记录类型
	 * @return PwError
	 */
	private function updateTryLog($uid, $ip, $type = 'pwd') {
		if (true !== ($isIpOver = $this->checkIpLimit($ip))) return $isIpOver;
		$info = $this->_getUserDs()->getUserByUid($uid, PwUser::FETCH_DATA);
		if (!$info || !$info['trypwd']) {
			$num = $lastTry = 0;
		} else {
			list($lastTry, $num) = explode('|', $info['trypwd']);
		}
		$now = Pw::getTime();
		$totalTry = intval($this->loginConfig['trypwd']);
		//尝试的次数没有达到上限
		if ($num < $totalTry) {
			$num = ($lastTry == 0 || ($now - $lastTry) >= $this->trySpace) ? 1 : $num + 1;
			$this->_updateTryRecode($info['uid'], $now . '|' . $num);
			if ($num == $totalTry) {
				return new PwError('USER:login.error.tryover.' . $type, array('{totalTry}' => $totalTry, '{min}' => $this->trySpace / 60));
			} else {
				return new PwError('USER:login.error.' . $type, array('{num}' => $totalTry - $num));
			}
			
		//尝试的次数已经达到上限，同时上次错误的时间距离现在已经大于30分钟
		} elseif (($now - $lastTry) > $this->trySpace) {
			$this->_updateTryRecode($info['uid'], $now . '|1');
			return new PwError('USER:login.error.' . $type, array('{num}' => $totalTry - 1));
		}
		//如果尝试的次数已经达到上限，并且上次错误的时间距离现在没有超过30分钟
		return new PwError('USER:login.error.tryover.' . $type, array('{totalTry}' => $totalTry, '{min}' => $this->trySpace / 60));
	}
	
	/**
	 * 检查IP的限制
	 *
	 * @param string $ip
	 * @return boolean|PwError
	 */
	private function checkIpLimit($ip) {
		if (!$ip) return true;
		/* @var $ipDs PwUserLoginIpRecode */
		$ipDs = Wekit::load('user.PwUserLoginIpRecode');
		$info = $ipDs->getRecode($ip);
		$tody = Pw::time2str(Pw::getTime(), 'Y-m-d');
		if (!$info) {
			$info['error_count'] = 0;
			$info['last_time'] = $tody;
		}
		//不是今天的则先清空
		($info['last_time'] != $tody) && $info['error_count'] = 0;
		if ($info['error_count'] >= $this->ipLimit) {
			return new PwError('USER:login.error.ip.tryover', array('{num}' => $this->ipLimit));
		}
		$error_count = $info['error_count'] + 1;
		$ipDs->updateRecode($ip, $tody, $error_count);
		return true;
	}

	/**
	 * 更新尝试次数的记录
	 *
	 * @param int $uid
	 * @param string $tryPwd
	 * @return boolean|PwError
	 */
	private function _updateTryRecode($uid, $tryPwd) {
		$userdm = new PwUserInfoDm($uid);
		$userdm->setTrypwd($tryPwd);
		return $this->_getUserDs()->editUser($userdm, PwUser::FETCH_DATA);
	}
	
	/** 
	 * 获得用户Ds
	 *
	 * @return PwUser
	 */
	private function _getUserDs() {
		return Wekit::load('user.PwUser');
	}
	
	/** 
	 * 获得windidDS
	 *
	 * @return WindidUserApi
	 */
	protected function _getWindid() {
		return WindidApi::api('user');
	}
		
	/* (non-PHPdoc)
	 * @see PwBaseHookService::_getInterfaceName()
	 */
	protected function _getInterfaceName() {
		return 'PwUserLoginDoBase';
	}
}