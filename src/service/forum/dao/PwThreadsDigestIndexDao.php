<?php

/**
 * 精华帖子索引表
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwThreadsDigestIndexDao.php 15975 2012-08-16 09:40:09Z xiaoxia.xuxx $
 * @package src.service.forum.dao
 */
class PwThreadsDigestIndexDao extends PwBaseDao {
	protected $_table = 'bbs_threads_digest_index';
	protected $_pk = 'tid';
	protected $_dataStruct = array('tid', 'cid', 'fid', 'topic_type', 'created_time', 'lastpost_time', 'operator', 'operator_userid', 'operator_time');
	
	/**
	 * 根据版块分类ID获取精华帖子
	 *
	 * @param int $cid 类型
	 * @param int $limit 查询的条数
	 * @param int $offset 开始查询的位置
	 * @param string $order 排序方式
	 * @return array
	 */
	public function getThreadsByCid($cid, $limit, $offset, $order) {
		list($field, $idx) = $this->_getOrderFieldAndIndex($order);
		$sql = $this->_bindSql('SELECT * FROM %s FORCE INDEX(%s) WHERE cid=? ORDER BY %s DESC %s', $this->getTable(), $idx, $field, $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($cid), 'tid');
	}
	
	/**
	 * 根据版块分类ID统计精华帖子
	 *
	 * @param int $cid
	 * @return int
	 */
	public function countByCid($cid) {
		$sql = $this->_bindTable('SELECT COUNT(*) as count FROM %s WHERE cid=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue(array($cid));
	}
	
	/**
	 * 根据版块ID获取该版块的精华列表
	 *
	 * @param int $fid  版块ID
	 * @param int $typeid 主题类型
	 * @param int $limit
	 * @param int $offset
	 * @param string $order
	 * @return array
	 */
	public function getThreadsByFid($fid, $typeid, $limit, $offset, $order) {
		list($field, $idx) = $this->_getOrderFieldAndIndex($order, true);
		$sql = $this->_bindSql('SELECT * FROM %s FORCE INDEX(%s) WHERE fid=? %s ORDER BY %s DESC %s', $this->getTable(), $idx, $typeid ? ' AND topic_type = ' . intval($typeid) : '', $field, $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($fid), 'tid');
	}
	
	/**
	 * 根据版块ID统计该版块的精华列表
	 *
	 * @param int $fid  版块ID
	 * @param int $typeid 主题类型
	 * @return int
	 */
	public function countByFid($fid, $typeid) {
		$sql = $this->_bindSql('SELECT COUNT(*) as count FROM %s WHERE fid=? %s', $this->getTable(), $typeid ? ' AND topic_type = ' . intval($typeid) : '');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue(array($fid));
	}
	
	/**
	 * 添加精华
	 *
	 * @param int $tid
	 * @param array $fields
	 * @return boolean
	 */
	public function addThread($tid, $fields) {
		if (1 != $fields['digest']) return true;
		$fields['tid'] = $tid;
		$fields = $this->_processField($fields);
		return $this->_add($fields, false);
	}
	
	/**
	 * 批量加精
	 *tem
	 * @param array $data
	 * @return int
	 */
	public function batchAddDigest($data) {
		$clear = array();
		foreach ($data as $_tmp) {
			$clear[] = array($_tmp['tid'], $_tmp['cid'], $_tmp['fid'], $_tmp['topic_type'], $_tmp['created_time'], 
					$_tmp['lastpost_time'], $_tmp['operator'], $_tmp['operator_userid'], $_tmp['operator_time']);
		}
		$sql = $this->_bindSql('REPLACE INTO %s (`tid`, `cid`, `fid`, `topic_type`, `created_time`, `lastpost_time`, `operator`, `operator_userid`, `operator_time`) VALUES	%s', $this->getTable(), $this->sqlMulti($clear));
		return $this->getConnection()->execute($sql);
	}

	/**
	 * 更新精华相关信息
	 *
	 * @param int $tid
	 * @param array $fields
	 * @param array $increaseFields
	 * @return int
	 */
	public function updateThread($tid, $fields, $increaseFields = array()) {
		$fields = $this->_processField($fields);
		return $this->_update($tid, $fields, $increaseFields);
	}

	/**
	 * 批量更新精华相关信息
	 *
	 * @param array $tids
	 * @param array $fields
	 * @param array $increaseFields
	 * @return boolean
	 */
	public function batchUpdateThread($tids, $fields, $increaseFields = array()) {
		$fields = $this->_processField($fields);
		return $this->_batchUpdate($tids, $fields, $increaseFields);
	}
	
	/**
	 * 删除精华相关信息
	 *
	 * @param int $tid
	 * @return int
	 */
	public function deleteThread($tid) {
		return $this->_delete($tid);
	}

	/**
	 * 批量删除帖子精华相关信息
	 *
	 * @param array $tids
	 * @return boolean
	 */
	public function batchDeleteThread($tids) {
		return $this->_batchDelete($tids);
	}

	/**
	 * 处理版块对应的分类
	 *
	 * @param array $fields
	 * @return array
	 */
	private function _processField($fields) {
		if (isset($fields['fid'])) {
			$fields['cid'] = $fields['fid'] ? Wekit::load('forum.srv.PwForumService')->getCateId($fields['fid']) : 0;
		}
		return $fields;
	}
	
	/**
	 * 获得排序的字段和索引
	 *
	 * @param string $order
	 * @param boolean $fid
	 * @return array
	 */
	private function _getOrderFieldAndIndex($order, $fid = false) {
		if ($order == 'lastpost') {
			return array('lastpost_time', false === $fid ? 'idx_cid_lastposttime' : 'idx_fid_lastposttime_topictype');
		}
		return array('tid', 'PRIMARY');
	}
}