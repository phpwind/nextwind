<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * controller 基类
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @license http://www.phpwind.com
 * @version $Id: PwBaseController.php 23183 2013-01-07 03:02:15Z jieyin $
 * @package lib.base.controller
 */
class PwBaseController extends WindController {

	/**
	 * 当前用户信息
	 *
	 * @var PwUserBo $loginUser
	 */
	protected $loginUser;
	protected $_m;
	protected $_c;
	protected $_a;
	protected $_mc;
	protected $_mca;
	protected $_compileDir = '';
	
	/*
	 * (non-PHPdoc) @see WindSimpleController::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {

		$this->_m = $handlerAdapter->getModule();
		$this->_c = $handlerAdapter->getController();
		$this->_a = $handlerAdapter->getAction();
		$this->_mc = $this->_m . '/' . $this->_c;
		$this->_mca = $this->_mc . '/' . $this->_a;
		$this->_setPreCache($this->_m, $this->_mc, $this->_mca);
		
		$this->loginUser = Wekit::getLoginUser();
		$this->_setPreHook($this->_m, $this->_mc, $this->_mca);

		$config = Wekit::C('site');
		if ($config['visit.state'] > 0) {
			$service = Wekit::load('site.srv.PwSiteStatusService');
			$resource = $service->siteStatus($this->loginUser, $config);
			if ($resource instanceof PwError) {
				if (!($config['visit.state'] == 1 && $this->_mc == 'u/login')) {
					$this->showError($resource->getError());
				}
			}
		}
		if (!in_array($this->_mc, array('u/login', 'u/register', 'u/findPwd')) && !$this->loginUser->getPermission('allow_visit')) {
			if ($this->loginUser->isExists()) {
				if ($this->_mca != 'u/login/logout') $this->showError(array('permission.visit.allow', array('{grouptitle}' => $this->loginUser->getGroupInfo('name'))));
			} else {
				$this->forwardAction('u/login/run');
			}
		}
		if ($config['refreshtime'] > 0 && Wind::getApp()->getRequest()->isGet() && !Wind::getApp()->getRequest()->getIsAjaxRequest()) {
			if (Wekit::app()->lastRequestUri == Wekit::app()->requestUri && (Wekit::app()->lastvisit + $config['refreshtime']) > Pw::getTime()) {
				$this->showError('SITE:refresh.fast');
			}
		}
		$debug = $config['debug'] || !$config['css.compress'];
		Wekit::setGlobal(array('debug' => $debug ? '/dev' : '/build'), 'theme');
		$this->setTheme('site', null);
	}

	protected function _setPreCache($m, $mc, $mca) {
		$precache = Wekit::V('precache');
		if (isset($precache[$m])) Wekit::cache()->preset($precache[$m]);
		if (isset($precache[$mc])) Wekit::cache()->preset($precache[$mc]);
		if (isset($precache[$mca])) Wekit::cache()->preset($precache[$mca]);
	}

	protected function _setPreHook($m, $mc, $mca) {
		$prehook = Wekit::V('prehook');
		PwHook::preset($prehook['ALL']);
		PwHook::preset($prehook[$this->loginUser->isExists() ? 'LOGIN' : 'UNLOGIN']);
		if (isset($prehook[$m])) PwHook::preset($prehook[$m]);
		if (isset($prehook[$mc])) PwHook::preset($prehook[$mc]);
		if (isset($prehook[$mca])) PwHook::preset($prehook[$mca]);
	}


	/**
	 * 显示信息
	 *
	 * @param string $message 消息信息
	 * @param string $referer 跳转地址
	 * @param boolean $referer 是否刷新页面
	 * @see WindSimpleController::showMessage()
	 */
	protected function showMessage($message = '', $referer = '', $refresh = false) {
		$this->addMessage('success', 'state');
		$this->addMessage($this->forward->getVars('data'), 'data');
		$this->showError($message, $referer, $refresh);
	}

	/**
	 * 显示错误
	 *
	 * @param string $error 消息信息
	 * @param string $referer 跳转地址
	 * @param boolean $referer 是否刷新页面
	 */
	protected function showError($error = '', $referer = '', $refresh = false) {
		if ($referer) {
			$_referer = explode('#', $referer, 2);
			$referer = WindUrlHelper::createUrl($_referer[0], array(), 
				isset($_referer[1]) ? $_referer[1] : '');
		}
		$this->addMessage($referer, 'referer');
		$this->addMessage($refresh, 'refresh');
		parent::showMessage($error);
	}
	
	/*
	 * (non-PHPdoc) @see WindSimpleController::setDefaultTemplateName()
	 */
	protected function setDefaultTemplateName($handlerAdapter) {
		$this->setTemplate($handlerAdapter->getController() . '_' . $handlerAdapter->getAction());
	}
	
	/*
	 * (non-PHPdoc) @see WindSimpleController::afterAction()
	 */
	public function afterAction($handlerAdapter) {
		$this->runDesign();
		$this->setOutput($this->loginUser, 'loginUser');
		$this->updateOnline();
		$this->setOutput($this->runCron(), 'runCron');
		$this->_compileDir && $this->getForward()->getWindView()->compileDir .= $this->_compileDir;
	}

