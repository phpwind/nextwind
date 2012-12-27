<?php
/**
 * windid用户接口
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserApi.php 22709 2012-12-26 12:00:08Z gao.wanggao $ 
 * @package 
 */
class WindidUserApi {
	
	public function register($username, $email, $password, $question = '', $answer = '', $regip = '') {
		$params = array(
			'username'=>$username,
			'email'=>$email,
			'password'=>$password,
			'question'=>$question,
			'answer'=>$answer,
			'regip'=>$regip,
		);
		return WindidApi::open('user/register', array(), $params);
	}
	
	public function login($userid, $password, $type = 2, $ifcheck = false, $question = '', $answer = '') {
		$params = array(
			'userid'=>$userid,
			'password'=>$password,
			'type'=>$type,
			'ifcheck'=>$ifcheck,
			'question'=>$question,
			'answer'=>$answer,
		);
		return WindidApi::open('user/login', array(), $params);
	}
	
	public function synLogin($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('user/synLogin', array(), $params);
	}
	

	public function synLogout($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('user/synLogout',  array(),$params);
	}
	
	public function checkUserInput($input, $type, $username = '', $uid = 0) {
		$params = array(
			'input'=>$input,
			'type'=>$type,
			'username'=>$username,
			'uid'=>$uid,
		);
		return WindidApi::open('user/checkInput', array(), $params);
	}
	
	public function checkQuestion($uid, $question, $answer) {
		$params = array(
			'uid'=>$uid,
			'question'=>$question,
			'answer'=>$answer,
		);
		return WindidApi::open('user/checkQuestion', array(),$params);
	}
	

	public function getUser($userid, $type = 1) {
		$params = array(
			'userid'=>$userid,
			'type'=>$type,
		);
		return WindidApi::open('user/get', $params);
	}
	
	/**
	 * 获取一个用户详细资料
	 * Enter description here ...
	 * @param multi $userid
	 * @param int $type 1-uid ,2-username 3-email
	 * @return array
	 */
	public function getUserInfo($userid, $type = 1) {
		$params = array(
			'userid'=>$userid,
			'type'=>$type,
		);
		return WindidApi::open('user/getInfo', $params);
	}
	
	/**
	 * 批量获取用户信息
	 * Enter description here ...
	 * @param array $uids/$username
	 * @param int $type 1-uid ,2-username
	 * @return array
	 */
	public function fecthUserInfo($uids, $type = 1) {
		$params = array(
			'uids'=>implode('_', $uids),
			'type'=>$type,
		);
		return WindidApi::open('user/fecthInfo', $params);
	}
	
	/**
	 * 修改用户基本信息
	 * Enter description here ...
	 * @param int $uid
	 * @param string $password
	 * @param array $editInfo  array('username', 'password', 'email', 'question', 'answer')
	 */
	public function editUser($uid, $password, $editInfo) {
		if (!is_array($editInfo)) return false;
		$params = array(
			'uid'=>$uid,
			'password'=>$password,
		);
		$allow = array('username', 'password', 'email', 'question', 'answer');
		foreach ($editInfo AS $key=>$info) {
			if (!in_array($key, $allow)) continue;
			$params[$key] = $info;
		}
		return WindidApi::open('user/edit', array(),$params);
	}
	
	/**
	 * 修改用户资料
	 * Enter description here ...
	 * @param int $uid
	 * @param array $editInfo
	 */
	public function editInfo($uid, $editInfo) {
		if(!is_array($editInfo)) return false;
		$params = array(
			'uid'=>$uid,
		);
		$allow = array('realname','icon', 'gender', 'byear', 'bmonth','bday', 'hometown', 'location', 'homepage', 'qq', 'aliww', 'mobile',	'alipay', 'msn','profile');
		foreach ($editInfo AS $key=>$info) {
			if (!in_array($key, $allow)) continue;
			$params[$key] = $info;
		}
		return WindidApi::open('editUserInfo',array(), $params);
	}
	
	/**
	 * 删除一个用户
	 * Enter description here ...
	 * @param int $uid
	 */
	public function deleteUser($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('user/delete', array(),$params);
	}
	
	public function batchDeleteUser($uids) {
		$params = array(
			'uids'=>implode('_', $uids),
		);
		return WindidApi::open('user/batchDelete', array(),$params);
	}
	
