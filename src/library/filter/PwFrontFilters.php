<?php
Wind::import('WIND:base.AbstractWindBootstrap');
/**
 * P9中的一些全局挂载
 * 
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwFrontFilters.php 23308 2013-01-08 07:02:16Z yishuo $
 * @package wind
 */
class PwFrontFilters extends AbstractWindBootstrap {
	/*
	 * (non-PHPdoc) @see WindHandlerInterceptor::preHandle()
	 */
	public function onCreate() {
		if (in_array(Wind::getAppName(), array('phpwind', 'pwadmin'))) {
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
		$_debug = Wekit::C('site', 'debug');
		if ($_debug == !Wind::$isDebug) Wind::$isDebug = $_debug;
		if ('phpwind' == Wind::getAppName()) {
			error_reporting($_debug ? E_ALL ^ E_NOTICE ^ E_DEPRECATED : E_ERROR | E_PARSE);
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