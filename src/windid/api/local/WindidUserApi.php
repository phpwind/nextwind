<?php
/**
 * windid用户接口
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserApi.php 23441 2013-01-09 11:30:49Z gao.wanggao $ 
 * @package 
 */
class WindidUserApi {
	
	/**
	 * 用户注册
	 * Enter description here ...
	 * @param string $username
	 * @param string $email
	 * @param string $password
	 * @param string $question
	 * @param string $answer
	 * @param string $regip
	 * @return int
	 */
	public function register($username, $email, $password, $question = '', $answer = '', $regip = '') {
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		$dm = new WindidUserDm();
		$dm->setUsername($username)->setEmail($email)->setPassword($password)->setQuestion($question)->setAnswer($answer)->setRegip($regip);
		$result = $this->_getUserDs()->addUser($dm);
		if ($result instanceof WindidError) return $result->getCode();
		$uid = (int)$result;
		$client = Windid::client();
		if ($client->windid == 'local') {
			$srv = Windid::load('user.srv.WindidUserService');
			$result = $srv->defaultAvatar($uid, 'face');
		} else {
			$params = array(
				'uid'=>$uid,
				'type'=>'face',
			);
			WindidApi::open('avatar/default', array(), $params);
		}
		$this->_getNotifyClient()->send('register', $uid);
		return $uid;
	}
	
	/**
	 * 用户登录
	 * Enter description here ...
	 * @param string $userid
	 * @param string $password
	 * @param int $type $type 1-uid ,2-username 3-email
	 * @param string $question
	 * @param string $answer
	 * @return array
	 */
	public function login($userid, $password, $type = 2, $ifcheck = false, $question = '', $answer = '') {
		$user = array();
		$ds = $this->_getUserDs();
		switch ($type){
			case 1:
				$user = $ds->getUserByUid($userid, WindidUser::FETCH_MAIN);
				break;
			case 2:
				$user = $ds->getUserByName($userid, WindidUser::FETCH_MAIN);
				break;
			case 3:
				$user = $ds->getUserByEmail($userid, WindidUser::FETCH_MAIN);
				break;
		}
		if (!$user) return array(WindidError::USER_NOT_EXISTS);
		if ($ifcheck) {
			$safecv = WindidUtility::buildQuestion($question, $answer);
			if ($safecv != $user['safecv']) return array(WindidError::SAFECV_ERROR, $user);
		}
		if (WindidUtility::buildPassword($password, $user['salt']) !== $user['password']) {
			return array(WindidError::PASSWORD_ERROR, $user);
		}
		return array(1, $user);
	}
	
	/**
	 * 本地登录成功后同步登录通知
	 * 
	 * @param int $uid
	 * @param string $backurl
	 * @return string
	 */
	public function synLogin($uid) {
		$out = '';
		$result = $this->_getNotifyClient()->syn('synLogin', $uid);
		foreach ($result AS $val) {
			$out .= '<script type="text/javascript" src="'.$val.'"></script>';
		}
		return $out;
	}
	
	/**
	 * 本地登出成功后同步登出
	 * Enter description here ...
	 * @param int $uid
	 * @param string $backurl
	 * @return string
	 */
	public function synLogout($uid) {
		$out = '';
		$result = $this->_getNotifyClient()->syn('synLogout', $uid);
		foreach ($result AS $val) {
			$out .= '<script type="text/javascript" src="'.$val.'"></script>';
		}
		return $out;
	}
	
	/**
	 * 检查用户提交的信息是否符合windid配置规范
	 * Enter description here ...
	 * @param string $input
	 * @param int $type 综合检查类型： 1-用户名, 2-密码,  3-邮箱
	 * @param int $uid
	 * @return bool
	 */
	public function checkUserInput($input, $type, $username = '', $uid = 0) {
		Wind::import('WINDID:service.user.validator.WindidUserValidator');
		switch ($type) {
			case 1:
				$result = WindidUserValidator::checkName($input, $uid, $username);
				break;
			case 2:
				$result = WindidUserValidator::checkPassword($input);
				break;
			case 3:
				$result = WindidUserValidator::checkEmail($input, $uid, $username);
				break;
			default:
				return WindidError::FAIL;
		}
		if ($result instanceof WindidError) return $result->getCode();
		return WindidError::SUCCESS;
	}
	
