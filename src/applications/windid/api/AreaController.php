<?php
Wind::import('APPS:windid.api.OpenBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: AreaController.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class AreaController extends OpenBaseController{
	
	public function getAction() {
		$result = $this->_getAreaDs()->getArea($this->getInput('id', 'get'));
		$this->output($result);
	}
	
	public function fetchAction(){
		$result = $this->_getAreaDs()->fetchByAreaid(explode('_', $this->getInput('ids', 'get')));
		$this->output($result);
	}
	
	public function getByParentidAction() {
		$result = $this->_getAreaDs()->getAreaByParentid($this->getInput('parentid', 'get'));
		$this->output($result);
	}
	
	public function getAllAction(){
		$result = $this->_getAreaDs()->fetchAll();
		$this->output($result);
	}
	
	private function _getAreaDs() {
		return Windid::load('area.WindidArea');
	}
}
?>