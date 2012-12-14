<?php

/**
 * 实名认证审核Ds
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyCheck {
	
	public function getCheckType() {
		return array(
			self::VERIFY_REALNAME => '真实姓名',
		);
	}
	
	/**
	 * 获取一条数据
	 *
	 * @param int $uid
	 * @return array 
	 */
	public function getVerifyCheck($id) {
		$id = intval($id);
		if ($id < 1) return array();
		return $this->_getDao()->get($id);
	}
	
	/**
	 * 批量获取信息
	 *
	 * @param array $ids
	 * @return array 
	 */
	public function fetchVerifyCheck($ids) {
		if (!is_array($ids) || !$ids) return array();
		return $this->_getDao()->fetch($ids);
	}
	
	/**
	 * 根据用户和类型获取信息
	 *
	 * @param int $uid
	 * @param int $type
	 * @return array 
	 */
	public function getVerifyByUidAndType($uid, $type) {
		$uid = intval($uid);
		$type = intval($type);
		if ($uid < 1 || $type < 1) return array();
		return $this->_getDao()->getByUidAndType($uid, $type);
	}
	
	/**
	 * 根据用户获取信息
	 *
	 * @param int $uid
	 * @return array 
	 */
	public function getVerifyByUid($uid) {
		$uid = intval($uid);
		if ($uid < 1) return array();
		return $this->_getDao()->get($uid);
	}
	
	/**
	 * 根据类型获取数据
	 *
	 * @param int $type
	 * @return array 
	 */
	public function countVerifyCheckByType($type = '') {
		return $this->_getDao()->countByType($type);
	}
	
	/**
	 * 根据类型获取数据
	 *
	 * @param int $type
	 * @return array 
	 */
	public function getVerifyCheckByType($type = '', $limit = 20, $offset = 0) {
		return $this->_getDao()->getByType($type, $limit, $offset);
	}
	
	/**
	 * 添加
	 *
	 * @param AppVerify_VerifyDm $dm
	 * @return bool 
	 */
	public function addVerifyCheck(AppVerify_VerifyDm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getDao()->add($dm->getData());
	}
	
	/**
	 * 添加替换
	 *
	 * @param AppVerify_VerifyDm $dm
	 * @return bool 
	 */
	public function replaceVerifyCheck(AppVerify_VerifyDm $dm) {
		if (($result = $dm->beforeAdd()) instanceof PwError) return $result;
		return $this->_getDao()->replace($dm->getData());
	}
	
	/**
	 * 删除一条
	 *
	 * @param int $id
	 * @return array 
	 */
	public function deleteVerifyCheck($id) {
		$id = intval($id);
		if ($id < 1) return false;
		return $this->_getDao()->delete($id);
	}	 
	
	/**
	 * 批量删除
	 *
	 * @param array $ids
	 * @return array 
	 */
	public function batchDeleteVerifyCheck($ids) {
		if (!is_array($ids) || !$ids) return false;
		return $this->_getDao()->batchDelete($ids);
	}	
	
	/**
	 * 单条修改
	 *
	 * @param int $uid
	 * @param array $data
	 * @return bool
	 */
	public function updateVerifyCheck($id, AppVerify_VerifyDm $dm) {
		if (($result = $dm->beforeUpdate()) instanceof PwError) return $result;
		return $this->_getDao()->update($id,$dm->getData());
	}
	
	/**
	 * @return AppVerify_VerifyCheckDao
	 */
	protected function _getDao() {
		return Wekit::loadDao('EXT:verify.service.dao.AppVerify_VerifyCheckDao');
	}
}