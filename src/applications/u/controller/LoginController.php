<?php
Wind::import('SRV:user.PwUser');
Wind::import('SRV:user.srv.PwLoginService');
Wind::import('APPS:u.service.helper.PwUserHelper');

/**
 * 登录
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: LoginController.php 22361 2012-12-21 11:50:28Z xiaoxia.xuxx $
 * @package products.u.controller
 */
class LoginController extends PwBaseController {
	
	/*
	 * (non-PHPdoc) @see PwBaseController::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		$action = $handlerAdapter->getAction();
		if ($this->loginUser->isExists() && !in_array($action, array('showverify', 'logout', 'show'))) {
			
			$inviteCode = $this->getInput('invite');
			if ($inviteCode) {
				$user = Wekit::load('SRV:invite.srv.PwInviteFriendService')->invite($inviteCode, $this->loginUser->uid);
				if ($user instanceof PwError) {
					$this->showError($user->getError());
				}
			}
			
			if ($action == 'fast') {
				$this->showMessage('USER:login.success');
			} elseif ($action == 'welcome') {
				$this->forwardAction('u/login/show');
			} elseif($this->getRequest()->getIsAjaxRequest()) {
				$this->showError('USER:login.exists');
			} else {
				$this->forwardRedirect($this->_filterUrl());
			}
		}
	}
	
	/*
	 * (non-PHPdoc) 页面登录页 @see WindController::run()
	 */
	public function run() {
		$this->setOutput($this->_showVerify(), 'verify');
		$this->setOutput('用户登录', 'title');
		$this->setOutput($this->_filterUrl(false), 'url');
		$this->setOutput(PwUserHelper::getLoginMessage(), 'loginWay');
		$this->setOutput($this->getInput('invite'), 'invite');
		$this->setTemplate('login');
		
		Wind::import('SRV:seo.bo.PwSeoBo');
		$lang = Wind::getComponent('i18n');
		PwSeoBo::setCustomSeo($lang->getMessage('SEO:u.login.run.title'), '', '');
	}

	/**
	 * 快捷登录
	 */
	public function fastAction() {
		$this->setOutput($this->_showVerify(), 'verify');
		$this->setOutput($this->_filterUrl(), 'url');
		$this->setOutput(PwUserHelper::getLoginMessage(), 'loginWay');
		$this->setTemplate('login_fast');
	}

	/**
	 * 页面登录
	 */
	public function dorunAction() {
		$userForm = $this->_getLoginForm();
		
		/* [验证验证码是否正确] */
		if ($this->_showVerify()) {
			$veryfy = $this->_getVerifyService();
			if ($veryfy->checkVerify($userForm['code']) !== true) {
				$this->showError('USER:verifycode.error');
			}
		}
		$question = $userForm['question'];
		if ($question == -4) {
			$question = $this->getInput('myquestion', 'post');
		}
		
		/* [验证用户名和密码是否正确] */
		$login = new PwLoginService();
		$this->runHook('c_login_dorun', $login);
		
		$isSuccess = $login->login($userForm['username'], $userForm['password'], $this->getRequest()->getClientIp(), $question, $userForm['answer']);
		if ($isSuccess instanceof PwError) {
			$this->showError($isSuccess->getError());
		}
		$config = Wekit::C('site');
		if ($config['windid'] != 'local') {
			$localUser = $this->_getUserDs()->getUserByUid($isSuccess['uid'], PwUser::FETCH_MAIN); 
			if ($userForm['username'] != $localUser['username']) $this->showError('USER:user.syn.error');
		}
	
		$info = $login->sysUser($isSuccess['uid']);
		$identity = PwLoginService::createLoginIdentify($info);
		$identity = base64_encode($identity . '|' . $this->getInput('backurl'));
		
		/* [是否需要设置安全问题] */
		/* @var $userService PwUserService */
		$userService = Wekit::load('user.srv.PwUserService');
		if (empty($isSuccess['safecv']) && $userService->mustSettingSafeQuestion($info['uid'])) {
			$this->addMessage(
				array('url' => WindUrlHelper::createUrl('u/login/setquestion?v=1&_statu=' . $identity)), 'check');
		}
		$this->showMessage('', 'u/login/welcome?_statu=' . $identity);
	}

