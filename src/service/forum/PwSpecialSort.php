<?php

/**
 * Enter description here ...
 * 
 * @author peihong.zhangph <peihong.zhangph@aliyun-inc.com> Dec 9, 2011
 * @link http://www.phpwind.com
 * @copyright 2011 phpwind.com
 * @license
 * @version $Id: PwSpecialSort.php 17072 2012-08-31 02:25:02Z peihong.zhangph $
 */

class PwSpecialSort {

	/**
	 * 获取某个版块特殊排序的帖子
	 *
	 * @param int $fid
	 * @return array
	 */
	public function getSpecialSortByFid($fid) {
		if (empty($fid)) return array();
		return $this->_getDao()->getSpecialSortByFid($fid);
	}
	
	/**
	 * 
	 * 根据排序类型及参数获取相关Tids
	 */
	public function getSpecialSortByTypeExtra($sortType,$extra = 0){
		$extra = intval($extra);
		return $this->_getDao()->getSpecialSortByTypeExtra($sortType,$extra);
	}

	/**
	 * 获取某个帖子特殊排序情况
	 *
	 * @param int $tid
	 * @return array
	 */
	public function getSpecialSortByTid($tid) {
		if (empty($tid)) return array();
		return $this->_getDao()->getSpecialSortByTid($tid);
	}
	
	/**
	 * 批量添加排序帖子
	 *
	 * @param array $dms
	 * @return bool
	 */
	public function batchAdd($dms) {
		$data = array();
		foreach ($dms as $key => $dm) {
			if (($dm instanceof PwThreadSortDm) && ($result = $dm->beforeAdd()) === true) {
				$data[] = $dm->getData();
			}
		}
		if (empty($data)) return false;
		return $this->_getDao()->batchAdd($data);
	}
	
	/**
	 * 删除多个帖子的排序信息
	 *
	 * @param array $tids
	 * @return bool
	 */
	public function batchDeleteSpecialSortByTid($tids) {
		if (empty($tids) || !is_array($tids)) return false;
		return $this->_getDao()->batchDeleteSpecialSortByTid($tids);
	}

	/*******************************************************************************/

	/**
	 * 
	 * 添加特殊排序
	 *
	 * @param PwSpecialSortDm $dm
	 */
	public function addSpecialSort($dm) {
		if (($result = $dm->beforeAdd()) !== true) {
			return $result;
		}
		$fields = $dm->getData();
		return $this->_getDao()->addSpecialSort($fields);
	}
	
	public function deleteSpecialSortByTids($tids) {
		if (empty($tids) || !is_array($tids)) return false;
		return $this->_getDao()->batchDeleteSpecialSortByTid($tids);
	}
	
	/**
	 * Enter description here ...
	 * @return PwSpecialSortDao
	 */
	protected function _getDao() {
		return Wekit::loadDao('forum.dao.PwSpecialSortDao');
	}
}