<?php
Wind::import('APPS:appcenter.service.srv.iPwInstall');

/**
 * 实名认证清理接口
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class AppVerify_VerifyUninstall implements iPwInstall {
	
	/**
	 * 注册主导航
	 */
	public function install($install) {
		$emailTitle = '来自{sitename}的认证邮件';
		$emailContent = '尊敬的{username}，这是来自{sitename}认证邮件。 <br/>请点击下面的链接进行认证：<br/> {url} <br/>如果不能点击链接，请复制到浏览器地址输入框访问。 <br/> <br/> {sitename} <br/> {time}';
		$config = new PwConfigSet('appVerify');
		$config->set('verify.email.title', $emailTitle)
			->set('verify.email.content', $emailContent)
			->flush();
		return true;
	}
	
	public function backUp($install) {
		return true;
	}
	
	public function revert($install) {
		return true;
	}
	
	public function rollback($install) {
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see AbstractPwAppUninstall::unInstall()
	 */
	public function unInstall($install) {
		$this->_loadConfigDs()->deleteConfig('appVerify');
		return true;
	}
	
	/**
	 * @return PwConfig
	 */
	private function _loadConfigDs() {
		return Wekit::load('config.PwConfig');
	}
}

?>