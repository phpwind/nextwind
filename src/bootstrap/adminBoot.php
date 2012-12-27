<?php
Wind::import('SRC:bootstrap.phpwindBoot');
Wind::import('ADMIN:service.srv.AdminUserService');

/**
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: adminBoot.php 22579 2012-12-25 10:26:38Z xiaoxia.xuxx $
 * @package wekit
 */
class adminBoot extends phpwindBoot {
	/**
	 * 后台菜单访问路径，
	 * 默认菜单地址‘APP:admin.conf.mainmenu.php’
	 *
	 * @var string
	 */
	public $menuPath = 'ADMIN:conf.mainmenu.php';
	/**
	 * 后台home页管理链接地址，
	 * 默认‘APP:admin.controller.HomeController’
	 *
	 * @var string
	 */
	public $homeLink = 'home/run';
	/**
	 * 搜索功能相关设置，
	 * 后台搜索功能是依赖于搜索文件的
	 * 搜索文件位置i18n/language/admin/searchFile
	 * 将搜索文件存放在语言包中，并指定相关搜索文件
	 *
	 * @var string
	 */
	public $searchFile = 'search';
	/**
	 * 后台log记录
	 *
	 * @var string
	 */
	public $logFile = 'DATA:log.admin_log.php';
	/**
	 * 数据表标识，
	 * 默认为空，为空时将不对数据表进行额外标识，所建立的数据表将为原始数据表
	 * 注意：当同个数据库下存在两套后台系统时，需要设置该项进行数据分离，否则会引起数据冲突。
	 *
	 * @var string
	 */
	public $dbTableMark = '';
	/**
	 * db组建名称，
	 * 默认为系统默认的db组建‘db’,如果需要启用其他的db组建，请设置改项
	 *
	 * @var string
	 */
	public $dbComponentName = 'db';
	/**
	 * 设置应用依赖服务配置
	 *
	 * @var array
	 */
	protected $dependenceServiceDefinitions = array(
		'adminUserService' => array('path' => ''));
	
	public function __construct() {
		parent::__construct();
	}
	
	/* (non-PHPdoc)
	 * @see phpwindBoot::init()
	 */
	public function init($front = null) {
		parent::init($front);
		foreach ($this->dependenceServiceDefinitions as $alias => $definition) {
			if (!$definition) continue;
			Wind::registeComponent($definition, $alias);
		}
	}
	
	/* (non-PHPdoc)
	 * @see phpwindBoot::_getLoginUser()
	 */
	protected function _getLoginUser() {
		$userCookie = Pw::getCookie('AdminUser');
		/* @var $adminUserService AdminUserService */
		$adminUserService = Wekit::load('ADMIN:service.srv.AdminUserService');
		if ($userCookie) {
			list($type, $uid, $password) = explode("\t", Pw::decrypt($userCookie));
			/* @var $founderService AdminFounderService */
			$founderService = Wekit::load('ADMIN:service.srv.AdminFounderService');
			if ($founderService->isFounder($uid)) {
				$founders = $founderService->getFounders();
				list($md5pwd) = explode('|', $founders[$uid], 2);
				$userinfo = $adminUserService->verifyUserByUsername($uid);
				$userinfo['password'] = $md5pwd;
			} else {
				$userinfo = $adminUserService->loadUserService()->getUserByUid($uid);
			}
		} else {
			$password = '';
			$userinfo = array();
		}
		Wind::import('ADMIN:service.bo.AdminDefaultUserBo');
		$user = new AdminDefaultUserBo($userinfo);
		if (!$user->isExists() || Pw::getPwdCode($userinfo['password']) != $password) {
			$user->reset();
		}
		return $user;
	}
	
	/* (non-PHPdoc)
	 * @see phpwind::runApps()
	*/
	public function runApps($front = null) {}

	/* (non-PHPdoc)
	 * @see phpwindBoot::beforeResponse()
	 */
	public function beforeResponse($front = null) {
		//后台搜索，加亮搜索关键字
		$searchword = Wind::getComponent('request')->getGet('searchword');
		if ($searchword) {
			$content = ob_get_contents();
			ob_end_clean();
			$content = preg_replace('/('.preg_quote($searchword, '/').')([^">;]*<)(?!\/script|\/textarea)/si','<span class="red"><u>\\1</u></span>\\2', $content);
			$compress = Wind::getApp()->getConfig('compress');
			if (!$compress || !ob_start('ob_gzhandler')) ob_start();
			echo $content;
		}
	}
	
	/* (non-PHPdoc)
	 * @see phpwind::_initUser()
	*/
	protected function _initUser() {}

}