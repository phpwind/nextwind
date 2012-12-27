<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwWindidInform.php 22634 2012-12-26 05:40:19Z gao.wanggao $ 
 * @package 
 */
class PwWindidInform {
	
	/**
	 * 用于通讯测试，传递参数test
	 * Enter description here ...
	 * @param unknown_type $params
	 */
	public function test($uid) {
		return $uid ? true : false;
	}
	
	public function synLogin($uid) {
		Wind::import('SRC:service.user.bo.PwUserBo');
		Wind::import('SRC:service.user.srv.PwLoginService');
		$userBo = new PwUserBo($uid);
		$srv = new PwLoginService();
		$ip = Wind::getApp()->getRequest()->getClientIp();
		$srv->welcome($userBo, $ip);
		return true;
	}
	
	public function synLogout($uid) {
		Wind::import('SRC:service.user.srv.PwUserService');
		$srv = new PwUserService();
		$srv->logout();
		return true;
	}
	
	public function addUser($uid) {
		Wind::import('SRC:service.user.srv.PwLoginService');
		$srv = new PwLoginService();
		$result = $srv->sysUser($uid);
		if ($result instanceof PwError) return $result->getError();
		return true;
	}
	
	public function editUser($uid) {
		$result = $this->_getUserDs()->synEditUser($uid);
		return $result;
	}
	
	public function editUserInfo($uid) {
		$result = $this->_getUserDs()->synEditUser($uid);
		return $result;
	}
	
	public function uploadAvatar($uid) {
		PwSimpleHook::getInstance('update_avatar')->runDo($uid);
		return true;
	}
	
	public function editCredit($uid) {
		$result = $this->_getUserDs()->synEditUser($uid);
		return $result;
	}
	
	public function editMessageNum($uid) {
		$result = Wekit::load('message.srv.PwMessageService')->synEditUser($uid);
		return $result;
	}
	
	public function deleteUser($uid) {
		Wind::import('SRV:user.srv.PwClearUserService');
		$userSer = new PwClearUserService($uid);
		$clear = $userSer->getClearTypes();
		$std = PwWindidStd::getInstance('user');
		$std->setMethod('deleteUser', $uid);
		$result = $userSer->run(array_keys($clear));
		if ($result instanceof PwError) return false;
		return true;
	}
	
	private function _getUserDs() {
		return Wekit::load('user.PwUser');
	}
}
?>