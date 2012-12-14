<?php
Wind::import('EXT:verify.service.AppVerify_Verify');

/**
 * Enter description here ...
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package file
 */

class AppVerify_VerifyDo {
	
	/**
	 * 实名认证 - 用户头像
	 *
	 * @param int $uid
	 * @return boolean
	 */
	public function uploadAvatar($uid) {
		return $this->_getService()->updateVerifyInfo($uid, AppVerify_Verify::VERIFY_AVATAR);
	}
	
	/**
	 * 实名认证 - 真实姓名
	 *
	 * @param int $uid
	 * @return boolean
	 */
	public function uploadRealName($uid) {
		return $this->_getService()->updateVerifyInfo($uid, AppVerify_Verify::VERIFY_REALNAME);
	}
	
	/**
	 * @return AppVerify_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.AppVerify_Verify');
	}
	
	/**
	 * @return AppVerify_VerifyService
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.AppVerify_VerifyService');
	}
}
?>