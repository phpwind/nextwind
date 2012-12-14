<?php
Wind::import('APPS:appcenter.service.srv.iPwInstall');
/**
 * 应用卸载基类
 * 
 * 如果你有额外数据清理，请实现uninstall方法
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AbstractPwAppUninstall.php 19751 2012-10-17 11:41:05Z long.shi $
 * @package lib
 */
abstract class AbstractPwAppUninstall implements iPwInstall {
	
	/*
	 * (non-PHPdoc) @see iPwInstall::install()
	 */
	public function install($install) {
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::backUp()
	 */
	public function backUp($install) {
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::revert()
	 */
	public function revert($install) {
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see iPwInstall::rollback()
	 */
	public function rollback($install) {
		return true;
	}
}

?>