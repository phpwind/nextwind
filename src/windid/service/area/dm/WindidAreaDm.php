<?php
Wind::import('WINDID:library.base.WindidBaseDm');

/**
 * 地区的DM
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidAreaDm.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package windid.service.area.dm
 */
class WindidAreaDm extends WindidBaseDm {
	public $areaid;
	
	/**
	 * 设置地区ID
	 *
	 * @param int $areaid
	 * @return WindidAreaDm
	 */
	public function setAreaid($areaid) {
		$this->areaid = intval($areaid);
		return $this;
	}
	
	/**
	 * 设置地区名字
	 *
	 * @param string $name
	 * @return WindidAreaDm
	 */
	public function setName($name) {
		$this->_data['name'] = trim($name);
		return $this;
	}
	
	/**
	 * 设置上级地区ID
	 *
	 * @param int $parentid
	 * @return WindidAreaDm
	 */
	public function setParentid($parentid) {
		$this->_data['parentid'] = intval($parentid);
		return $this;
	}
	
	/**
	 * 路径-省-市-区
	 *
	 * @param string $joinName
	 * @return WindidAreaDm
	 */
	public function setJoinname($joinName) {
		$this->_data['joinname'] = $joinName;
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see WindidBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if (!isset($this->_data['name']) || !$this->_data['name']) return new WindidError(WindidError::FAIL);
		$_tmp = str_replace(array('&', '"', "'", '<', '>', '\\', '/'), '', $this->_data['name']);
		if ($_tmp != $this->_data['name']) return new WindidError(WindidError::FAIL);
		return true;
	}

	/* (non-PHPdoc)
	 * @see WindidBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if (!$this->areaid) return new WindidError(WindidError::FAIL);
		if (!isset($this->_data['name']) || !$this->_data['name']) return new WindidError(-2);
		$_tmp = str_replace(array('&', '"', "'", '<', '>', '\\', '/'), '', $this->_data['name']);
		if ($_tmp != $this->_data['name']) return new WindidError(WindidError::FAIL);
		return true;
	}
}