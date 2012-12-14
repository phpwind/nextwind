<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: QueueController.php 21579 2012-12-11 08:33:39Z gao.wanggao $ 
 * @package 
 */
class QueueController extends PwBaseController {
	
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