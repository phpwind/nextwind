<?php
Wind::import('LIB:base.PwBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ExportController.php 22471 2012-12-24 12:06:23Z gao.wanggao $ 
 * @package 
 */
class ExportController  extends PwBaseController {
	
	public  function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		Wekit::load('design.PwDesignPermissions');
		$permissions = $this->_getPermissionsService()->getPermissionsForUserGroup($this->loginUser->uid);
		if ($permissions < PwDesignPermissions::IS_DESIGN ) $this->showError("DESIGN:permissions.fail");
	}
	
	public function run() {
	
	}
	
	public function dorunAction() {
		$pageid = (int)$this->getInput('pageid', 'get');
		Wind::import('SRV:design.bo.PwDesignPageBo');
    	$pageBo = new PwDesignPageBo($pageid);
		$pageInfo = $pageBo->getPage();
		if (!$pageInfo) $this->showError("operate.fail");
		if ($pageInfo['page_type'] == PwDesignPage::PORTAL) { //$this->showError("DESIGN:page.emport.fail");
			$portal = $this->_getPortalDs()->getPortal($pageInfo['page_unique']);
			if ($portal['template']) {
				$this->doZip($pageBo);	
			} else {
				$this->doTxt($pageInfo);
			}
		} else {
			$this->doZip($pageBo);	
			//$this->doTxt($pageInfo);
		}
		$this->_getDesignService()->clearCompile();
		$this->showMessage("operate.success");
	}
	
	protected function doZip($pageBo) {
		Wind::import('SRV:design.srv.PwDesignExportZip');
		$srv = new PwDesignExportZip($pageBo);
		$content = $srv->zip();
		$pageInfo = $pageBo->getPage();
		$this->forceDownload($content, $pageInfo['page_name'], 'zip');
	}
	
	/**
	 * 导出当前页设计数据  已无用
	 * Enter description here ...
	 */
	protected function doTxt($pageInfo) {	
		Wind::import('SRV:design.srv.PwDesignExportTxt');
		$srv = new PwDesignExportTxt($pageInfo);
		$msg = $srv->txt();
		$this->forceDownload($msg['content'], $msg['filename'], $msg['ext']);
	}
	
	protected function forceDownload($string, $filename, $ext = 'txt') {
		$router = Wind::getComponent('router');
		$agent = $router->request->getServer('HTTP_USER_AGENT');
		if ((preg_match("/MSIE/", $agent))) {
			$filename = urlencode($filename);
		}
		$filename .= '.'.$ext;
		ob_end_clean();
		header('Content-Encoding: none');
		header("Content-type: application/octet-stream");
		header('Content-type: text/html; charset='.Wekit::app()->charset.'');
        header("Accept-Ranges: bytes");
        header("Accept-Length: ".Pw::strlen($string));
        header("Content-Disposition: attachment; filename=".$filename);
        echo $string;
        @flush();
		@ob_flush();
		exit;
	}
	
	private function _getDesignService() {
		return Wekit::load('design.srv.PwDesignService');
	}
	
	private function _getPermissionsService() {
		return Wekit::load('design.srv.PwDesignPermissionsService');
	}
	
	private function _getPageDs() {
		return Wekit::load('design.PwDesignPage');
	}
	
	private function _getPortalDs() {
		return Wekit::load('design.PwDesignPortal');
	}
}
?>