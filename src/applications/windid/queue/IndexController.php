<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: IndexController.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */
class IndexController extends PwBaseController {
	
	public function run() {
		if(!ini_get('safe_mode')){
			ignore_user_abort(true);
			set_time_limit(0);
		}
		$this->_getNotifyService()->send();
		echo 'success';
		exit;
	}
	
	private function _getNotifyService() {
		return Windid::load('notify.srv.WindidNotifyServer');
	}
}
?>