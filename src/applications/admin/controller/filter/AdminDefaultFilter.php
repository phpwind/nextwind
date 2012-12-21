<?php
Wind::import('WIND:filter.WindActionFilter');
/**
 * 后台管理平台默认过滤器
 * 后台管理平台默认过滤器，职责:<ol>
 * <li>设置后台所需全局变量信息</li>
 * <li>配置信息设置</li>
 * <li>检查后台用户是否登录</li>
 * </ol>
 * 
 * @author Qiong Wu <papa0924@gmail.com> 2011-10-13
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AdminDefaultFilter.php 22312 2012-12-21 08:03:00Z yishuo $
 * @package wind
 */
class AdminDefaultFilter extends WindActionFilter {
	
	/*
	 * (non-PHPdoc) @see WindHandlerInterceptor::preHandle()
	 */
	public function preHandle() {
		$module = $this->router->getModule();
		$controller = $this->router->getController();
		$action = $this->router->getAction();
		if (in_array("$module/$controller/$action", 
			array('default/index/login', 'default/index/showVerify'))) return;
		
		/* @var $userService AdminUserService */
		$userService = Wekit::load('ADMIN:service.srv.AdminUserService');
		/* @var $safeService AdminSafeService */
		$safeService = Wekit::load('ADMIN:service.srv.AdminSafeService');
		/* @var $founderService AdminFounderService */
		$founderService = Wekit::load('ADMIN:service.srv.AdminFounderService');
		
		/* @var $loginUser AdminUserBo */
		$loginUser = Wekit::getLoginUser();
		if (!$loginUser->isExists() || (!$founderService->isFounder($loginUser->getUsername()) && !$safeService->ipLegal(
			Wekit::app()->clientIp))) {
			if (!$this->getRequest()->getIsAjaxRequest()) {
				$this->forward->forwardAction('default/index/login');
			} else {
				$this->errorMessage->addError('logout', 'state');
				$this->errorMessage->sendError('ADMIN:login.fail.not.login');
			}
		}
		
		$_unVerifyTable = array('home', 'index', 'find');
		if (!in_array(strtolower($controller), $_unVerifyTable)) {
			if ($controller != 'adminlog') {
				$logService = Wekit::load('ADMIN:service.srv.AdminLogService');
				$logService->log($this->getRequest(), $loginUser->username, $module, $controller, 
					$action);
			}
			$_result = $userService->verifyUserMenuAuth($loginUser, $module, $controller, $action);
			if ($_result instanceof PwError) $this->errorMessage->sendError($_result->getError());
		}
	}
	
	/*
	 * (non-PHPdoc) @see WindHandlerInterceptor::postHandle()
	 */
	public function postHandle() {}
}

?>