	/**
	 * 风格设置
	 *
	 * 设置当前页面风格，需要两个参数，$type风格类型，$theme该类型下风格
	 *
	 * @see WindSimpleController::setTheme()
	 * @param string $type 风格类型(site,space,area...)
	 * @param string $theme 风格别名
	 */
	protected function setTheme($type, $theme) {
		$config = Wekit::C('site');
		$themePack = $config['theme.' . $type . '.pack'];
		$themePack = 'THEMES:' . $themePack;
		// 风格预览，管理员权限
		if ($style = Pw::getCookie('style_preview')) {
			list($s_theme, $s_type) = explode('|', $style, 2);
			if ($s_type == $type) {
				$theme = $s_theme;
				$config['theme.' . $type . '.default'] = $theme;
				Wekit::C()->site = new PwConfigIniBo($config);
			}
		}
		if (!$theme) $theme = $config['theme.' . $type . '.default'];
		parent::setTheme($theme, $themePack);
	}

	/**
	 * action Hook 注册
	 *
	 * @param string $registerKey 扩展点别名
	 * @param PwBaseHookService $bp        	
	 * @throws PwException
	 * @return void
	 */
	protected function runHook($registerKey, $bp) {
		if (!$registerKey) return;
		if (!$bp instanceof PwBaseHookService) {
			throw new PwException('class.type.fail', 
				array(
					'{parm1}' => 'src.library.base.PwBaseController.runHook', 
					'{parm2}' => 'PwBaseHookService', 
					'{parm3}' => get_class($bp)));
		}
		if (!$filters = PwHook::getRegistry($registerKey)) return;
		if (!$filters = PwHook::resolveActionHook($filters, $bp)) return;
		$args = func_get_args();
		$_filters = array();
		foreach ($filters as $key => $value) {
			$args[0] = isset($value['method']) ? $value['method'] : '';
			$_filters[] = array('class' => $value['class'], 'args' => $args);
		}
		$this->resolveActionFilter($_filters);
	}
	
	/**
	 * 门户流程控制
	 * Enter description here ...
	 */
	protected function runDesign() {
		$pageName = $unique = '';
		$pk = 0;
		if ($this->_mca == 'bbs/read/run') return true;//帖子阅读页在ReadController里处理
		$sysPage = Wekit::load('design.srv.router.PwDesignRouter')->get();
		if (!isset($sysPage[$this->_mca]))return false;
		list($pageName, $unique) = $sysPage[$this->_mca];
		$unique && $pk = $this->getInput($unique, 'get');
		if (!$pk) return false;
		Wind::import('SRV:design.bo.PwDesignPageBo');
    	$bo = new PwDesignPageBo();
    	$pageid = $bo->getPageId($this->_mca, $pageName, $pk);
		$pageid && $this->forward->getWindView()->compileDir = 'DATA:compile.design.'.$pageid;
		return true;
	}
	
	protected function addCompileDir($dir) {
		$this->_compileDir .= '.' . $dir;
	}
	
	/**
	 * 首页绑定计划任务
	 *
	 * @return string Ambigous string>
	 */
	protected function runCron() {
		$ishome = false;
		$homeRouter = Wekit::C('site', 'homeRouter');
		if (!$homeRouter) return '';
		$request = $this->getRequest();
		if ($this->_m == $homeRouter['m'] && $this->_c == $homeRouter['c'] && $this->_a == $homeRouter['a']) {
			$ishome = true;
		}
		unset($homeRouter['m'], $homeRouter['c'], $homeRouter['a']);
		foreach ($homeRouter as $k => $v) {
			if (!$k) continue;
			if ($request->getAttribute($k) != $v) $ishome = false;
		}
		if (!$ishome) return '';
		$time = Pw::getTime();
		$cron = Wekit::load('cron.PwCron')->getFirstCron();
		if (!$cron || $cron['next_time'] > $time) return '';
		return WindUrlHelper::createUrl('cron/index/run/');
	}

	/**
	 * 在线服务  	
	 */
	protected function updateOnline() {
		if ($this->loginUser->uid > 0 && $this->_mca == 'bbs/read/run') return false; //帖子阅读页在ReadController里处理
		if ($this->loginUser->uid > 0 && $this->_m == 'space') return false; //空间在spaceBaseController里处理
		$online = Wekit::load('online.srv.PwOnlineService');
		// $service->clearNotOnline(); // 由计划任务清理
		if ($this->loginUser->uid > 0 && $this->_mca == 'bbs/thread/run') {
			$createdTime = $online->forumOnline($this->getInput('fid'));
		} else {
			$request = $this->getRequest();
			$clientIp = $request->getClientIp();
			$createdTime = $online->visitOnline($clientIp);
		}
		if (!$createdTime) return false;
		$dm = Wekit::load('online.dm.PwOnlineDm');
		$time = Pw::getTime();
		if ($this->loginUser->uid > 0) {
			$dm->setUid($this->loginUser->uid)->setUsername($this->loginUser->username)->setModifytime($time)->setCreatedtime($createdTime)->setGid($this->loginUser->gid)->setFid($this->getInput('fid', 'get'))->setRequest($this->_mca);
			Wekit::load('online.PwUserOnline')->replaceInfo($dm);
		} else {
			$dm->setIp($clientIp)->setCreatedtime($createdTime)->setModifytime($time)->setFid($this->getInput('fid', 'get'))->setTid($this->getInput('tid', 'get'))->setRequest($this->_mca);
			Wekit::load('online.PwGuestOnline')->replaceInfo($dm);
		}
	}
}