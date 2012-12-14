<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifySynDao.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package 
 */
class WindidNotifySynDao extends WindidBaseDao {

	protected $_table = 'windid_notify_syn';
	protected $_pk = 'id';
	protected $_dataStruct = array('uid', 'appid','syntype', 'created_time');
	
	/**
	 * 根据ID获取信息
	 *
	 * @param int $nid
	 * @return array|boolean
	 */
	public function get($id) {
		return $this->_get($id);
	}
	
	public function add($data) {
		return $this->_add($data, true);
	}
	
	
	public function update($id, $data) {
		return $this->_update($id, $data);
	}
	
	public function delete($id) {
		return $this->_delete($id);
	}
	
	
	public function deleteByTime($time) {
		$sql = $this->_bindTable('DELETE FROM %s WHERE `created_time` < ? ');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($time));
	}
}

?>