	/**
	 * 验证安全问题
	 * Enter description here ...
	 * @param int $uid
	 * @param int $question
	 * @param int $answer
	 * @return bool
	 */ 
	public function checkQuestion($uid, $question, $answer) {
		$user = $this->_getUserDs()->getUserByUid($uid, WindidUser::FETCH_MAIN);
		if ($user){
			$safecv = WindidUtility::buildQuestion($question, $answer);
			if ($safecv == $user['safecv']) return WindidError::SUCCESS;;
		}
		return WindidError::FAIL;
	}
	
	/**
	 * 获取一个用户基本资料
	 * Enter description here ...
	 * @param multi $userid
	 * @param int $type 1-uid ,2-username 3-email
	 * @return array
	 */
	public function getUser($userid, $type = 1) {
		$user = array();
		$ds = $this->_getUserDs();
		switch ($type){
			case 1:
				$user = $ds->getUserByUid($userid, WindidUser::FETCH_MAIN);
				break;
			case 2:
				$user = $ds->getUserByName($userid, WindidUser::FETCH_MAIN);
				break;
			case 3:
				$user = $ds->getUserByEmail($userid, WindidUser::FETCH_MAIN);
				break;
		}
		unset($user['salt']);//TODO 需要提供一个判断是否设置了安全问题的方法或是怎么兼容  xiaoxia, $user['safecv']);
		return $user;
	}
	
	/**
	 * 获取一个用户详细资料
	 * Enter description here ...
	 * @param multi $userid
	 * @param int $type 1-uid ,2-username 3-email
	 * @return array
	 */
	public function getUserInfo($userid, $type = 1) {
		$user = array();
		$ds = $this->_getUserDs();
		switch ($type){
			case 1:
				$user = $ds->getUserByUid($userid, WindidUser::FETCH_MAIN|WindidUser::FETCH_INFO);
				break;
			case 2:
				$user = $ds->getUserByName($userid, WindidUser::FETCH_MAIN|WindidUser::FETCH_INFO);
				break;
			case 3:
				$user = $ds->getUserByEmail($userid, WindidUser::FETCH_MAIN|WindidUser::FETCH_INFO);
				break;
		}
		if ($user) unset($user['password'], $user['salt'], $user['safecv']);
		return $user;
		
	}
	
	/**
	 * 批量获取用户信息
	 * Enter description here ...
	 * @param array $uids/$username
	 * @param int $type 1-uid ,2-username
	 * @return array
	 */
	public function fecthUserInfo($uids, $type = 1) {
		$users = array();
		$ds = $this->_getUserDs();
		switch ($type){
			case 1:
				$_data = $ds->fetchUserByUid($uids, WindidUser::FETCH_MAIN|WindidUser::FETCH_INFO);
				foreach ($_data AS $key=>&$user) {
					unset($user['password'], $user['salt'], $user['safecv']);
					$users[$key] = $_data[$key];
				}
				break;
			case 2:
				$users = $ds->fetchUserByName($uids, WindidUser::FETCH_INFO);
				break;
		}
		return $users;
	}
	
