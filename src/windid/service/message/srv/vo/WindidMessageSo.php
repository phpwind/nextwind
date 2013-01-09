<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageSo.php 23072 2013-01-06 02:12:11Z gao.wanggao $ 
 * @package 
 */
class WindidMessageSo {
	
	protected $_data = array();
	
	public function getData() {
		return $this->_data;
	}
	
	public function setFromUid($fromuid) {
		$this->_data['fromuid'] = (int)$fromuid;
	}
	
	public function setToUid($touid) {
		$this->_data['touid'] = (int)$touid;
	}

	public function setKeyword($keyword) {
		$this->_data['keyword'] = $keyword;
	}
	
	public function setStarttime($starttime) {
		$this->_data['starttime'] = $starttime;
	}
	
	public function setEndTime($time) {
		$this->_data['endtime'] = $time;
	}

}
?>