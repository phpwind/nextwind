<?php
/**
 * Date model的基类
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com> 2010-11-2
 * @license http://www.phpwind.com
 * @version $Id: WindidBaseDm.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package Windid.labrary.base
 */
abstract class WindidBaseDm {
	
	/**
	 * 数据信息
	 *
	 * @var array
	 */
	protected $_data = array();
	protected $_increaseData = array();
	protected $_status = array();
	
	/**
	 * 返回用户资料信息
	 * 
	 * @return array
	 */
	public function getData() {
		return $this->_data;
	}

	/**
	 * 获取递增的数据信息
	 *
	 * @return array
	 */
	public function getIncreaseData() {
		return $this->_increaseData;
	}
	
	final public function beforeAdd() {
		isset($this->_status['add']) || $this->_status['add'] = $this->_beforeAdd();
		return $this->_status['add'];
	}

	final public function beforeUpdate() {
		isset($this->_status['update']) || $this->_status['update'] = $this->_beforeUpdate();
		return $this->_status['update'];
	}
	
	/** 
	 * 获取data中的数据
	 *
	 * @param string $field
	 * @return mixed
	 */
	public function getField($field) {
		return isset($this->_data[$field]) ? $this->_data[$field] : null;
	}

	/**
	 * 添加数据的检查
	 * 
	 * @return boolean
	 */
	abstract protected function _beforeAdd();
	
	/**
	 * 更新数据的检查
	 * 
	 * @return boolean
	 */
	abstract protected function _beforeUpdate();
}