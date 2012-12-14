<?php

Wind::import('WINDID:library.base.WindidBaseDm');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyLogDm.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package 
 */
class WindidNotifyLogDm extends WindidBaseDm {
	
	public $logid;
	
	public function __construct($logid = null) {
		isset($logid) && $this->logid = $logid;
	}
	
	public function setNid($nid) {
		$this->_data['nid'] = intval($nid);
		return $this;
	}
	
	public function setAppid($appid) {
		$this->_data['appid'] = intval($appid);
		return $this;
	}
	
	public function setComplete($complete) {
		$this->_data['complete'] = intval($complete);
		return $this;
	}
	
	public function setSendNum($num) {
		$this->_data['send_num'] = intval($num);
		return $this;
	}
	
	public function setReason($reason) {
		$this->_data['reason'] = $reason;
		return $this;
	}
	
	public function setIncreaseSendNum($num) {
		$this->_increaseData['send_num'] = intval($num);
		return $this;
	}

	protected function _beforeAdd() {

		return true;
	}

	protected function _beforeUpdate() {
		
		return true;
	}
}