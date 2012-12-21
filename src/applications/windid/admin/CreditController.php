<?php
Wind::import('APPS:windid.admin.WindidBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: CreditController.php 22007 2012-12-18 06:23:13Z gao.wanggao $ 
 * @package 
 */
class CreditController extends WindidBaseController { 

	public function run() {
		$service = $this->_getConfigDs();
		$config = $service->getValues('credit');
		$this->setOutput($config['credits'], 'credits');	
	}
	
	public function docreditAction() {
		$credits = $this->getInput('credits', 'post');
		$newcredits = $this->getInput('newcredits', 'post');
		Wind::import('WINDID:service.config.srv.WindidCreditSetService');
		$srv = new WindidCreditSetService();
		$srv->setCredits($credits, $newcredits);
		$this->showMessage('WINDID:success');
	}
	
	public function doDeletecreditAction() {
		$creditId = (int) $this->getInput("creditId");
		if ($creditId < 5) $this->showError('WINDID:fail');
		Wind::import('WINDID:service.config.srv.WindidCreditSetService');
		
		$srv = new WindidCreditSetService();
		if ((!$srv->deleteCredit($creditId))) {
			$this->showError('WINDID:fail');
		}
		$this->showMessage('WINDID:success');
	}
	
	private function _getConfigDs() {
		return Windid::load('config.WindidConfig');
	}
}
?>