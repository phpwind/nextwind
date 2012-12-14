<?php
Wind::import('ADMIN:library.AdminBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PageController.php 19854 2012-10-19 02:11:42Z gao.wanggao $ 
 * @package 
 */

class PageController extends AdminBaseController {
	
	public function run() {
		$page = (int)$this->getInput('page','get');
		$perpage = 10;
		$args = array();
		$page =  $page > 1 ? $page : 1;
		list($start, $perpage) = Pw::page2limit($page, $perpage);
		$list = $this->_getPageDs()->getPageList(PwDesignPage::SYSTEM, $start, $perpage);
		$count =  $this->_getPageDs()->countPage(PwDesignPage::SYSTEM);
		$path = Wind::getRealPath('SRV:design.srv.router.router');
		if (!is_file($path)) return false;
		$sysPage = @include $path;
		foreach ($list AS &$v) {
			if (isset($sysPage[$v['page_router']])){ 
				list($pagename, $unique) = $sysPage[$v['page_router']];
			}
			list($m,$c,$a,$id) = explode('|', $v['page_router']);
			if ($unique) {
				$v['url'] = WindUrlHelper::createUrl($m .'/'. $c .'/'. $a .'/?'.$unique .'=' . $v['page_unique'], array(), '', 'pw');
			} else {
				$v['url'] = WindUrlHelper::createUrl($m .'/'. $c .'/'. $a, array(), '', 'pw');
			}
			$sep = strpos($v['url'],'?') === false ? '?' : '&';
			$v['designurl'] = $v['url'].$sep.'design=1';
		}
		
		$this->setOutput($list,'list');
		$this->setOutput($count, 'count');
		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput(ceil($count/$perpage), 'totalpage');
		$this->setOutput('design/page/run', 'pageurl');
	}
	
	public function getModuleOptionAction() {
		$option = '';
		$pageid = (int)$this->getInput('pageid','post');
		$pageInfo = $this->_getPageDs()->getPage($pageid);
		if (!$pageInfo['module_ids']) $this->showMessage("operate.fail");
		$modules = $this->_getModuleDs()->fetchModule(explode(',', $pageInfo['module_ids']));
		
		foreach ($modules AS $v) {
			$option .= '<option value="'.$v['module_id'].'">'.$v['module_name'].'</option>';
		}
		$this->setOutput($option, 'data');
		$this->showMessage("operate.success");
	}
	
	/**
	 * 清空当前页设计数据
	 * Enter description here ...
	 * @see ImportController->dorunAction
	 */
	public function doclearAction() {
		$pageid = (int)$this->getInput('id', 'get');
		$pageDs = $this->_getPageDs();
		$pageInfo = $pageDs->getPage($pageid);
		if (!$pageInfo) $this->showError("operate.fail");
		
		$ids = explode(',', $pageInfo['module_ids']);
		$names = explode(',', $pageInfo['module_names']);
		$moduleDs = $this->_getModuleDs();
		$bakDs = $this->_getBakDs();
		$dataDs = $this->_getDataDs();
		$pushDs = $this->_getPushDs();
		$imageSrv = Wekit::load('design.srv.PwDesignImage');
		// module
		$moduleDs->deleteByPageId($pageid);
		
		// data && push
		foreach ($ids AS $id) {
			$dataDs->deleteByModuleId($id);
			$pushDs->deleteByModuleId($id);
			$imageSrv->clearFolder($id);
		}
		
		//structure
		$ds = $this->_getStructureDs();
		foreach ($names AS $name) {
			$ds->deleteStruct($name);
		}
		
		//segment
		$this->_getSegmentDs()->deleteSegmentByPageid($pageid);
		
		//bak
		$bakDs->deleteByPageId($pageid);
		if ($pageInfo['page_type'] == PwDesignPage::PORTAL) {
			Wind::import('SRV:design.dm.PwDesignPortalDm');
			$dm = new PwDesignPortalDm($pageInfo['page_unique']);
			$dm->setTemplate(0);
			$this->_getPortalDs()->updatePortal($dm);
			$this->_getDesignService()->clearTemplate($pageid);
		}
		$this->_getDesignService()->clearCompile();
		$this->showMessage("operate.success");
	}
	
	private function _getDesignService() {
		return Wekit::load('design.srv.PwDesignService');
	}
	
	private function _getModuleDs() {
		return Wekit::load('design.PwDesignModule');
	}
	
	private function _getPageDs() {
		return Wekit::load('design.PwDesignPage');
	}
	
	private function _getStructureDs() {
		return Wekit::load('design.PwDesignStructure');
	}

	
	private function _getBakDs() {
		return Wekit::load('design.PwDesignBak');
	}
	
	private function _getSegmentDs() {
		return Wekit::load('design.PwDesignSegment');
	}
	
	private function _getDataDs() {
		return Wekit::load('design.PwDesignData');
	}
	
	private function _getPushDs() {
		return Wekit::load('design.PwDesignPush');
	}
	
	private function _getPortalDs() {
		return Wekit::load('design.PwDesignPortal');
	}
}