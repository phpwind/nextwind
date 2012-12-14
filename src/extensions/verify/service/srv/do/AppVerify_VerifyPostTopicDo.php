<?php
Wind::import('SRV:forum.srv.post.do.PwPostDoBase');
Wind::import('EXT:verify.service.AppVerify_Verify');

/**
 * 实名认证 - 发帖扩展
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyPostTopicDo extends PwPostDoBase { 
	
	protected $loginUser;
	
	public function __construct(PwPost $pwpost) {
		$this->loginUser = $pwpost->user;
	}
	
	public function check($postDm) {
		if (($result = $this->_getService()->checkVerifyRights($this->loginUser->uid, AppVerify_Verify::RIGHT_POSTTOPIC)) instanceof PwError) {
			return $result;
		}
		return true;
	}
	
	/**
	 * @return AppVerify_VerifyService
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.AppVerify_VerifyService');
	}
}
?>