	/**
	 * 修改用户基本信息
	 * Enter description here ...
	 * @param int $uid
	 * @param string $password
	 * @param array $editInfo  array('username', 'password', 'email', 'question', 'answer')
	 */
	public function editUser($uid, $password, $editInfo) {
		if(!is_array($editInfo)) $editInfo = array($editInfo);
		$user = $this->_getUserDs()->getUserByUid($uid, WindidUser::FETCH_MAIN);
		if (WindidUtility::buildPassword($password, $user['salt']) != $user['password']) {
			return WindidError::PASSWORD_ERROR;
		}
		$allow = array('username', 'password', 'email', 'question', 'answer');
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		$dm = new WindidUserDm($uid);
		foreach ($editInfo AS $key=>$info) {
			if (!in_array($key, $allow)) continue;
			$fun = 'set'.ucfirst($key);
			$dm->$fun($info);
		}
		$result = $this->_getUserDs()->editUser($dm);
		if ($result instanceof WindidError) return $result->getCode();
		$this->_getNotifyClient()->send('editUser', $uid);
		return  (int)$result;
	}
	
	/**
	 * 修改用户资料
	 * Enter description here ...
	 * @param int $uid
	 * @param array $editInfo
	 */
	public function editUserInfo($uid, $editInfo) {
		if(!is_array($editInfo)) $editInfo = array($editInfo);
		$allow = array('realname','icon', 'gender', 'byear', 'bmonth','bday', 'hometown', 'location', 'homepage', 'qq', 'aliww', 'mobile',	'alipay', 'msn','profile');
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		$dm = new WindidUserDm($uid);
		foreach ($editInfo AS $key=>$info) {
			if (!in_array($key, $allow)) continue;
			$fun = 'set'.ucfirst($key);
			$dm->$fun($info);
		}
		$result = $this->_getUserDs()->editUser($dm);
		if ($result instanceof WindidError) return $result->getCode();
		$this->_getNotifyClient()->send('editUserInfo', $uid);
		return  (int)$result;
	}
	
	/**
	 * 删除一个用户
	 * Enter description here ...
	 * @param int $uid
	 */
	public function deleteUser($uid) {
		if ($this->_getUserDs()->deleteUser($uid)) {
			$this->_getNotifyClient()->send('deleteUser', $uid);
			return WindidError::SUCCESS;
		} else {
			return WindidError::FAIL;
		}
	}
	
	public function batchDeleteUser($uids) {
		if ($this->_getUserDs()->batchDeleteUser($uids)) {
			foreach ($uids AS $uid) {
				$this->_getNotifyClient()->send('deleteUser', $uid);
			}
			return WindidError::SUCCESS;
		} else {
			return WindidError::FAIL;
		}
	}
	
	/**
	 * 添加用户对象接口，使用前必须使用WidnidApi::getDm('user') 设置数据
	 * Enter description here ...
	 * @param WindidUserDm $dm
	 */
	public function addDmUser($dm) {
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		if (!$dm instanceof WindidUserDm) return WindidError::CLASS_ERROR;
		$result =  $this->_getUserDs()->addUser($dm);
		if ($result instanceof WindidError) return WindidError::FAIL;
		$uid = (int)$result;
		$client = Windid::client();
		if ($client->windid == 'local') {
			$srv = Windid::load('user.srv.WindidUserService');
			$result = $srv->defaultAvatar($uid, 'face');
		} else {	
			$params = array(
				'uid'=>$uid,
				'type'=>'face',
			);
			WindidApi::open('avatar/default', array(), $params);
		}
		$this->_getNotifyClient()->send('register', $uid);
		return $uid;
	}
	
	public function editDmUser($dm) {
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		if (!$dm instanceof WindidUserDm) return WindidError::CLASS_ERROR;
		$result = $this->_getUserDs()->editUser($dm);
		$_data = $dm->getData();
		
		$_baseInfo = false;
		$userBase = array('username', 'password', 'email', 'question', 'answer');
		foreach ($_data AS $k=>$v) {
			if (in_array($k, $userBase)) $_baseInfo = true;
		}
		if ($_baseInfo ) {
			$this->_getNotifyClient()->send('editUser', $dm->uid);
		}
		$this->_getNotifyClient()->send('editUserInfo', $dm->uid);
		return WindidError::SUCCESS;
	}
	
