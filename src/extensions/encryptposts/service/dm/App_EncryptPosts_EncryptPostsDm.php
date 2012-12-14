<?php
defined('WEKIT_VERSION') or exit(403);
/**
 * App_EncryptPosts_EncryptpostsDm - 数据模型
 *
 * @author fengxiao <xiao.fengx@alibaba-inc.com>
 * @copyright www.phpwind.net
 * @license www.phpwind.net
 */
class App_EncryptPosts_EncryptPostsDm extends PwBaseDm {
	

	protected $_data = array();

	public function setTid($tid){
		$this->_data['tid'] = intval($tid);
		return $this;
	}


	public function setToken($token){
		$this->_data['token'] = $token;
		return $this;
	}


	protected function _beforeAdd() {
		if(empty($this->_data['tid'])) return false;
		if(empty($this->_data['token'])) return false;
		return true;
	}

	 protected function _beforeUpdate() {
		// TODO Auto-generated method stub
	 	//check the fields value before update
		return true;
	}
}

?>