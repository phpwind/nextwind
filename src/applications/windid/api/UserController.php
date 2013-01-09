<?php
Wind::import('APPS:windid.api.OpenBaseController');
Wind::import('WINDID:api.local.WindidUserApi');
/**
 * windid用户接口
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: UserController.php 23072 2013-01-06 02:12:11Z gao.wanggao $ 
 * @package 
 */
class UserController extends OpenBaseController {

	public function registerAction() {
		$username = $this->getInput('username', 'post');
		$email = $this->getInput('email', 'post');
		$password = $this->getInput('password', 'post');
		$question = $this->getInput('question', 'post');
		$answer = $this->getInput('answer', 'post');
		$regip = $this->getInput('regip', 'post');
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		$dm = new WindidUserDm();
		$dm->setUsername($username)->setEmail($email)->setPassword($password)->setQuestion($question)->setAnswer($answer)->setRegip($regip);
		$result = $this->getUserDs()->addUser($dm);
		if ($result instanceof WindidError) return $result->getCode();
		$uid = (int)$result;
		$srv = Windid::load('user.srv.WindidUserService');
		$srv->defaultAvatar($uid, 'face');
		$this->_getNotifyClient()->send('register', $uid);
		$this->output($uid);
	}
	
	public function loginAction() {
		$userid = $this->getInput('userid', 'post');
		$password = $this->getInput('password', 'post');
		$type = $this->getInput('type', 'post');
		$ifcheck = (bool)$this->getInput('ifcheck', 'post');
		$question = $this->getInput('question', 'post');
		$answer = $this->getInput('answer', 'post');
		!$type && $type = 2;
		$result = $this->getApi()->login($userid, $password, $type, $ifcheck, $question, $answer);
		$this->output($result);
	}

	public function synLoginAction() {
		$result = $this->getApi()->synLogin($this->getInput('uid', 'post'));
		$this->output($result);
	}
	
	public function synLogoutAction() {
		$result = $this->getApi()->synLogout($this->getInput('uid', 'post'));
		$this->output($result);
	}
	
	public function checkInputAction() {
		$input = $this->getInput('input', 'post');
		$type = $this->getInput('type', 'post');
		$username = $this->getInput('username', 'post');
		$uid = $this->getInput('uid', 'post');
		$result = $this->getApi()->checkUserInput($input, $type, $username, $uid);
		$this->output($result);
	}
	
	public function checkQuestionAction() {
		$question = $this->getInput('question', 'post');
		$answer = $this->getInput('answer', 'post');
		$uid = $this->getInput('uid', 'post');
		$result = $this->getApi()->checkQuestion($uid, $question, $answer);
		$this->output($result);
	}
	
	public function getAction() {
		$userid = $this->getInput('userid', 'get');
		$type = $this->getInput('type', 'get');
		!$type && $type = 1;
		$result = $this->getApi()->getUser($userid, $type);
		$this->output($result);
	}

	public function getInfoAction() {
		$userid = $this->getInput('userid', 'get');
		$type = $this->getInput('type', 'get');
		!$type && $type = 1;
		$result = $this->getApi()->getUserInfo($userid, $type);
		$this->output($result);
	}
	
	/**
	 * 批量获取用户信息
	 */
	public function fecthInfoAction() {
		$uids = explode('_',$this->getInput('uids', 'get'));
		$type = $this->getInput('type', 'get');
		!$type && $type = 1;
		$result = $this->getApi()->getUserInfo($uids, $type);
		$this->output($result);
	}
	
