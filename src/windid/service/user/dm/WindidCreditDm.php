<?php

Wind::import("WINDID:service.user.validator.WindidUserValidator");
Wind::import('WINDID:library.base.WindidBaseDm');
/**
 * 用户信息数据模型
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidCreditDm.php 22114 2012-12-19 08:18:05Z gao.wanggao $
 * @package windid.service.user.dm
 */
class WindidCreditDm extends WindidBaseDm {
	
	public $uid;
	private $_tmpData = array();
	
	public function __construct($uid) {
		$this->uid = $uid;
	}
	
	public function getTmp() {
		return $this->_tmpData;
	}

	public function addCredit($cType, $value) {
		if (!$this->_isLegal($cType) || $value == 0) return;
		$this->_increaseData['credit' . $cType] = $value;
		$this->_tmpData['field'] =  $cType;
		$this->_tmpData['credit'] = $value;
		return $this;
	}

	public function setCredit($cType, $value) {
		if (!$this->_isLegal($cType)) return;
		$this->_data['credit' . $cType] =  $value;
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see WindidBaseDm::beforeAdd()
	 */
	protected function _beforeAdd() {
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see WindidBaseDm::beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if (!$this->uid) {
			return false;
		}
		if (empty($this->_data) && empty($this->_increaseData)) {
			return false;
		}
		return true;
	}

	private function _isLegal(&$key) {
		$key = intval($key);
		return $key >= 1 && $key <= 8;
	}
}