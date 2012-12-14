<?php
Wind::import('WINDID:api.open.OpenBaseApi');
Wind::import('WINDID:api.local.WindidUserApi');
/**
 * windid用户接口
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: UserApi.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class UserApi extends OpenBaseApi {

	public function register() {
		$username = $this->getInput('username');
		$email = $this->getInput('email');
		$password = $this->getInput('password');
		$question = $this->getInput('question');
		$answer = $this->getInput('answer');
		$regip = $this->getInput('regip');
		return $this->getApi()->register($username, $email, $password, $question, $answer, $regip);
	}
	
	public function login() {
		$userid = $this->getInput('userid');
		$password = $this->getInput('password');
		$type = $this->getInput('type');
		$ifcheck = $this->getInput('ifcheck');
		$question = $this->getInput('question');
		$answer = $this->getInput('answer');
		return $this->getApi()->login($userid, $password, $type, $ifcheck, $question, $answer);
	}

	public function synLogin() {
		return $this->getApi()->synLogin($this->getInput('uid'));
	}
	
	public function synLogout() {
		return $this->getApi()->synLogout($this->getInput('uid'));
	}
	
	public function checkUserInput() {
		$input = $this->getInput('input');
		$type = $this->getInput('type');
		$username = $this->getInput('username');
		$uid = $this->getInput('uid');
		return $this->getApi()->checkUserInput($input, $type, $username, $uid);
	}
	
	public function checkQuestion() {
		$question = $this->getInput('question');
		$answer = $this->getInput('answer');
		$uid = $this->getInput('uid');
		return $this->getApi()->checkQuestion($uid, $question, $answer);
	}
	
	public function getUser() {
		return $this->getApi()->getUser($this->getInput('userid'), $this->getInput('type'));
	}

	public function getUserInfo() {
		return $this->getApi()->getUserInfo($this->getInput('userid'), $this->getInput('type'));
	}
	
	/**
	 * 批量获取用户信息
	 */
	public function fecthUserInfo() {
		$uids = explode('_',$this->getInput('uids'));
		return $this->getApi()->getUserInfo($uids, $this->getInput('type'));
	}
	
	/**
	 * 修改用户基本信息
	 */
	public function editUser() {
		$uid = $this->getInput('uid');
		$password = $this->getInput('password');
		$editInfo['username'] = $this->getInput('username');
		$editInfo['newpassword'] = $this->getInput('newpassword');
		$editInfo['email'] = $this->getInput('email');
		$editInfo['question'] = $this->getInput('question');
		$editInfo['answer'] = $this->getInput('answer');
		return $this->getApi()->editUser($uid, $password, $editInfo);
	}
	
	
	public function editUserInfo($uid, $editInfo) {
		$uid = $this->getInput('uid');
		$editInfo['realname'] = $this->getInput('realname');
		$editInfo['profile'] = $this->getInput('profile');
		$editInfo['icon'] = $this->getInput('icon');
		$editInfo['gender'] = $this->getInput('gender');
		$editInfo['byear'] = $this->getInput('byear');
		$editInfo['bmonth'] = $this->getInput('bmonth');
		$editInfo['bday'] = $this->getInput('bday');
		$editInfo['hometown'] = $this->getInput('hometown');
		$editInfo['location'] = $this->getInput('location');
		$editInfo['homepage'] = $this->getInput('homepage');
		$editInfo['qq'] = $this->getInput('qq');
		$editInfo['aliww'] = $this->getInput('aliww');
		$editInfo['mobile'] = $this->getInput('mobile');
		$editInfo['alipay'] = $this->getInput('alipay');
		$editInfo['msn'] = $this->getInput('msn');
		return $this->getApi()->editUserInfo($uid, $editInfo);
	}
	
	/**
	 * 删除一个用户
	 */
	public function deleteUser() {
		return $this->getApi()->deleteUser($this->getInput('uid'));
	}
	
	public function batchDeleteUser() {
		$uids = explode('_',$this->getInput('uids'));
		return $this->getApi()->batchDeleteUser($uids);
	}
	
	/**
	 * 获取用户未读消息条数
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	public function getMessageNum() {
		return $this->getApi()->getMessageNum($this->getInput('uid'));
	}
	
	/**
	 * 更新消息数
	 * Enter description here ...
	 * @param int $uid
	 * @param int $num
	 */
	public function editMessageNum() {
		return $this->getApi()->editMessageNum($this->getInput('uid'), $this->getInput('num'));
	}
	
	/**
	 * 获取用户积分
	 * Enter description here ...
	 * @param int $uid
	 */
	public function getUserCredit() {
		return $this->getApi()->getUserCredit($this->getInput('uid'));
	}
	
	/**
	 * 批量获取用户积分
	 * Enter description here ...
	 * @param array $uids
	 * @return array
	 */
	public function fecthUserCredit() {
		$uids = explode('_',$this->getInput('uids'));
		return $this->getApi()->fecthUserCredit($uids);
	}
	
	/**
	 * 更新用户积分
	 * Enter description here ...
	 * @param int $uid
	 * @param int $cType (1-8)
	 * @param int $value
	 */
	public function editCredit() {
		return $this->getApi()->editCredit($this->getInput('uid'), $this->getInput('cType'), $this->getInput('value'));
	}
	
	/**
	 * 清空一个积分字段
	 * Enter description here ...
	 * @param int $num >8
	 */
	public function clearCredit() {
		return $this->getApi()->clearCredit($this->getInput('num'));
	}
	
	/**
	 * 获取用户黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @return array uids
	 */
	public function getBlack() {
		return $this->getApi()->getBlack($this->getInput('uid'));
	}
	
	public function fetchBlack() {
		$uids = explode('_',$this->getInput('uids'));
		return $this->getApi()->fetchBlack($uids);
	}
	
	/**
	 * 增加黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function addBlack() {
		return $this->getApi()->addBlack($this->getInput('uid'), $this->getInput('blackUid'));
	}
	
	/**
	 * 删除某的黑名单 $blackUid为空删除所有
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function delBlack() {
		return $this->getApi()->delBlack($this->getInput('uid'), $this->getInput('blackUid'));
	}
	
	protected function getApi() {
		return new WindidUserApi();
	}
	
}
?>