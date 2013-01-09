<?php
Wind::import('WIND:filter.WindActionFilter');
/**
 * 系统默认全局filter
 *
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-2
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwFilter.php 23173 2013-01-07 02:09:11Z gao.wanggao $
 * @package src
 * @subpackage library.filter
 */
class PwFilter extends WindActionFilter {
	
	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		/* 模板变量设置 */
		$url = array();
		$app = Wekit::app();
		$url['base'] = $app->baseUrl;
		$url['res'] = $app->res;
		$url['css'] = $app->css;
		$url['images'] = $app->images;
		$url['js'] = $app->js;
		$url['attach'] = $app->attach;
		$url['extres'] = $app->extres;
		Wekit::setGlobal($url, 'url');
	}
	
	/* (non-PHPdoc)
	 * @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {
		//门户管理模式 编译目录切换
		if ($this->getRequest()->getPost('design')) {
			$loginUser = Wekit::getLoginUser();
			$designPermission = $loginUser->getPermission('design_allow_manage.push');
			if ($designPermission > 0) {
				$dir = Wind::getRealDir('DATA:design.template');
				if (is_dir($dir)) WindFolder::rm($dir, true);
				$this->forward->getWindView()->compileDir = 'DATA:design.template';
			}
		}
		
		// SEO settings
		Wind::import('SRV:seo.bo.PwSeoBo');
		$sitename = Wekit::C('site', 'info.name');
		PwSeoBo::set('{sitename}', $sitename);
		Wekit::setGlobal(NEXT_VERSION . ' ' . NEXT_RELEASE, 'version');
		Wekit::setGlobal(PwSeoBo::getData(), 'seo');
		
		$this->setOutput($this->getRequest()->getIsAjaxRequest() ? '1' : '0', '_ajax_');
		
		/*[设置给PwGlobalFilters需要的变量]*/
		$_var = array(
			'current' => $this->forward->getWindView()->templateName,
			'a' => $this->router->getAction(),
			'c' => $this->router->getController(),
			'm' => $this->router->getModule());
		$this->getResponse()->setData($_var, '_aCloud_');
		Wekit::load('APPS:appcenter.service.srv.PwDebugApplication')->compile();
	}
}
?>