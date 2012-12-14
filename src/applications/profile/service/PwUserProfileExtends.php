<?php

/**
 * 菜单扩展服务
 *
 * @author xiaoxia.xu <x_824@sina.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwUserProfileExtends.php 21826 2012-12-13 10:45:50Z jinlong.panjl $
 * @package src.products.u.service
 */
class PwUserProfileExtends extends PwBaseHookService {
	public $current = '';
	public $left = '';
	public $user = null;
	
	public function __construct(PwUserBo $userBo) {
		parent::__construct();
		$this->user = $userBo;
	}
	
	/**
	 * 设置当前的菜单项
	 *
	 * @param string $left
	 * @param string $tab
	 */
	public function setCurrent($left, $tab) {
		$this->current = $left . '_' . $tab;
		$this->left = $left;
	}
	
	/**
	 * 获得模板
	 *
	 * @param string $left
	 * @param string $tab
	 */
	public function createHtml($left, $tab) {
		return $this->runDo('createHtml', $left, $tab);
	}
	
	/**
	 * 执行
	 */
	public function execute() {
		if (($r = $this->runWithVerified('check')) instanceof PwError) {
			return $r;
		}
		return $this->runDo('execute');
	}
	
	/**
	 * 获得底部模板
	 *
	 */
	public function displayFootHtml() {
		return $this->runDo('displayFootHtml', $this->current);
	}
	
	/* (non-PHPdoc)
	 * @see PwBaseHookService::_getInterfaceName()
	 */
	protected function _getInterfaceName() {
		return 'PwProfileExtendsDoBase';
	}
}