<?php

/**
 * 用户user_data_ExpandDao的Ds服务
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwUserDataExpand.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package wind
 */
class PwUserDataExpand {
	
	/**
	 * 获得表结构
	 * 
	 * @return array
	 */
	public function getCreditStruct() {
		$struct = $this->_getDao()->getStruct();
		$credit = array();
		foreach ($struct as $_key) {
			if (strpos($_key, 'credit') === 0) $credit[] = $_key;
		}
		return $credit;
	}

	/**
	 * 更新用户data表添加credit字段
	 *
	 * @param int $num
	 * @return boolean
	 */
	public function alterAddCredit($num) {
		$this->_getWindid()->alterCredit($num);
		$num = intval($num);
		return $num < 9 ? false : $this->_getDao()->alterAddCredit($num);
	}
	
	/**
	 * 删除用户积分字段（1-8不允许删除）
	 *
	 * @param int $num
	 * @return boolean
	 */
	public function alterDropCredit($num) {
		$this->_getWindid()->dropCredit($num);
		$num = intval($num);
		return $num < 9 ? false : $this->_getDao()->alterDropCredit($num);
	}
	
	/**
	 * 将用户积分的某一列清空
	 *
	 * @param int $num
	 * @return boolean
	 */
	public function clearCredit($num) {
		$this->_getWindid()->clearCredit($num);
		$num = intval($num);
		return ($num > 8 || $num < 1) ? false : $this->_getDao()->clearCredit($num);
	}
	
	/**
	 * 返回用户dataExpandDao对象
	 *
	 * @return PwUserDataExpandDao
	 */
	private function _getDao() {
		return Wekit::loadDao('user.dao.PwUserDataExpandDao');
	}
	
	/**
	 * 获得windidDS
	 *
	 * @return WindidUser
	 */
	protected function _getWindid() {
		return WindidApi::api('user');
	}
}