	/**
	 * 获取用户积分
	 * Enter description here ...
	 * @param int $uid
	 */
	public function getUserCredit($uid) {
		$result = $this->_getUserDs()->getUserByUid($uid, WindidUser::FETCH_DATA);
		unset($result['messages']);
		return $result;
	}
	
	/**
	 * 批量获取用户积分
	 * Enter description here ...
	 * @param array $uids
	 * @return array
	 */
	public function fecthUserCredit($uids) {
		$users = array();
		$_data = $this->_getUserDs()->fetchUserByUid($unique, WindidUser::FETCH_DATA);
		foreach ($_data AS $key=>&$user) {
			unset($user['messages']);
			$users[$key] = $_data[$key];
		}
		return $users;
	}
	
	/**
	 * 更新用户积分
	 * Enter description here ...
	 * @param int $uid
	 * @param int $cType (1-8)
	 * @param int $value
	 */
	public function editCredit($uid, $cType, $value, $isset = false) {
		Wind::import('WINDID:service.user.dm.WindidCreditDm');
		$dm = new WindidCreditDm($uid);
		if ($isset) {
			$dm->setCredit($cType, $value);
		} else {
			$dm->addCredit($cType, $value);
		}
		$result = $this->_getUserDs()->updateCredit($dm);
		if ($result instanceof WindidError) return $result->getCode();
		if (!$result) return 0;
		$this->_getNotifyClient()->send('editCredit', $uid);
		return  (int)$result;
	}
	
	public function editDmCredit($dm) {
		Wind::import('WINDID:service.user.dm.WindidCreditDm');
		if (!$dm instanceof WindidCreditDm) return WindidError::CLASS_ERROR;
		$result = $this->_getUserDs()->updateCredit($dm);
		if ($result instanceof WindidError) return $result->getCode();
		if (!$result) return 0;
		$this->_getNotifyClient()->send('editCredit', $dm->uid);
		return  (int)$result;
	}
	
	
	/**
	 * 清空一个积分字段
	 * Enter description here ...
	 * @param int $num >8
	 */
	public function clearCredit($num) {
		$result = $this->_getUserDs()->clearCredit($num);
		return (int)$result;
	}
	
	/**
	 * 获取用户黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @return array uids
	 */
	public function getBlack($uid) {
		return $this->_getUserBlackDs()->getBlacklist($uid);
	}
	
	public function fetchBlack($uids) {
		return $this->_getUserBlackDs()->fetchBlacklist($uids);
	}
	
	/**
	 * 增加黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function addBlack($uid, $blackUid) {
		$result = $this->_getUserBlackDs()->addBlackUser($uid,$blackUid);
		if ($result instanceof WindidError) return $result->getCode();
		return (int)$result;
	}
	
	/**
	 * 批量替换黑名单
	 * Enter description here ...
	 * @param $uid
	 * @param $blackList array
	 */
	public function replaceBlack($uid, $blackList) {
		$result = $this->_getUserBlackDs()->setBlacklist($uid,$blackList);
		if ($result instanceof WindidError) return $result->getCode();
		return (int)$result;
	}
	
	
	/**
	 * 删除某的黑名单 $blackUid为空删除所有
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function delBlack($uid, $blackUid = '') {
		if ($blackUid) {
			$result = $this->_getUserBlackDs()->deleteBlackUser($uid, $blackUid);
		} else {
			$result = $this->_getUserBlackDs()->deleteBlacklist($uid);
		}
		return (int)$result;
	}
	
	private function _getAppDs() {
		return Windid::load('app.WindidApp');
	}
	
	private function _getUserDs() {
		return Windid::load('user.WindidUser');
	}
	
	private function _getUserBlackDs() {
		return Windid::load('user.WindidUserBlack');
	}
	
	private function _getNotifyClient() {
		return Windid::load('notify.srv.WindidNotifyClient');
	}
}
?>