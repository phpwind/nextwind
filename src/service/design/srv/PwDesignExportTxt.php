<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignExportTxt.php 23387 2013-01-09 07:14:36Z gao.wanggao $ 
 * @package 
 */
class PwDesignExportTxt {
	
	public $pageInfo = array();
	
	public function __construct($pageInfo) {
		$this->pageInfo = $pageInfo;
	}
	

	public function txt() {
		$pageInfo = $this->pageInfo;
		//$ids = explode(',', $pageInfo['module_ids']);
		$ids = array();
		$modules = $this->_getModuleDs()->getByPageid($pageInfo['page_id']);
		foreach ($modules  AS $k=>$v) {
			if (!$v['isused']) continue;
			$ids[] = $k;
		}
		$names = explode(',', $pageInfo['struct_names']);
		$modules = $this->_getModuleDs()->fetchModule($ids);
		foreach ($modules AS &$module) {
			unset($module['isused'], $module['module_id']);
		}

		$structures = $this->_getStructureDs()->fetchStruct($names);
		$txtSegment = array();
		$segments = $this->_getSegmentDs()->getSegmentByPageid($pageInfo['page_id']);
		foreach ($segments AS $k=>$v) {
			if (!$v['segment_tpl']) continue;
			$txtSegment[$k] = $v['segment_struct'];
		}
		$txtPage['module_ids'] = $pageInfo['module_ids'];
		$txtPage['struct_names'] = $pageInfo['struct_names'];
		$_nr = "\n";
		$_time = Pw::getTime();
		$_title = $_nr;
		$_text['page']= $txtPage;
		$_text['segment'] = $txtSegment;
		$_text['structure'] = $structures;
		$_text['module'] = $modules;
		$_text = wordwrap(base64_encode(serialize($_text)), 100, $_nr, true);
		$_end = $_nr;
		$filename = $pageInfo['page_name'] ? $pageInfo['page_name'] : $_time;
		$_text = $_title . $_text . $_end;
		return array(
			'content'=>$_text,
			'filename'=>$filename,
			'ext'=>'txt'
		);
	}
	
	private function _getSegmentDs() {
		return Wekit::load('design.PwDesignSegment');
	}
	
	private function _getModuleDs() {
		return Wekit::load('design.PwDesignModule');
	}
	
	private function _getStructureDs() {
		return Wekit::load('design.PwDesignStructure');
	}
	
	private function _getPageDs() {
		return Wekit::load('design.PwDesignPage');
	}
	
	private function _getPortalDs() {
		return Wekit::load('design.PwDesignPortal');
	}
}
?>