	/**
	 * 页头登录
	 */
	public function dologinAction() {
		$userForm = $this->_getLoginForm();
		
		$login = new PwLoginService();
		$result = $login->login($userForm['username'], $userForm['password'], $this->getRequest()->getClientIp());
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		} else {
			$config = Wekit::C('site');
			if ($config['windid'] != 'local') {
				$localUser = $this->_getUserDs()->getUserByUid($result['uid'], PwUser::FETCH_MAIN); 
				if ($userForm['username'] != $localUser['username']) $this->showError('USER:user.syn.error');
			}
			$info = $login->sysUser($result['uid']);
			$identity = PwLoginService::createLoginIdentify($info);
			$backUrl = $this->getInput('backurl');
			if (!$backUrl) $backUrl = $this->getRequest()->getServer('HTTP_REFERER');
			$identity = base64_encode($identity . '|' . $backUrl);
			
			if ($result['safecv']) {
				$url = WindUrlHelper::createUrl('u/login/showquestion?_statu=' . $identity);
			} elseif (Wekit::load('user.srv.PwUserService')->mustSettingSafeQuestion($info['uid'])) {
				$url = WindUrlHelper::createUrl('u/login/setquestion?_statu=' . $identity);
			} elseif ($this->_showVerify()) {
				$url = WindUrlHelper::createUrl('u/login/showquestion?_statu=' . $identity);
			}
			$this->addMessage(array('url' => $url), 'check');
			$this->showMessage('USER:login.success', 'u/login/welcome?_statu=' . $identity);
		}
	}

	/**
	 * 显示安全问题
	 */
	public function showquestionAction() {
		$statu = $this->checkUserInfo();
		$verify = $this->_showVerify();
		/* @var $userSrv PwUserService */
		$userSrv = Wekit::load('SRV:user.srv.PwUserService');
		$hasQuestion = $userSrv->isSetSafecv($this->loginUser->uid);
		if (!$hasQuestion && !$verify) {
			$this->forwardRedirect(WindUrlHelper::createUrl('u/login/welcome', array('_statu' => $statu)));
		}
		$this->setOutput($hasQuestion, 'hasQuestion');
		$this->setOutput($this->_getQuestions(), 'safeCheckList');
		$this->setOutput($verify, 'verify');
		$this->setOutput($statu, '_statu');
		$this->setTemplate('login_question');
	}

	/**
	 * 检查安全问题是否正确---也头登录的弹窗，带有验证码
	 */
	public function doshowquestionAction() {
		$statu = $this->checkUserInfo();
		$code = $this->getInput('code', 'post');
		if ($this->_showVerify()) {
			$veryfy = $this->_getVerifyService();
			if (false === $veryfy->checkVerify($code)) $this->showError('USER:verifycode.error');
		}
		/* @var $userSrv PwUserService */
		$userSrv = Wekit::load('SRV:user.srv.PwUserService');
		$hasQuestion = $userSrv->isSetSafecv($this->loginUser->uid);
		if ($hasQuestion) {
			list($question, $answer) = $this->getInput(array('question', 'answer'), 'post');
			if ($question == -4) {
				$question = $this->getInput('myquestion', 'post');
			}
			$login = new PwLoginService();
			$result = $login->checkQuestion($this->loginUser->uid, $question, $answer, $this->getRequest()->getClientIp());
			if ($result instanceof PwError) {
				$this->showError($result->getError());
			}
		}
		$this->showMessage('USER:login.success', 'u/login/welcome?_statu=' . $statu);
	}

	/**
	 * 验证密码
	 */
	public function checkpwdAction() {
		list($password, $username) = $this->getInput(array('password', 'username'), 'post');
		$login = new PwLoginService();
		$info = $login->login($username, $password, $this->getRequest()->getClientIp());
		if ($info instanceof PwError) {
			$this->showError($info->getError());
		}
		$this->showMessage();
	}

	/**
	 * 验证安全问题
	 */
	public function checkquestionAction() {
		$statu = $this->checkUserInfo();
		list($question, $answer) = $this->getInput(array('question', 'answer'), 'post');
		$userLoginBp = new PwLoginService();
		$result = $userLoginBp->checkQuestion($this->loginUser->uid, $question, $answer, $this->getRequest()->getClientIp());
		if ($result instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->showMessage();
	}

	/**
	 * 设置安全问题弹窗
	 */
	public function setquestionAction() {
		$statu = $this->checkUserInfo();
		$mustSetting = Wekit::load('user.srv.PwUserService')->mustSettingSafeQuestion($this->loginUser->uid);
		$verify = $this->_showVerify();
		$v = $this->getInput('v', 'get');
		if (!$mustSetting && (1 == $v || !$verify)) {
			$this->forwardRedirect(WindUrlHelper::createUrl('u/login/welcome', array('_statu' => $statu)));
		}
		if (1 != $v) {
			$this->setOutput($verify, 'verify');
		}
		$this->setOutput($v, 'v');
		$this->setOutput($this->_getQuestions(), 'safeCheckList');
		$this->setOutput($statu, '_statu');
		$this->setTemplate('login_setquestion');
	}

	/**
	 * 执行设置安全问题
	 */
	public function dosettingAction() {
		$statu = $this->checkUserInfo();
		$code = $this->getInput('code', 'post');
		if ($this->_showVerify() && (1 != $this->getInput('v', 'post'))) {
			$veryfy = $this->_getVerifyService();
			if (false === $veryfy->checkVerify($code)) {
				$this->showError('USER:verifycode.error');
			}
		}
		list($question, $answer) = $this->getInput(array('question', 'answer'), 'post');
		if (!$question || !$answer) $this->showError('USER:login.question.setting');
		if (intval($question) === -4) {
			$question = $this->getInput('myquestion', 'post');
			if (!$question) $this->showError('USER:login.question.setting');
		}
		
		/* @var $userDs PwUser */
		$userDs = Wekit::load('user.PwUser');
		$userDm = new PwUserInfoDm($this->loginUser->uid);
		$userDm->setQuestion($question, $answer);
		if (($result = $userDs->editUser($userDm, PwUser::FETCH_MAIN)) instanceof PwError) {
			$this->showError($result->getError());
		}
		$this->showMessage('USER:login.question.setting.success', 'u/login/welcome?_statu=' . $statu);
	}

	/**
	 * 登录成功
	 */
	public function welcomeAction() {
		$identify = $this->checkUserInfo();
		if (Pw::getstatus($this->loginUser->info['status'], PwUser::STATUS_UNACTIVE)) {
			Wind::import('SRV:user.srv.PwRegisterService');
			$identify = PwRegisterService::createRegistIdentify($this->loginUser->uid, 
				$this->loginUser->info['password']);
			$this->forwardAction('u/register/sendActiveEmail', array('_statu' => $identify, 'from' => 'login'), true);
		}
		$login = new PwLoginService();
		$login->welcome($this->loginUser, $this->getRequest()->getClientIp());
		list(, $refUrl) = explode('|', base64_decode($identify));
		if (Pw::getstatus($this->loginUser->info['status'], PwUser::STATUS_UNCHECK)) {
			$this->forwardRedirect(WindUrlHelper::createUrl('u/login/show?backurl=' . $refUrl));
		}
		if (!$refUrl) $refUrl = Wekit::app()->baseUrl;
		$config = Wekit::C('site');
		if ($config['windid'] == 'local') {
			$this->forwardRedirect($refUrl);
		} else {
			$synLogin = $this->_getWindid()->synLogin($this->loginUser->uid);
			$this->setOutput($this->loginUser->username, 'username');
			$this->setOutput($refUrl, 'refUrl');
			$this->setOutput($synLogin, 'synLogin');
		}
	}
	
	/**
	 * 提示信息
	 */
	public function showAction() {
		if (Pw::getstatus($this->loginUser->info['status'], PwUser::STATUS_UNCHECK)) {
			$this->showError('USER:login.active.check');
		}
		$this->forwardRedirect($this->_filterUrl());
	}

	/**
	 * 检查用户输入的用户名
	 */
	public function checknameAction() {
		$login = new PwLoginService();
		list($status, $info) = $login->auth($this->getInput('username'), '');
		if (-1 == $status) $this->showError('USER:user.error.-14');
		if (!empty($info['safecv'])) {
			$status = PwLoginService::createLoginIdentify($login->sysUser($info['uid']));
			$identify = base64_encode($status . '|');
			$this->addMessage($this->_getQuestions(), 'safeCheck');
			$this->addMessage($identify, '_statu');
			$this->showMessage();
		}
		$this->showMessage();
	}

	/**
	 * 退出
	 *
	 * @return void
	 */
	public function logoutAction() {
		$this->setOutput('用户登出', 'title');
		/* @var $userService PwUserService */
		$userService = Wekit::load('user.srv.PwUserService');
		if (!$userService->logout()) $this->showMessage('USER:loginout.fail');
		$url = $this->getInput('backurl');
		if (!$url) $url = $this->getRequest()->getServer('HTTP_REFERER');
		if (!$url) $url = WindUrlHelper::createUrl('u/login/run');
		$config = Wekit::C('site');
		if ($config['windid'] == 'local') {
			$this->forwardRedirect($url);
		} else {
			$synLogout = $this->_getWindid()->synLogout($this->loginUser->uid);
			$this->setOutput($this->loginUser->username, 'username');
			$this->setOutput($url, 'refUrl');
			$this->setOutput($synLogout, 'synLogout');
		}
	}

	/**
	 * 检查用户信息合法性
	 *
	 * @return string
	 */
	private function checkUserInfo() {
		$identify = $this->getInput('_statu', 'get');
		!$identify && $identify = $this->getInput('_statu', 'post');

		if (!$identify) $this->showError('USER:illegal.request');
		list($identify, $url) = explode('|', base64_decode($identify));
		list($uid, $password) = PwLoginService::parseLoginIdentify(rawurldecode($identify));
		
// 		$info = $this->_getUserDs()->getUserByUid($uid, PwUser::FETCH_MAIN);
		$this->loginUser = new PwUserBo($uid);
		if (!$this->loginUser->isExists() || Pw::getPwdCode($this->loginUser->info['password']) != $password) {
			$this->showError('USER:illegal.request');
		}
		return base64_encode($identify . '|' . $url);
	}

	/**
	 * 获得安全问题列表
	 *
	 * @return array
	 */
	private function _getQuestions() {
		$questions = PwUserHelper::getSafeQuestion();
		$questions[-4] = '自定义安全问题';
		return $questions;
	}
	
	/**
	 * 判断是否需要展示验证码
	 * 
	 * @return boolean
	 */
	private function _showVerify() {
		$config = Wekit::C('verify', 'showverify');
		!$config && $config = array();
		return in_array('userlogin', $config);
	}
	
	private function _getWindid() {
		return WindidApi::api('user');
	}

	/**
	 * 获得用户DS
	 *
	 * @return PwUser
	 */
	private function _getUserDs() {
		return Wekit::load('user.PwUser');
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return PwCheckVerifyService
	 */
	private function _getVerifyService() {
		return Wekit::load("verify.srv.PwCheckVerifyService");
	}

	/**
	 * 过滤来源URL
	 *
	 * TODO
	 * 
	 * @return string
	 */
	private function _filterUrl($returnDefault = true) {
		$url = $this->getInput('backurl');
		if (!$url) $url = $this->getRequest()->getServer('HTTP_REFERER');
		if ($url) {
			// 排除来自注册页面/自身welcome/show的跳转
			$args = WindUrlHelper::urlToArgs($url);
			if ($args['m'] == 'u' && in_array($args['c'], array('register', 'login'))) {
				$url = '';
			}
		}
		if (!$url && $returnDefault) $url = Wekit::app()->baseUrl;
		return $url;
	}

	/**
	 * @return array
	 */
	private function _getLoginForm() {
		$data = array();
		list($data['username'], $data['password'], $data['question'], $data['answer'], $data['code']) = $this->getInput(
			array('username', 'password', 'question', 'answer', 'code'), 'post');
		if (empty($data['username']) || empty($data['password'])) $this->showError('USER:login.user.require', 'u/login/run');
		return $data;
	}
}