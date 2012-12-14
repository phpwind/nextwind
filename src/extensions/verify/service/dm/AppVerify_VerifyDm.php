<?php
Wind::import('EXT:verify.service.AppVerify_VerifyCheck');

/**
 * 认证DM
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyDm extends PwBaseDm {
	
	public $uid;
	
	public function __construct($uid = 0) {
		$uid && $this->uid = $uid;
	}
	
	/** 
	 * 设置uid
	 *
	 * @param int $uid
	 * @return AppVerify_VerifyDm
	 */
	public function setUid($uid) {
		$this->_data['uid'] = intval($uid);
		return $this;
	}
	
	/** 
	 * 设置用户名
	 *
	 * @param string $username
	 * @return AppVerify_VerifyDm
	 */
	public function setUsername($username) {
		$this->_data['username'] = trim($username);
		return $this; 
	}
	
	/** 
	 * 设置类型
	 *
	 * @param string $username
	 * @return AppVerify_VerifyDm
	 */
	public function setType($type) {
		$this->_data['type'] = intval($type);
		return $this; 
	}
	
	/** 
	 * 设置increase类型
	 *
	 * @param int $type
	 * @return AppVerify_VerifyDm
	 */
	public function updateType($type) {
		$this->_increaseData['type'] = intval($type);
		return $this; 
	}
	
	/** 
	 * 设置位运算类型
	 *
	 * @param string $username
	 * @return AppVerify_VerifyDm
	 */
	public function setBitType($type, $bool) {
		$this->_bitData['type'][$type] = (bool)$bool;
		return $this; 
	}
	
	/** 
	 * 设置真实姓名
	 *
	 * @param int $bool
	 * @return AppVerify_VerifyDm
	 */
	public function setRealName($bool) {
		$this->_bitData['type'][AppVerify_Verify::VERIFY_REALNAME] = (bool)$bool;
		return $this; 
	}
	
	/** 
	 * 设置头像
	 *
	 * @param int $bool
	 * @return AppVerify_VerifyDm
	 */
	public function setAvatar($bool) {
		$this->_bitData['type'][AppVerify_Verify::VERIFY_AVATAR] = (bool)$bool;
		return $this; 
	}
	
	/** 
	 * 设置邮箱
	 *
	 * @param int $bool
	 * @return AppVerify_VerifyDm
	 */
	public function setEmail($bool) {
		$this->_bitData['type'][AppVerify_Verify::VERIFY_EMAIL] = (bool)$bool;
		return $this; 
	}
	
	/** 
	 * 设置支付宝
	 *
	 * @param int $bool
	 * @return AppVerify_VerifyDm
	 */
	public function setAlipay($bool) {
		$this->_bitData['type'][AppVerify_Verify::VERIFY_ALIPAY] = (bool)$bool;
		return $this; 
	}
	
	/** 
	 * 设置手机
	 *
	 * @param int $bool
	 * @return AppVerify_VerifyDm
	 */
	public function setMobile($bool) {
		$this->_bitData['type'][AppVerify_Verify::VERIFY_MOBILE] = (bool)$bool;
		return $this; 
	}
	
	/** 
	 * 设置数据
	 *
	 * @param object $data
	 * @return AppVerify_VerifyDm
	 */
	public function setData($data) {
		$this->_data['data'] = $data;
		return $this; 
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		$this->_data['created_time'] = Pw::getTime();
		return true;
	}

	/* (non-PHPdoc)
	 * @see PwBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		return true;
	}
}