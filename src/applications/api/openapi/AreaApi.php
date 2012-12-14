<?php
Wind::import('APPS:api.openapi.OpenBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AreaApi.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class AreaController extends OpenBaseController{
	
	public function getArea() {
		return $this->_getAreaDs()->getArea($this->getInput('id'));
		exit;
	}
	
	public function fetchArea(){
		return $this->_getAreaDs()->fetchByAreaid(explode('_', $this->getInput('ids')));
	}
	
	public function getByParentid() {
		return $this->_getAreaDs()->getAreaByParentid($this->getInput('parentid'));
	}
	
	public function getAll(){
		return $this->_getAreaDs()->fetchAll();
	}
	
	private function _getAreaDs() {
		return Windid::load('area.WindidArea');
	}
}
?>