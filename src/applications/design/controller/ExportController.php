<?php
Wind::import('LIB:base.PwBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: yishuo $>
 * @author $Author: yishuo $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ExportController.php 20274 2012-10-25 07:49:56Z yishuo $ 
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
		$pageDs = $this->_getPageDs();
		$pageInfo = $pageDs->getPage($pageid);
		if (!$pageInfo) $this->showError("operate.fail");
		if ($pageInfo['page_type'] == PwDesignPage::PORTAL) { //$this->showError("DESIGN:page.emport.fail");
			$portal = $this->_getPortalDs()->getPortal($pageInfo['page_unique']);
			if ($portal['template']) {
				$this->doZip($pageInfo);	
			} else {
				$this->doTxt($pageInfo);
			}
		} else {
			$this->doTxt($pageInfo);
		}
		$this->_getDesignService()->clearCompile();
		$this->showMessage("operate.success");
	}
	
	protected function doZip($pageInfo) {
		Wind::import('SRV:design.srv.PwDesignExportZip');
		$srv = new PwDesignExportZip($pageInfo['page_id']);
		$content = $srv->zip();
		$this->forceDownload($content, $pageInfo['page_name'], 'zip');
	}
	
	/**
	 * 导出当前页设计数据
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