<?php
Wind::import('EXT:verify.service.AppVerify_Verify');
Wind::import('EXT:verify.service.dm.AppVerify_VerifyDm');
Wind::import('LIB:utility.PwMail');
Wind::import('SRV:user.dm.PwUserInfoDm');

/**
 * 实名认证前台
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class IndexController extends PwBaseController {
	
	const VERIFY_EMAIL = 3;//邮件认证
	protected $conf = array();
	private $loginConfig = array();
	private $ipLimit = 100;
	private $trySpace = 1800;
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		if (!$this->loginUser->isExists()) {
			$this->forwardRedirect(WindUrlHelper::createUrl('u/login/run?_type=' . $this->getInput('_type')));
		}
		$this->conf = Wekit::C('appVerify');
		if (!$this->conf['verify.isopen']) {
			$this->forwardRedirect(WindUrlHelper::createUrl('bbs/index/run'));
		}
	}
	
	public function run() {
		$this->forwardRedirect(WindUrlHelper::createUrl('profile/extends/run?_left=verify'));
	}
	
	public function realnameAction() {
		$statu = $this->_checkState();
		$info = $this->_getCheckDs()->getVerifyByUidAndType($this->loginUser->uid, AppVerify_Verify::VERIFY_REALNAME);
		$info && $ischeck = 1;
		$types = $this->_getDs()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], AppVerify_Verify::VERIFY_REALNAME)) && $isVerify = 1;
		$info = $this->loginUser->info;
		
		$this->setOutput(array('title' => $types[AppVerify_Verify::VERIFY_REALNAME], 'ischeck' => $ischeck, 'isVerify' => $isVerify, 'statu' => $statu, 'realname' => $info['realname']), 'data');
		$this->setOutput('realname', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function passwdAction() {
		$type = $this->getInput('type', 'get');
		$this->setOutput(array('type' => $type), 'data');

		$this->setOutput('password', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function checkPasswdAction() {
		list($type, $passwd) = $this->getInput(array('type', 'passwd'), 'post');
		Wind::import('EXT:verify.service.srv.AppVerify_VerifyService');
		if (($result = $this->checkPasswd($passwd)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$statu = AppVerify_VerifyService::createIdentify($this->loginUser->uid, $type, $passwd);
		$this->setOutput('password', 'type_segment');
		
		$this->setOutput(array('statu' => $statu), 'data');
		$this->showMessage('success',"app/verify/index/$type");
	}
	
	public function avatarAction() {
		$types = $this->_getDs()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		Pw::getstatus($verify['type'], AppVerify_Verify::VERIFY_AVATAR) && $isVerify = 1;

		$this->setOutput(array('title' => $types[AppVerify_Verify::VERIFY_AVATAR], 'isVerify' => $isVerify), 'data');
		$this->setOutput('avatar', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function emailAction() {
		$statu = $this->_checkState();
		$types = $this->_getDs()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], AppVerify_Verify::VERIFY_EMAIL)) && $isVerify = 1;
		$info = $this->loginUser->info;
		$this->setOutput(array('email' => $info['email'], 'isVerify' => $isVerify, 'statu' => $statu), 'data');
		$this->setOutput('email', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function mobileAction() {
		$statu = $this->getInput('statu');
		$types = $this->_getDs()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], AppVerify_Verify::VERIFY_MOBILE)) && $isVerify = 1;
		$info = $this->_getUser()->getUserByUid($this->loginUser->uid, PwUser::FETCH_INFO);
		
		$this->setOutput(array('mobile' => $info['mobile'], 'isVerify' => $isVerify), 'data');
		$this->setOutput('mobile', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	public function alipayAction() {
		$statu = $this->_checkState();
		$types = $this->_getDs()->getVerifyType();
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		(!$statu && Pw::getstatus($verify['type'], AppVerify_Verify::VERIFY_ALIPAY)) && $isVerify = 1;
		$info = $this->_getUser()->getUserByUid($this->loginUser->uid, PwUser::FETCH_INFO);
		
		$this->setOutput(array('alipay' => $info['alipay'], 'isVerify' => $isVerify), 'data');
		$this->setOutput('alipay', 'type_segment');
		$this->setTemplate('verify_pop');
	}
	
	/**
	 * 邮箱认证 - 发送认证邮件
	 */
	public function doEmailAction() {
		$statu = $this->_checkState();
		$email = $this->getInput('email');
		if (!$email) $this->showError('请输入邮箱');
		$info = $this->_getUser()->getUserByEmail($email);
		if ($info && $info['uid'] != $this->loginUser->uid) $this->showError('该邮箱已存在');

		if (($result = $this->sendVerifyEmail($email)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->setOutput(array('email' => $email), 'data');
		$this->showMessage('success');
	}

	/**
	 * 确认邮箱认证
	 */
	public function verifyEmailAction() {
		$code = $this->getInput('code');
		$activeCodeDs = Wekit::load('user.PwUserActiveCode');
		$data = $activeCodeDs->getInfoByUid($this->loginUser->uid, self::VERIFY_EMAIL);
		if ($code != $data['code']) {
			$this->showError('非法请求');
		}
		$info = $this->_getUser()->getUserByEmail($data['email']);
		if ($info && $info['uid'] != $this->loginUser->uid) {
			$this->showError('该邮箱已存在');
		}
		if ($data['email'] != $this->loginUser->info['email']) {
			$dm = new PwUserInfoDm($this->loginUser->uid);
			$dm->setEmail($data['email']);
			$this->_getUser()->editUser($dm, PwUser::FETCH_MAIN);
		}
		$this->_getService()->updateVerifyInfo($this->loginUser->uid, AppVerify_Verify::VERIFY_EMAIL);
		//认证完成删除
		$activeCodeDs->deleteInfoByUid($this->loginUser->uid);
		$this->showMessage('认证成功');
	}
	
	public function verifyRealnameAction() {
		$statu = $this->_checkState();
		$realname = $this->getInput('realname');
		if (!$realname) $this->showError('请输入真实姓名');
		
		$dm = new AppVerify_VerifyDm();
		$dm->setUid($this->loginUser->uid)
			->setUsername($this->loginUser->username)
			->setType(AppVerify_Verify::VERIFY_REALNAME)
			->setData(serialize(array('realname' => $realname)));
		$this->_getService()->addCheck($dm);
		$this->showMessage('success');
	}
	
	public function loginAlipayAction() {
		$alipay = $this->getInput('alipay');
		Wind::import('APPS:appcenter.service.srv.helper.PwApplicationHelper');
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'alipayAuth', 'callback' => WindUrlHelper::createUrl('app/verify/index/verifyAlipay?uid='.$this->loginUser->uid), 'account' => $alipay));
		$info = PwApplicationHelper::requestAcloudData($url);
	    if ($info['code'] !== '0') $this->showError($info['msg']);
		$this->setOutput(array('referer' => $info['info']), 'data');
		$this->showMessage('success');
	}
	
	public function verifyAlipayAction() {
		list($uid, $alipay) = $this->getInput(array('uid', 'email'));
		$info = $this->loginUser->info;
		if ($uid != $this->loginUser->uid) {
			$this->forwardRedirect(WindUrlHelper::createUrl('bbs/index/run'));
		}
		$this->_getService()->updateVerifyInfo($this->loginUser->uid, AppVerify_Verify::VERIFY_ALIPAY);
		$dm = new PwUserInfoDm($this->loginUser->uid);
		$dm->setAlipay($alipay);
		$this->_getUser()->editUser($dm, PwUser::FETCH_INFO);
		$this->showMessage('支付宝认证成功', 'profile/extends/run?_left=verify', true);
	}
	
	public function verifyMobileAction() {
		list($mobileCode, $mobile) = $this->getInput(array('mobileCode', 'mobile'), 'post');
		if (($result = $this->_checkMobileRight($mobile)) instanceof PwError) {
			$this->showError($result->getError());
		}
		if (($result = $this->_getService()->checkVerify($mobile, $mobileCode)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->_getService()->updateVerifyInfo($this->loginUser->uid, AppVerify_Verify::VERIFY_MOBILE);
		$this->showMessage('success');
	}
	
	/**
	 * 发送手机验证码
	 */
	public function sendmobileAction() {
		$mobile = $this->getInput('mobile', 'post');
		if (($result = $this->_checkMobileRight($mobile)) instanceof PwError) {
			$this->showError($result->getError());
		}
		if (($result = Wekit::load('SRV:mobile.srv.PwMobileService')->sendMobileMessage($mobile)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->showMessage('success');
	}
	
	/**
	 * 验证手机号码
	 */
	public function checkmobileAction() {
		$mobile = $this->getInput('mobile', 'post');
		if (($result = $this->_checkMobileRight($mobile)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$result = Wekit::load('SRV:mobile.srv.PwMobileService')->checkTodayNum($mobile);
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->showMessage();
	}
	
	public function typeTabAction() {
		$type = $this->getInput('type');
		$userInfo = Wekit::load('user.PwUser')->getUserByUid($this->loginUser->uid, PwUser::FETCH_INFO);
		$userInfo = array_merge($this->loginUser->info, $userInfo);
		$verify = $this->_getDs()->getVerify($this->loginUser->uid);
		$typeId = $this->_getDs()->getVerifyTypeByName($type);
		(Pw::getstatus($verify['type'], $typeId)) && $isVerify = 1;

		$this->setOutput($userInfo, 'userinfo');
		$this->setOutput($isVerify, 'isVerify');
		$this->setTemplate('verify_segment_'.$type);
	}
	
	private function _checkMobileRight($mobile) {
		$config = Wekit::C('register');
		if (!$config['active.phone']) {
			return new PwError('USER:mobile.reg.open.error');
		}
		Wind::import('SRV:user.validator.PwUserValidator');
		if (!PwUserValidator::isMobileValid($mobile)) {
			return new PwError('USER:error.mobile');
		}
		$mobileInfo = Wekit::load('user.PwUserMobile')->getByMobile($mobile);
		if ($mobileInfo) $this->showError('USER:mobile.mobile.exist');
		return true;
	}
	
	protected function checkPasswd($oldPwd) {
		if (!$oldPwd) {
			$this->showError('USER:pwd.change.oldpwd.require');
		}
		Wind::import('SRV:user.srv.PwLoginService');
		$login = new PwLoginService();
		$ip = $this->getRequest()->getClientIp();
		if (($result = $this->allowTryAgain($this->loginUser->uid, $ip, 'pwd')) instanceof PwError) {
			return $result;
		}
		list($statu, $user) = $this->_getWindid()->login($this->loginUser->uid, $oldPwd, 1);
		if ($statu == -13) {
			return $this->updateTryLog($this->loginUser->uid, $ip, 'pwd');
		}
		return $user;
	}
	
	protected function allowTryAgain($uid, $ip, $type = 'pwd') {
		$now = Pw::getTime();
		//Ip限制添加
		/* @var $ipDs PwUserLoginIpRecode */
		$ipDs = Wekit::load('user.PwUserLoginIpRecode');
		$ipInfo = $ipDs->getRecode($ip);
		if ($ipInfo && $ipInfo['error_count'] >= 100 && $ipInfo['last_time'] == Pw::time2str($now, 'Y-m-d')) {
			return new PwError('USER:login.error.ip.tryover', array('{num}' => $this->ipLimit));
		}
		//密码次数测试
		$info = $this->_getUser()->getUserByUid($uid, PwUser::FETCH_DATA);
		if (!$info || !$info['trypwd']) {
			$num = $lastTry = 0;
		} else {
			list($lastTry, $num) = explode('|', $info['trypwd']);
		}
		$this->loginConfig = Wekit::C('login');
		$totalTry = intval($this->loginConfig['trypwd']);
		//尝试次数达到上限同时帐号还在被冻结状态
		if ($num >= $totalTry && ($now - $lastTry) <= $this->trySpace) {
			return new PwError("已经连续".$totalTry."次密码错误,您将在30分钟内无法进行密码验证");
		}
		return true;
	}

	/** 
	 * 跟新用户的尝试信息
	 *
	 * @param int $uid 用户ID
	 * @param string $ip 登录的IP地址
	 * @param string $type 记录类型
	 * @return PwError
	 */
	public function updateTryLog($uid, $ip, $type = 'pwd') {
		if (true !== ($isIpOver = $this->checkIpLimit($ip))) return $isIpOver;
		$info = $this->_getUser()->getUserByUid($uid, PwUser::FETCH_DATA);
		if (!$info || !$info['trypwd']) {
			$num = $lastTry = 0;
		} else {
			list($lastTry, $num) = explode('|', $info['trypwd']);
		}
		$now = Pw::getTime();
		$this->loginConfig = Wekit::C('login');
		$totalTry = intval($this->loginConfig['trypwd']);
		$min = $this->trySpace / 60;
		//尝试的次数没有达到上限
		if ($num < $totalTry) {
			$num = ($lastTry == 0 || ($now - $lastTry) >= $this->trySpace) ? 1 : $num + 1;
			$this->_updateTryRecode($info['uid'], $now . '|' . $num);
			if ($num == $totalTry) {
				return new PwError("已经连续".$totalTry."次密码错误,您将在".$min."分钟内无法正常登录");
			} else {
				return new PwError("密码错误,您还可以尝试".($totalTry - $num)."次");
			}
			
		//尝试的次数已经达到上限，同时上次错误的时间距离现在已经大于30分钟
		} elseif (($now - $lastTry) > $this->trySpace) {
			$this->_updateTryRecode($info['uid'], $now . '|1');
			return new PwError("密码错误,您还可以尝试".($totalTry - 1)."次");
		}
		//如果尝试的次数已经达到上限，并且上次错误的时间距离现在没有超过30分钟
		return new PwError("已经连续".$totalTry."次密码错误,您将在".$min."分钟内无法正常登录");
	}

	/**
	 * 更新尝试次数的记录
	 *
	 * @param int $uid
	 * @param string $tryPwd
	 * @return boolean|PwError
	 */
	private function _updateTryRecode($uid, $tryPwd) {
		$userdm = new PwUserInfoDm();
		$userdm->setUid($uid)->setTrypwd($tryPwd);
		return $this->_getUser()->editUser($userdm, PwUser::FETCH_DATA);
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
	
	protected function sendVerifyEmail($email) {
		$userBo = Wekit::getLoginUser();
		$user = $userBo->info;
		Wind::import('SRV:user.srv.PwRegisterService');
		
		$code = substr(md5(Pw::getTime()), mt_rand(1, 8), 8);
		$url = WindUrlHelper::createUrl('app/verify/index/verifyEmail', array('code' => $code));
		list($title, $content) = $this->_buildTitleAndContent('verify.email.title', 'verify.email.content', $user['username'], $url);

		$activeCodeDs = Wekit::load('user.PwUserActiveCode');
		$activeCodeDs->addActiveCode($user['uid'], $email, $code, Pw::getTime(), self::VERIFY_EMAIL);
	
		$mail = new PwMail();
		$mail->sendMail($email, $title, $content);
		return true;
	}
	
	protected function _buildTitleAndContent($titleKey, $contentKey, $username, $url = '') {
		$search = array('{username}', '{sitename}');
		$replace = array($username, Wekit::C('site', 'info.name'));
		$title = str_replace($search, $replace, $this->conf[$titleKey]);
		$search[] = '{time}';
		$search[] = '{url}';
		$replace[] = Pw::time2str(Pw::getTime(), 'Y-m-d H:i:s');
		$replace[] = $url ? sprintf('<a href="%s">%s</a>', $url, $url) : '';
		$content = str_replace($search, $replace, $this->conf[$contentKey]);
		return array($title, $content);
	}
	
	/**
	 * 检查是否符合要求
	 */
	private function _checkState() {
		$statu = $this->getInput('statu');
		if (!$statu) return false;
		Wind::import('EXT:verify.service.srv.AppVerify_VerifyService');
		list($uid, $type, $passwd) = AppVerify_VerifyService::parserIdentify($statu);
		if (($result = $this->checkPasswd($passwd)) instanceof PwError) {
			$this->showError($result->getError());
		}
		return $statu;
	}
	
	protected function checkVerifyExist($uid, $type) {
		$info = $this->_getDs()->getVerify($uid);
		if (Pw::getstatus($info['type'], $type)) {
			return true;
		}
		return false;
	}
	
	/**
	 * @return AppVerify_VerifyService
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.AppVerify_VerifyService');
	}
	
	/** 
	 * 获得PwUser
	 *
	 * @return PwUser
	 */
	protected function _getUser() {
		return Windid::load('user.PwUser');
	}
	
	/** 
	 * 获得windidDS
	 *
	 * @return WindidUser
	 */
	protected function _getWindid() {
		return WindidApi::api('user');
	}
	
	/**
	 * @return AppVerify_VerifyCheck
	 */
	protected function _getCheckDs() {
		return Wekit::load('EXT:verify.service.AppVerify_VerifyCheck');
	}
	
	/**
	 * @return AppVerify_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.AppVerify_Verify');
	}
}

?>