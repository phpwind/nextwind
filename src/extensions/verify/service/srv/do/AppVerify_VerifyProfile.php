<?php
Wind::import('APPS:.profile.service.PwProfileExtendsDoBase');
/**
 * 个人设置 - 实名认证
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyProfile extends PwProfileExtendsDoBase {
	protected $user;
	
	public function __construct(PwUserProfileExtends $bp = null) {
		$this->user = $bp->user;
	}
	
	public function createHtml($left, $tab) {
		$verify = $this->_getDs()->getVerify($this->user->uid);
		$types = $this->_getDs()->getOpenVerifyType();
		$typeNames = $this->_getDs()->getVerifyTypeName();
		$haveVerify = $noVerify = array();
		foreach ($types as $k => $v) {
			if (Pw::getstatus($verify['type'], $k)) {
				$haveVerify[$typeNames[$k]] = $v;
			} else {
				$noVerify[$typeNames[$k]] = $v;
			}
		}

		$conf = Wekit::C('appVerify');
		$rightType = $this->_getDs()->getRightType();
		PwHook::template('displayAppProfileVerify', 'EXT:verify.template.index_run', true, $rightType, $types, $haveVerify, $noVerify, $conf);
	}
	
	public function displayFootHtml($current) {
		$this->conf = Wekit::C('appVerify');
		if (!$this->conf['verify.isopen']) {
			return false;
		}
		$openTypes = $this->_getDs()->getOpenVerifyType();
		$types = $this->_getDs()->getVerifyTypeName();
		$data = array();
		foreach ($openTypes as $k => $v) {
			$data[$types[$k]] = WindUrlHelper::createUrl('app/verify/index/typeTab?type='.$types[$k]);
		}
		
		PwHook::template('displayAppProfileVerifyFootHtml', 'EXT:verify.template.index_run', true, $current, $data);
	}
	
	/**
	 * @return AppVerify_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.AppVerify_Verify');
	}
}
