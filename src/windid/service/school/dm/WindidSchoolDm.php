<?php
Wind::import('WINDID:library.base.WindidBaseDm');
/**
 * 学校DM
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: WindidSchoolDm.php 23087 2013-01-06 03:37:34Z jinlong.panjl $
 * @package service.school.dm
 */
class WindidSchoolDm extends WindidBaseDm {
	private $schoolid = 0;
	
	/**
	 * 设置学校ID
	 *
	 * @param int $schoolid
	 * @return WindidSchoolDm
	 */
	public function setSchoolid($schoolid) {
		$this->schoolid = intval($schoolid);
		return $this;
	}
	
	/**
	 * 获得学校ID
	 *
	 * @return int
	 */
	public function getSchoolid() {
		return $this->schoolid;
	}
	
	/**
	 * 学校名称
	 *
	 * @param string $name
	 * @return WindidSchoolDm
	 */
	public function setName($name) {
		$this->_data['name'] = $name;
		return $this;
	}
	
	/**
	 * 设置首字母
	 *
	 * @param string $first_char
	 * @return WindidSchoolDm
	 */
	public function setFirstChar($first_char) {
		$this->_data['first_char'] = $first_char;
		return $this;
	}
	
	/**
	 * 设置类型
	 *
	 * @param int $typeid
	 * @return WindidSchoolDm
	 */
	public function setTypeid($typeid) {
		$this->_data['typeid'] = intval($typeid);
		return $this;
	}
	
	/**
	 * 设置地区
	 *
	 * @param int $areaid
	 * @return WindidSchoolDm
	 */
	public function setAreaid($areaid) {
		$this->_data['areaid'] = intval($areaid);
		return $this;
	}
	
	/* (non-PHPdoc)
	 * @see WindidBaseDm::_beforeAdd()
	 */
	protected function _beforeAdd() {
		if (!isset($this->_data['name']) || !$this->_data['name']) return new WindidError(WindidError::FAIL);
		if (!isset($this->_data['areaid']) || $this->_data['areaid'] < 1) return new WindidError(WindidError::FAIL);
		if (!isset($this->_data['typeid'])) return new WindidError(WindidError::FAIL);
		return true;
	}

	/* (non-PHPdoc)
	 * @see WindidBaseDm::_beforeUpdate()
	 */
	protected function _beforeUpdate() {
		if ($this->schoolid < 1) return new WindidError(WindidError::FAIL);
		unset($this->_data['typeid']);
		return true;
	}
}