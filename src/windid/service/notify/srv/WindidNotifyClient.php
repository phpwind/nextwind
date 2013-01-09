<?php
Wind::import('WINDID:service.notify.dm.WindidNotifyLogDm');
/**
 * 客户端通知服务
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifyClient.php 22910 2012-12-28 09:59:27Z gao.wanggao $ 
 * @package 
 */
class WindidNotifyClient {
	
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
	
	/**
	 * 写入通知信息
	 * Enter description here ...
	 * @param int $method
	 * @param multi $data
	 * @param bool $iswindid 
	 */
	public function send($method, $data, $iswindid = false) {
		$client = Windid::client();
		if ($client->windid == 'local' && !$iswindid) return true;
		$operation = array_search($method, $this->notify);
		if (!$operation) return false;
		$clientid = $iswindid ? 0 : $client->clientId;
		$time = Windid::getTime();
		$nid = $this->_getNotifyDs()->addNotify($clientid, $operation, $data, $time);
		if (!$nid) return false;
		$apps = $this->_getAppDs()->getList();
		$dms = array();
		foreach ($apps AS $val) {
			if (!$val['isnotify'] || $val['id'] == $clientid) continue;
			$dm = new WindidNotifyLogDm();
			$dm->setAppid($val['id'])->setNid($nid);
			$dms[] = $dm;
		}
		$this->_getNotifyLogDs()->multiAddLog($dms);
		register_shutdown_function(array($this,'shutdownSend'));
		return true;
	}
	
	public function shutdownSend() {
		$url = Windid::client()->serverUrl . '/windid/index.php?m=queue';
		WindidUtility::buildRequest($url, array(), false, 10);
		return true;
	}
	
	/**
	 * 同步登录登出
	 * Enter description here ...
	 * @param string $notify
	 * @param int $uid
	 */
	public function syn($method, $uid) {
		$operation = array_search($method, $this->notify);
		$time = Windid::getTime();
		$data = array();
		$apps = $this->_getAppDs()->getList();
		$client = Windid::client();
		$syn = false;
		//TODO
		foreach ($apps AS $val) {
			if (!$val['issyn'] && $val['id'] == $client->clientId) {
				$syn = true;
				break;
			}
			if (!$val['issyn'] || $val['id'] == $client->clientId) continue;
			$array = array(
				'windidkey'=> WindidUtility::appKey($val['id'],$time, $val['secretkey']),
				'operation'=>$operation,
				'uid'=>$uid,
				'clientid'=>$val['id'],
				'time'=>$time
			);
			$data[] =  WindidUtility::buildClientUrl($val['siteurl'], $val['apifile']).http_build_query($array);
		}
		return $syn ? array() : $data;
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