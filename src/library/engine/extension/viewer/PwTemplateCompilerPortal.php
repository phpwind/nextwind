<?php
Wind::import('WIND:viewer.AbstractWindTemplateCompiler');
Wind::import('SRV:design.srv.PwPortalCompile');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwTemplateCompilerPortal.php 22740 2012-12-27 02:34:43Z gao.wanggao $ 
 * @package 
 */
class PwTemplateCompilerPortal extends AbstractWindTemplateCompiler {
	
	protected $srv;
	private $_url;
	private $_router;
	
	public function compile($key, $content) {
		$viewTemplate = Wind::getComponent('template');
		$this->_router();
		list($pageName, $unique) = $this->_pageName();
		if (!$pageName && !$unique) {
			$content = str_replace('<pw-start/>', '', $content);
			$content = str_replace('<pw-end/>', '', $content);
			return $viewTemplate->compileStream($content, $this->windViewerResolver);
		}
		$this->srv = Wekit::load('design.srv.PwDesignCompile');
		$this->srv->setIsDesign($this->getRequest()->getPost('design'));
		$_pk = $unique ? $this->getRequest()->getGet($unique) : '';
		$this->srv->beforeDesign($this->_router, $pageName, $_pk);
		$pageBo = $this->srv->getPageBo();
		$this->srv->setPermission();
		//对模版进行编译
		$portalSrv = new PwPortalCompile($pageBo);
		
		$content = $portalSrv->compileTpl($content);
		
		//对新模版进行编译
		$content = $portalSrv->compileDesign($content);
		$isCompile = $this->srv->isPortalCompile();
		if ($isCompile == 3) {
			$portalSrv->compilePortal($content);
		}
		//转换Pw标签
		$content = $this->compileStart($content, $_pk, $this->_url);
		$content = $this->compileCss($content, $pageBo);
		$content = $this->compileSign($content);
		$content = $this->compileDrag($content);
		$content = $this->compileTitle($content);
		$content = $this->compileList($content);
		$content = $this->compileEnd($content);
		$content =  $viewTemplate->compileStream($content, $this->windViewerResolver);
		$this->srv->refreshPage();//srv 里判断刷新权限
		return $content;
	}
	
	protected function compileStart($content, $pk, $url) {
		$start = $this->srv->startDesign($pk, $url);
		return str_replace('<pw-start/>', $start, $content);
	}
	
	protected function compileSign($content) {
		$in = array(
			'<pw-head/>',
			'<pw-navigate/>',
			'<pw-footer/>',
		);
		$out = array(
			'<!--# if($portal[\'header\']): #--><template source=\'TPL:common.header\' load=\'true\' /><!--# endif; #-->',
			'<!--# if($portal[\'navigate\']): #--><div class="bread_crumb">{@$headguide|html}</div><!--# endif; #-->',
			'<!--# if($portal[\'footer\']): #--><template source=\'TPL:common.footer\' load=\'true\' /><!--# endif; #-->',
		);
		return str_replace($in, $out, $content);
	}
	
	protected function compileCss($content, $pageBo) {
		$dir = $pageBo->getTplPath();
		$url =  WindUrlHelper::checkUrl(PUBLIC_THEMES . '/portal/local/' . $dir, PUBLIC_URL);
		if (preg_match_all('/\{@G:design.url.(\w+)}/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
				$replace = $url . '/' . $v;
	    		$content = str_replace($matches[0][$k], $replace, $content);
    		}
		}			
		return $content;
	}
	
	protected function compileTitle($content) {
		if (preg_match_all('/\<pw-title\s*id=\"(\w+)\"\s*[>|\/>](.+)<\/pw-title>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
    			$title = $this->srv->compileTitle($v);
	    		$content = str_replace($matches[0][$k], $title, $content);
    		}
		}
		return $content;
	}
	
	protected function compileList($content) {
		if (preg_match_all('/\<pw-list\s*id=\"(\d+)\"\s*[>|\/>](.+)<\/pw-list>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
    			$list = $this->srv->compileList($v);
	    		$content = str_replace($matches[0][$k], $list, $content);
    		}
		}
		return $content;
	}
	
	protected function compileDrag($content) {
		if (preg_match_all('/\<pw-drag\s*id=\"(\w+)\"\s*\/>/isU',$content, $matches)) {
			foreach ($matches[1] AS $k=>$v) {
				if (!$v) continue;
    			$segment = $this->srv->compileSegment($v);
	    		$content = str_replace($matches[0][$k], $segment, $content);
    		}
		}
		return $content;
	}
	
	/**
	 * 必须放在转换的最后一步
	 */
	protected function compileEnd($content) {
		//$viewTemplate = Wind::getComponent('template');
		$end = $this->srv->afterDesign();
		return str_replace('<pw-end/>', $end, $content);
		//return $viewTemplate->compileStream($content, $this->windViewerResolver);
	}
	
	private function _router() {
		$router = Wind::getComponent('router');
    	$m = $router->getModule(); 
    	$c = $router->getController(); 
    	$a = $router->getAction();
    	$this->_router = $m.'/'.$c.'/'.$a;
    	$this->_url = urlencode($router->request->getHostInfo() .$router->request->getRequestUri());
	}
	
	private function _pageName() {
		$sysPage = Wekit::load('design.srv.router.PwDesignRouter')->get();
		if ($this->_router && isset($sysPage[$this->_router])){ 
			return $sysPage[$this->_router];
		}
		return array();
	}
	
}
?>