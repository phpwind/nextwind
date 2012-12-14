<?php
Wind::import('APPS:.profile.service.PwUserProfileExtends');
Wind::import('EXT:verify.service.srv.do.AppVerify_VerifyProfile');

/**
 * 帖子发布 - 话题相关
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyProfileInjector extends PwBaseHookInjector {
	
	public function createHtml() {
		$user = Wekit::getLoginUser();
		$bp = new PwUserProfileExtends($user);
		return new AppVerify_VerifyProfile($bp);
	}
	
	public function displayFootHtml() {
		$user = Wekit::getLoginUser();
		$bp = new PwUserProfileExtends($user);
		return new AppVerify_VerifyProfile($bp);
	}

}