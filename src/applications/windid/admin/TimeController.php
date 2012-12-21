<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('APPS:windid.admin.WindidBaseController');

/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: TimeController.php 22007 2012-12-18 06:23:13Z gao.wanggao $ 
 * @package 
 */
class TimeController extends WindidBaseController {
	
	public function run() {
		$config = $this->_getConfigDs()->getValues('site');
		$this->setOutput($config, 'config');
	}
	
	public function dorunAction() {
		Wind::import('WINDID:service.config.srv.WindidConfigSet');
		$config = new WindidConfigSet('site');
		$config->set('timezone', intval($this->getInput('timeTimezone', 'post')))
			->set('timecv', intval($this->getInput('timecv', 'post')))->flush();
		$this->showMessage('ADMIN:success');
	}
	
	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
	
}
?>