<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 实名认证搜索
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifySo {
	
	protected $_data = array();
	
	
	/** 
	 * 设置uid
	 *
	 * @param array $uids
	 * @return AppVerify_VerifyDm
	 */
	public function setUid($uids) {
		$this->_data['uid'] = (array)$uids;
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
	 * @param int $type
	 * @return AppVerify_VerifyDm
	 */
	public function setType($type) {
		$this->_data['type'] = intval($type);
		return $this; 
	}
	
	public function getData() {
		return $this->_data;
	}
}