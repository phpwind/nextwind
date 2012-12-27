<?php
Wind::import('WIND:base.AbstractWindBootstrap');
/**
 * P9中的一些全局挂载
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwFrontFilters.php 22592 2012-12-25 11:38:24Z yetianshi $
 * @package wind
 */
class PwFrontFilters extends AbstractWindBootstrap {
	/*
	 * (non-PHPdoc) @see WindHandlerInterceptor::preHandle()
	 */
	public function onCreate() {
		if (in_array(Wind::getAppName(), array('phpwind','pwadmin'))) {
			//云应用监听sql执行
			WindFactory::_getInstance()->loadClassDefinitions(
				array(
					'sqlStatement' => array(
						'proxy' => 'WIND:filter.proxy.WindEnhancedClassProxy', 
						'listeners' => array('LIB:compile.acloud.PwAcloudDbListener'))));
		}
		if (!is_file(Wind::getRealPath('DATA:install.lock', true))) {
			Wind::getApp()->getResponse()->sendRedirect("install.php");
		}
		Wekit::createapp(Wind::getAppName());
		if ('phpwind' == Wind::getAppName()) {
			error_reporting(
				Wekit::C('site', 'debug') ? E_ALL ^ E_NOTICE ^ E_DEPRECATED : E_ERROR | E_PARSE);
			set_error_handler(array($this->front, '_errorHandle'), error_reporting());
		}
		$this->_convertCharsetForAjax();
		if ($components = Wekit::C('components')) {
			Wind::getApp()->getFactory()->loadClassDefinitions($components);
		}
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindBootstrap::onStart()
	 */
	public function onStart() {
		Wekit::app()->init($this->front);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindBootstrap::onResponse()
	 */
	public function onResponse() {
		Wekit::app()->beforeResponse($this->front);
	}

	/**
	 * ajax递交编码转换
	 */
	private function _convertCharsetForAjax() {
		if (!Wind::getApp()->getRequest()->getIsAjaxRequest()) return;
		$toCharset = Wind::getApp()->getResponse()->getCharset();
		if (strtoupper(substr($toCharset, 0, 2)) != 'UT') {
			$_tmp = array();
			foreach ($_POST as $key => $value) {
				$key = WindConvert::convert($key, $toCharset, 'UTF-8');
				$_tmp[$key] = WindConvert::convert($value, $toCharset, 'UTF-8');
			}
			$_POST = $_tmp;
		}
	}
}