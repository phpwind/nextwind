<?php
Wind::import('SRC:library.base.PwBaseDao');

/**
 * 用户认证审核
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyCheckDao extends PwBaseDao {
	
	protected $_table = 'appverify_verify_check';
	protected $_dataStruct = array('id', 'uid', 'username', 'type', 'data', 'created_time');
	
	/**
	 * 获取一条信息
	 *
	 * @param int $id
	 * @return array
	 */
	public function get($id) {
		return $this->_get($id);
	}
	
	/**
	 * 批量获取信息
	 *
	 * @param array $ids
	 * @return array
	 */
	public function fetch($ids) {
		return $this->_fetch($ids);
	}
	
	/**
	 * 根据用户获取信息
	 *
	 * @param int $uid
	 * @return array
	 */
	public function getByUid($uid) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE `uid`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll(array($uid));
	}
	
	/**
	 * 根据用户和类型获取信息
	 *
	 * @param int $uid
	 * @param int $type
	 * @return array 
	 */
	public function getByUidAndType($uid, $type) {
		$sql = $this->_bindTable('SELECT * FROM %s WHERE `uid`=? AND `type`=?');
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($uid, $type));
	}
	
	/**
	 * 根据类型获取信息
	 *
	 * @param int $type
	 * @return int
	 */
	public function countByType($type = '') {
		$sqlAdd = ' where 1';
		$param = array();
		if ($type) {
			$sqlAdd .= ' AND `type`=?';
			$param[] = $type;
		}
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s %s', $this->getTable(), $sqlAdd);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($param);
	}
		
	/**
	 * 根据类型获取信息
	 *
	 * @param int $type
	 * @return array
	 */
	public function getByType($type = '', $limit, $offset) {
		$sqlAdd = ' where 1';
		$param = array();
		if ($type) {
			$sqlAdd .= ' AND `type`=?';
			$param[] = $type;
		}
		$sql = $this->_bindSql('SELECT * FROM %s %s ORDER BY created_time DESC %s', $this->getTable(), $sqlAdd, $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($param);
	}
	
	/**
	 * 单条添加
	 *
	 * @param array $data
	 * @return bool
	 */
	public function add($data) {
		return $this->_add($data);
	}
	
	/**
	 * 单条添加
	 *
	 * @param array $data
	 * @return bool
	 */
	public function replace($data) {
		$sql = $this->_bindSql('REPLACE INTO %s SET %s', $this->getTable(), $this->sqlSingle($data));
		return $this->getConnection()->execute($sql);
	}
	
	/**
	 * 单条删除
	 *
	 * @param int $uid
	 * @return bool
	 */
	public function delete($id) {
		return $this->_delete($id);
	}
	
	/**
	 * 批量删除
	 *
	 * @param array $uids
	 * @return bool
	 */
	public function batchDelete($ids) {
		return $this->_batchDelete($ids);
	}
	
	/**
	 * 单条修改
	 *
	 * @param int $uid
	 * @param array $data
	 * @return bool
	 */
	public function update($id,$data) {
		return $this->_update($id,$data);
	}
	
	/**
	 * 根据查询条件查询数据
	 *
	 * @param array $condition
	 * @param int $limit 
	 * @param int $start
	 * @param array $orderby
	 * @return array
	 */
	public function searchVerify($condition, $limit, $start, $offset) {
		list($where, $params) = $this->_buildCondition($condition);
		$sql = $this->_bindSql('SELECT * FROM %s %s ORDER BY created_time DESC %s', $this->getTable(), $where, $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($params);
	}
	
	/**
	 * 根据查询条件统计
	 *
	 * @param array $condition
	 * @return int
	 */
	public function countSearchVerify($condition) {
		list($where, $params) = $this->_buildCondition($condition);
		$sql = $this->_bindSql('SELECT COUNT(*) AS total FROM %s %s', $this->getTable(), $where);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($params);
	}
	
	private function _buildCondition($condition) {
		if (!$condition) return array('', array());
		$where = $params = array();
		foreach ($condition as $key=>$value) {
			switch ($key) {
				case 'uid':
					 $where[] = 'uid = ?';
					 $params[] = $value;
				break;
				case 'username':
					 $where[] = 'username LIKE ?';
					 $params[] = "%$value%";
				break;
				case 'type':
					 $where[] = 'type = ?';
					 $params[] = $value;
				break;
			}
		}
		$_whereSql = $where ? $this->_bindSql('WHERE %s', implode(' AND ', $where)) : '';
		return array($_whereSql, $params);
	}
}