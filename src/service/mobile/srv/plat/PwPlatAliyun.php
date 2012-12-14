<?php
Wind::import('APPS:appcenter.service.srv.helper.PwApplicationHelper');

/**
 * 阿里云短信平台
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class PwPlatAliyun {

	/**
	 * 获取剩余短信数量
	 *
	 * @return int
	 */
	public function getRestMobileMessage() {
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'getSiteLastNum'));
		$info = PwApplicationHelper::requestAcloudData($url);

		if ($info['code'] !== '0') return new PwError($info['msg']);
		
		return $info['info'];
	}

	/**
	 * 发送短信
	 *
	 * @return bool
	 */
	public function sendMobileMessage($mobile, $code) {
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'sendSms', 'mobile' => $mobile, 'content' => $code));
		$info = PwApplicationHelper::requestAcloudData($url);
	    if ($info['code'] !== '0') return new PwError($info['msg']);
		
		return true;
	}
}
?>