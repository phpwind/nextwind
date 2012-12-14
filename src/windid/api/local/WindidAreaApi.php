<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAreaApi.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package 
 */
class WindidAreaApi {
	
	public function getArea($id) {
		return $this->_getAreaDs()->getArea($id);
	}
	
	public function fetchArea($ids){
		return $this->_getAreaDs()->fetchByAreaid($ids);
	}
	
	public function getByParentid($parentid) {
		return $this->_getAreaDs()->getAreaByParentid($parentid);
	}
	
	public function getAll(){
		return $this->_getAreaDs()->fetchAll();
	}
	
	private function _getAreaDs() {
		return Windid::load('area.WindidArea');
	}
}
?>