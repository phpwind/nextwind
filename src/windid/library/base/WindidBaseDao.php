<?php

Wind::import('WIND:dao.WindDao');

/**
 * Dao的基类
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidBaseDao.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package Windid.library.base
 */
abstract class WindidBaseDao extends WindDao {

	protected $_table = '';
	protected $_pk = 'id';
	protected $_dataStruct = array();
	protected $_baseInstance = null;
	protected $_defaultbaseInstance = '';
	
	/**
	 * 设置基础dao
	 *
	 * @param object $instance
	 * @return void
	 */
	public function setBaseInstance($instance) {
		$this->_baseInstance = $instance;
	}
	
	/**
	 * 获取基础dao
	 *
	 * @return object| throw Error
	 */
	public function getBaseInstance() {
		if (!$this->_baseInstance) {
			if (empty($this->_defaultbaseInstance)) throw new Exception('This dao is error');
			$this->_baseInstance = Windid::loadDao($this->_defaultbaseInstance);
		}
		return $this->_baseInstance;
	}
	/*
	public function getConnection() {
		return Wind::getComponent('windiddb');
	}*/

	/**
	 * 获得表名
	 * 
	 * @return string
	 */
	public function getTable() {
		return $this->getConnection()->getTablePrefix() . $this->_table;
	}

	/**
	 * sql组装,将数组组装成`key`=value的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlSingle($array) {
		return $this->getConnection()->sqlSingle($array);
	}

	/**
	 * sql组装,将数组组装成`key`=`key`+value的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlSingleIncrease($array) {
		if (!is_array($array)) return '';
		$str = array();
		foreach ($array as $key => $val) {
			$str[] = $this->getConnection()->sqlMetadata($key) . '=' . $this->getConnection()->sqlMetadata($key) . '+' . $this->getConnection()->quote($val);
		}
		return $str ? implode(',', $str) : '';
	}

	/**
	 * sql组装,将数组组装成('a1','b1','c1'),('a2','b2','c2')的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlMulti($array) {
		return $this->getConnection()->quoteMultiArray($array);
	}

	/**
	 * sql组装,将数组组装成('a1','b1','c1')的形式返回
	 *
	 * @param array $array 待组装的数据
	 * @return string
	 */
	public function sqlImplode($array) {
		return $this->getConnection()->quoteArray($array);
	}

	/**
	 * 组装sql limit表达式串,并返回组装后的结果
	 *
	 * @param int $start
	 * @param int $num
	 * @return string
	 */
	public function sqlLimit($limit, $offset = 0) {
		if (!$limit) return '';
		return ' LIMIT ' . max(0, intval($offset)) . ',' . max(1, intval($limit));
	}

	/**
	 * sql组合语句,(sqlSingle, sqlSingleIncrease) `key`=value,`key`=`key`+value
	 *
	 * @param array $updateFields 更新操作的字段
	 * @param array $IncreaseFields 增减操作的字段
	 * @return string
	 */
	public function sqlMerge($updateFields, $increaseFields) {
		$sql = $etr = '';
		if ($updateFields) {
			$sql .= $this->sqlSingle($updateFields);
			$etr = ',';
		}
		if ($increaseFields) {
			$sql .= $etr . $this->sqlSingleIncrease($increaseFields);
		}
		return $sql;
	}

	/**
	 * 检查数据结构合法性
	 *
	 * @param array $array 待检查的数据
	 * @param array $struct 合法的数据结构
	 * @return array
	 */
	protected function _filterStruct($array, $allow = array()) {
		if (empty($array) || !is_array($array)) return array();
		empty($allow) && $allow = $this->_dataStruct;
		if (empty($allow) || !is_array($allow)) return $array;
		$data = array();
		foreach ($array as $key => $value) {
			in_array($key, $allow) && $data[$key] = $value;
		}
		return $data;
	}

	/**
	 * 获得数据表结构
	 * 
	 * @return array
	 */
	protected function _getStruct() {
		$sql = $this->_bindTable('SHOW COLUMNS FROM %s');
		$tbFields = $this->getConnection()->createStatement($sql)->queryAll(array(), 'Field');
		return array_keys($tbFields);
	}
	
	/**
	 * 绑定tablename,并返回绑定后结果
	 *
	 * @param string $sql 需要绑定tablename的sql语句
	 * @param string $table 默认为当前表
	 * @return string
	 */
	protected function _bindTable($sql, $table = '') {
		$table === '' && $table = $this->getTable();
		return sprintf($sql, $table);
	}

	/**
	 * 绑定sql中的变量,并返回绑定后结果
	 *
	 * @param string $sql 需要绑定变量参数的sql语句
	 * @return string
	 */
	protected function _bindSql($sql) {
		$args = func_get_args();
		return call_user_func_array('sprintf', $args);
	}

	/**
	 * 结果集合并
	 *
	 * @param array $array1
	 * @param array $array2
	 * @return multitype:Ambigous <multitype:, unknown> 
	 */
	protected function _margeArray($array1, $array2) {
		$result = array();
		foreach ($array1 as $key => $value) {
			$result[$key] = isset($array2[$key]) ? array_merge($value, $array2[$key]) : $value;
		}
		return $result;
	}

	/**
	 * 返回最后插入的一个ID
	 * 
	 * @return int
	 */
	public function lastInsertId() {
		return $this->getConnection()->lastInsertId();
	}
	
	protected function _get($id) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE %s=?', $this->getTable(), $this->_pk);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->getOne(array($id));
	}

	protected function _fetch($ids, $index = '', $fetchMode = 0) {
		$sql = $this->_bindSql('SELECT * FROM %s WHERE %s IN %s', $this->getTable(), $this->_pk, $this->sqlImplode($ids));
		$rst = $this->getConnection()->query($sql);
		return $rst->fetchAll($index, $fetchMode);
	}

	protected function _add($fields, $getId = true) {
		if (!$fields = $this->_filterStruct($fields)) {
			return false;
		}
		$sql = $this->_bindSql('INSERT INTO %s SET %s', $this->getTable(), $this->sqlSingle($fields));
		$result = $this->getConnection()->execute($sql);
		return ($getId && $result) ? $this->getConnection()->lastInsertId() : $result;
	}

	protected function _update($id, $fields, $increaseFields = array()) {
		$fields = $this->_filterStruct($fields);
		$increaseFields = $this->_filterStruct($increaseFields);
		if (!$fields && !$increaseFields) {
			return false;
		}
		$sql = $this->_bindSql('UPDATE %s SET %s WHERE %s=?', $this->getTable(), $this->sqlMerge($fields, $increaseFields), $this->_pk);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($id));
	}

	protected function _delete($id) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE %s=?', $this->getTable(), $this->_pk);
		$smt = $this->getConnection()->createStatement($sql);
		return $smt->update(array($id));
	}

	protected function _batchDelete($ids) {
		$sql = $this->_bindSql('DELETE FROM %s WHERE %s IN %s', $this->getTable(), $this->_pk, $this->sqlImplode($ids));
		$this->getConnection()->execute($sql);
		return true;
	}
}