	/**
	 * 添加用户对象接口，使用前必须使用WidnidApi::getDm('user') 设置数据
	 * Enter description here ...
	 * @param WindidUserDm $dm
	 */
	public function addDmUser($dm) {
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		if (!$dm instanceof WindidUserDm) return WindidError::CLASS_ERROR;
		$_baseInfo = $_extInfo = array();
		$_data = $dm->getData();
		$userBase = array('username', 'password', 'email', 'question', 'answer', 'regip', 'regdate');
		foreach ($_data AS $k=>$v) {
			if (in_array($k, $userBase)) {
				$_baseInfo[$k] = $v;
				continue;
			}
			$_extInfo[$k] = $v;
		}
		if (!$_baseInfo) return WindidError::FAIL;
		$uid =  WindidApi::open('user/register', array(),$_baseInfo);
		if ($uid < 1) return $uid;
		if ($_extInfo && $uid > 0) {
			$_extInfo['uid'] = $uid;
			$result =  WindidApi::open('user/editInfo', array(),$_extInfo);
			if ($result < 1) return $result;
		}
		return $uid;
	}
	
	public function editDmUser($dm) {
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		if (!$dm instanceof WindidUserDm) return WindidError::CLASS_ERROR;
		$_baseInfo = $_extInfo = array();
		$_data = $dm->getData();
		$userBase = array('username', 'password', 'email', 'question', 'answer');
		foreach ($_data AS $k=>$v) {
			if (in_array($k, $userBase)) {
				$_baseInfo[$k] = $v;
				continue;
			}
			$_extInfo[$k] = $v;
		}
		if ($_baseInfo && isset($_data['old_password'])) {
			$_baseInfo['uid'] = $dm->uid;
			$_baseInfo['password'] = $_data['old_password'];
			$_baseInfo['newpassword'] = $_data['password'];
			$result = WindidApi::open('user/edit',array(), $_baseInfo);
			if ($result < 1) return $result;
		}
		
		if ($_extInfo) {
			$_extInfo['uid'] = $dm->uid;
			$result = WindidApi::open('user/editInfo', array(),$_extInfo);
		}
		return $result;
	}
	

	
	/**
	 * 获取用户积分
	 * Enter description here ...
	 * @param int $uid
	 */
	public function getUserCredit($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('user/getCredit', $params);
	}
	
	/**
	 * 批量获取用户积分
	 * Enter description here ...
	 * @param array $uids
	 * @return array
	 */
	public function fecthUserCredit($uids) {
		$params = array(
			'uids'=>implode('_', $uids),
		);
		return WindidApi::open('user/fecthCredit', $params);
	}
	
	/**
	 * 更新用户积分
	 * Enter description here ...
	 * @param int $uid
	 * @param int $cType (1-8)
	 * @param int $value
	 */
	public function editCredit($uid, $cType, $value, $isset = false) {
		$params = array(
			'uid'=>$uid,
			'cType'=>$cType,
			'value'=>$value,
			'isset'=>$isset,
		);
		return WindidApi::open('user/editCredit',array(), $params);
	}
	
	public function editDmCredit($dm) {
		Wind::import('WINDID:service.user.dm.WindidCreditDm');
		if (!$dm instanceof WindidCreditDm) return WindidError::CLASS_ERROR;
		$data = $dm->getData();
		$increase = $dm->getIncreaseData();
		$tmp = $dm->getTmp();
		if ($data) {
			$params = array(
				'uid'=>$dm->uid,
				'cType'=>$data['field'],
				'value'=>$data['credit'],
				'isset'=>true,
			);
		} else {
			$params = array(
				'uid'=>$dm->uid,
				'cType'=>$tmp['field'],
				'value'=>$tmp['credit'],
				'isset'=>false,
			);
		}
		return WindidApi::open('user/editCredit',array(), $params);
	}
	
	
	/**
	 * 清空一个积分字段
	 * Enter description here ...
	 * @param int $num >8
	 */
	public function clearCredit($num) {
		$params = array(
			'num'=>$num,
		);
		return WindidApi::open('user/clearCredit',array(), $params);
	}
	
	/**
	 * 获取用户黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @return array uids
	 */
	public function getBlack($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('user/getBlack', $params);
	}
	
	public function fetchBlack($uids) {
		$params = array(
			'uids'=>implode('_', $uids),
		);
		return WindidApi::open('user/fetchBlack', $params);
	}
	
	/**
	 * 增加黑名单
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function addBlack($uid, $blackUid) {
		$params = array(
			'uid'=>$uid,
			'blackUid'=>$blackUid,
		);
		return WindidApi::open('user/addBlack', array(),$params);
	}
	
	public function replaceBlack($uid, $blackList) {
		$params = array(
			'uid'=>$uid,
			'blackList'=>$blackList,
		);
		return WindidApi::open('user/replaceBlack', array(),$params);
	}
	
	/**
	 * 删除某的黑名单 $blackUid为空删除所有
	 * Enter description here ...
	 * @param int $uid
	 * @param int $blackUid
	 */
	public function delBlack($uid, $blackUid = '') {
		$params = array(
			'uid'=>$uid,
			'blackUid'=>$blackUid,
		);
		return WindidApi::open('user/delBlack', array(),$params);
	}
}
?>