	/**
	 * 修改用户基本信息
	 */
	public function editAction() {
		$uid = $this->getInput('uid', 'post');
		$password = $this->getInput('password', 'post');
		$editInfo['username'] = $this->getInput('username', 'post');
		$editInfo['password'] = $this->getInput('newpassword', 'post');
		$editInfo['email'] = $this->getInput('email', 'post');
		$editInfo['question'] = $this->getInput('question', 'post');
		$editInfo['answer'] = $this->getInput('answer', 'post');
		$result = $this->getApi()->editUser($uid, $password, $editInfo);
		$this->output($result);
	}
	
	
	public function editInfoAction($uid, $editInfo) {
		$uid = $this->getInput('uid', 'post');
		$editInfo['realname'] = $this->getInput('realname', 'post');
		$editInfo['profile'] = $this->getInput('profile', 'post');
		$editInfo['icon'] = $this->getInput('icon', 'post');
		$editInfo['gender'] = $this->getInput('gender', 'post');
		$editInfo['byear'] = $this->getInput('byear', 'post');
		$editInfo['bmonth'] = $this->getInput('bmonth', 'post');
		$editInfo['bday'] = $this->getInput('bday', 'post');
		$editInfo['hometown'] = $this->getInput('hometown', 'post');
		$editInfo['location'] = $this->getInput('location', 'post');
		$editInfo['homepage'] = $this->getInput('homepage', 'post');
		$editInfo['qq'] = $this->getInput('qq', 'post');
		$editInfo['aliww'] = $this->getInput('aliww', 'post');
		$editInfo['mobile'] = $this->getInput('mobile', 'post');
		$editInfo['alipay'] = $this->getInput('alipay', 'post');
		$editInfo['msn'] = $this->getInput('msn', 'post');
		$result = $this->getApi()->editUserInfo($uid, $editInfo);
		$this->output($result);
	}
	
	/**
	 * 删除一个用户
	 */
	public function deleteAction() {
		$result = $this->getApi()->deleteUser($this->getInput('uid', 'post'));
		$this->output($result);
	}
	
	public function batchDeleteAction() {
		$uids = explode('_',$this->getInput('uids', 'post'));
		$result = $this->getApi()->batchDeleteUser($uids);
		$this->output($result);
	}

	/**
	 * 获取用户积分
	 * Enter description here ...
	 * @param int $uid
	 */
	public function getCreditAction() {
		$result = $this->getApi()->getUserCredit($this->getInput('uid', 'get'));
		unset($result['messages']);
		$this->output($result);
	}
	
	/**
	 * 批量获取用户积分
	 * Enter description here ...
	 * @param array $uids
	 * @return array
	 */
	public function fecthCreditAction() {
		$uids = explode('_',$this->getInput('uids', 'get'));
		$result = $this->getApi()->fecthUserCredit($uids);
		$this->output($result);
	}
	
	/**
	 * 更新用户积分
	 * Enter description here ...
	 * @param int $uid
	 * @param int $cType (1-8)
	 * @param int $value
	 */
	public function editCreditAction() {
		$uid = (int)$this->getInput('uid', 'post'); 
		$cType = (int)$this->getInput('cType', 'post'); 
		$value = (int)$this->getInput('value', 'post');
		$isset = (bool)$this->getInput('isset', 'post');
		$result = $this->getApi()->editCredit($uid, $cType, $value, $isset);
		$this->output($result);
	}
	
	/**
	 * 清空一个积分字段
	 * Enter description here ...
	 * @param int $num >8
	 */
	public function clearCreditAction() {
		$result = $this->getApi()->clearCredit($this->getInput('num', 'post'));
		$this->output($result);
	}
	
	/**
	 * 获取用户黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @return array uids
	 */
	public function getBlackAction() {
		$result = $this->getApi()->getBlack($this->getInput('uid', 'get'));
		$this->output($result);
	}
	
	public function fetchBlackAction() {
		$uids = explode('_',$this->getInput('uids', 'get'));
		$result = $this->getApi()->fetchBlack($uids);
		$this->output($result);
	}
	
	/**
	 * 增加黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function addBlackAction() {
		$result = $this->getApi()->addBlack($this->getInput('uid', 'post'), $this->getInput('blackUid', 'post'));
		$this->output($result);
	}
	
	public function replaceBlackAction() {
		$uid = $this->getInput('uid', 'post');
		$blackList = $this->getInput('blackList', 'post');
		$result = $this->getApi()->replaceBlack($uid, $blackList);
		$this->output($result);
	}
	
	/**
	 * 删除某的黑名单 $blackUid为空删除所有
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function delBlackAction() {
		$result = $this->getApi()->delBlack($this->getInput('uid', 'post'), $this->getInput('blackUid', 'post'));
		$this->output($result);
	}
	
	protected function getApi() {
		return new WindidUserApi();
	}
	
	protected function getUserDs() {
		return Windid::load('user.WindidUser');
	}
	
	protected function getBlackDs() {
		return Windid::load('user.WindidUserBlack');
	}
	
	private function _getNotifyClient() {
		return Windid::load('notify.srv.WindidNotifyClient');
	}
	
}
?>