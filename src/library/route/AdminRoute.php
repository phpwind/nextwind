<?php
Wind::import('WIND:router.route.AbstractWindRoute');
/**
 * 后台路由，主要处理应用的后台url
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AdminRoute.php 21653 2012-12-12 06:17:21Z long.shi $
 * @package library
 */
class AdminRoute extends AbstractWindRoute {
	/*
	 * (non-PHPdoc) @see AbstractWindRoute::build()
	 */
	public function build($router, $action, $args = array()) {
		list($_a, $_c, $_m, $args) = $this->_resolveMca($router, $action, $args);
		if ($_m && $_m !== $router->getDefaultModule()) $args[$router->getModuleKey()] = $_m;
		if ($_c && $_c !== $router->getDefaultController()) $args[$router->getControllerKey()] = $_c;
		if ($_a && $_a !== $router->getDefaultAction()) $args[$router->getActionKey()] = $_a;
		$_url = Wind::getApp()->getRequest()->getScript() . '?' . WindUrlHelper::argsToUrl($args);
		return $_url;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindRoute::match()
	 */
	public function match($request) {
		return null;
	}
	
	/**
	 * 分析参数
	 *
	 * @param AbstractWindRouter $router
	 * @param string $action
	 * @param array $args
	 * @return array
	 */
	private function _resolveMca($router, $action, $args) {
		list($action, $_args) = explode('?', $action . '?');
		$args = array_merge($args, ($_args ? WindUrlHelper::urlToArgs($_args, false) : array()));
		$action = trim($action, '/');
		$tmp = explode('/', $action . '/');
		end($tmp);
		if (5 === count($tmp) && !strncasecmp('app/', $action, 4)) {
			list($_a, $_c, $_app_name, $_m) = array(prev($tmp), prev($tmp), prev($tmp), prev($tmp));
			$args['app'] = $_app_name;
		} else {
			list($_a, $_c, $_m) = array(prev($tmp), prev($tmp), prev($tmp));
		}
		return array($_a, $_c, $_m, $args);
	}
}

?>