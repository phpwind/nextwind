<?php
Wind::import('WINDID:service.notify.dm.WindidNotifyLogDm');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyServer.php 22632 2012-12-26 05:27:26Z gao.wanggao $ 
 * @package 
 */
class WindidNotifyServer {

	public $notify = array(
		'101'=>'register',
		'111'=>'synLogin',
		'112'=>'synLogout',
	
		'201'=>'editUser',
		'202'=>'editUserInfo',
		'203'=>'uploadAvatar',
		'211'=>'editCredit',
		'222'=>'editMessageNum',
	
		'301'=>'deleteUser',

	);

	protected $logId = array();
	
	public function send() {
		$this->queueSend();
		$this->updateLog();
		return true;
	}
	
	public function logSend($logid) {
		$time = Windid::getTime();
		$logDs = $this->_getNotifyLogDs();
		$log = $logDs->getLog($logid);
		if (!$log) return false;
		$app = $this->_getAppDs()->getApp($log['appid']);
		$notify = $this->_getNotifyDs()->getNotify($log['nid']);
		$array = array(
			'windidkey'=>WindidUtility::appKey($log['appid'],$time, $app['secretkey']),
			'operation'=>$notify['operation'],
			'uid'=>(int)$notify['param'],
			'clientid'=>$log['appid'],
			'time'=>$time
		);
		
		$url = WindidUtility::buildClientUrl($app['siteurl'], $app['apifile']).http_build_query($array);
		$result = WindidUtility::buildRequest($url);
		$dm = new WindidNotifyLogDm($logid);
		if ($result == 'seccess') {
			$dm->setComplete(1)->setIncreaseSendNum(1);
			$logDs->updateLog($dm);
			return true;
		} else {
			$dm->setComplete(0)->setIncreaseSendNum(1)->setReason('fail');
			$logDs->updateLog($dm);
			return false;
		}
	}
	

	/**
	 * 通知客户端
	 * Enter description here ...
	 * @param unknown_type $operation
	 * @param unknown_type $data
	 */
	protected function queueSend($start = 0) {
		$time = Windid::getTime();
		$appids = $nids = array();
		$logDs = $this->_getNotifyLogDs();
		$queue = $logDs->getList(0, 0, 10, $start, 0);
		if (!$queue) return false;
		foreach ($queue AS $v) {
			$appids[] = $v['appid'];
			$nids[] = $v['nid'];
		}
		$apps = $this->_getAppDs()->fetchApp(array_unique($appids));
		$notifys = $this->_getNotifyDs()->fetchNotify(array_unique($nids));

		$postData = $urls = array();
		foreach ($queue AS $k=>$v) {
			$appid = $v['appid'];
			$nid = $v['nid'];
			$array = array(
				'windidkey'=>WindidUtility::appKey($v['appid'],$time, $apps[$appid]['secretkey']),
				'operation'=>$notifys[$nid]['operation'],
				'uid'=>(int)$notifys[$nid]['param'],
				'clientid'=>$v['appid'],
				'time'=>$time
			);
			$urls[$k] = WindidUtility::buildClientUrl($apps[$appid]['siteurl'] , $apps[$appid]['apifile']).http_build_query($array);
		
		}
		if (!$urls) return false;
		$result = WindidUtility::buildMultiRequest($urls);
		sleep(3);
		$this->logId = $this->logId + $result;
		$start += 10;
		$this->queueSend($start);
	}
	
	protected function updateLog() {
		$logDs = $this->_getNotifyLogDs();
		foreach ($this->logId AS $k=>$v){
			$dm = new WindidNotifyLogDm($k);
			if ($v == 'seccess'){
				$dm->setComplete(1)->setIncreaseSendNum(1);
			} else {
				$dm->setComplete(0)->setIncreaseSendNum(1)->setReason('fail');
			}
			$logDs->updateLog($dm);
		}
		return true;
	}

	private function _getUserDs() {
		return Windid::load('user.WindidUser');
	}
	
	private function _getAppDs() {
		return Windid::load('app.WindidApp');
	}
	
	private function _getNotifyDs() {
			return Windid::load('notify.WindidNotify');
	}
	
	private function _getNotifyLogDs() {
			return Windid::load('notify.WindidNotifyLog');
	}
}
?>