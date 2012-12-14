<?php
Wind::import('SRC:library.base.PwBaseDao');

/**
 * 用户认证
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyDao extends PwBaseDao {
	
	protected $_pk = 'uid';
	protected $_table = 'appverify_verify';
	protected $_dataStruct = array('uid', 'username', 'type', 'created_time');
	
	/**
	 * 获取一条信息
	 *
	 * @param int $id
	 * @return array
	 */
	public function get($uid) {
		return $this->_get($uid);
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
	 * 单条修改
	 *
	 * @param array $data
	 * @return bool
	 */
	public function update($uid, $fields = array(), $increaseFields = array(), $bitFields = array()) {
		return $this->_update($uid, $fields, $increaseFields, $bitFields);
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
	 * @param int $id
	 * @return bool
	 */
	public function delete($uid) {
		return $this->_delete($uid);
	}
	
	/**
	 * 批量删除
	 *
	 * @param array $uids
	 * @return bool
	 */
	public function batchDelete($uids) {
		return $this->_batchDelete($uids);
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
	public function searchVerify($condition, $limit, $offset) {
		list($where, $params) = $this->_buildCondition($condition);
		$sql = $this->_bindSql('SELECT * FROM %s %s ORDER BY created_time DESC %s', $this->getTable(), $where, $this->sqlLimit($limit, $offset));
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->queryAll($params,'uid');
	}
	
	/**
	 * 根据查询条件统计
	 *
	 * @param array $condition
	 * @return int
	 */
	public function countSearchVerify($condition) {
		list($where, $params) = $this->_buildCondition($condition);
		$sql = $this->_bindSql('SELECT COUNT(*) FROM %s %s', $this->getTable(), $where);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getValue($params);
	}
	
	private function _buildCondition($condition) {
		if (!$condition) return array('', array());
		$where = $params = array();
		foreach ($condition as $key=>$value) {
			switch ($key) {
				case 'uid':
					 $where[] = 'uid IN '. $this->sqlImplode($value);

				break;
				case 'username':
					 $where[] = 'username LIKE ?';
					 $params[] = "%$value%";
				break;
				case 'type':
					 $where[] = 'type & ?';
					 $params[] = $value;
				break;
			}
		}
		$_whereSql = $where ? $this->_bindSql('WHERE %s', implode(' AND ', $where)) : '';
		return array($_whereSql, $params);
	}
}