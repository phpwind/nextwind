<?php
Wind::import('APPS:windid.admin.WindidBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: NotifyController.php 23371 2013-01-09 06:18:14Z gao.wanggao $ 
 * @package 
 */
class NotifyController extends WindidBaseController {
	public $notify = array(
		'101'=>'用户  %s  注册',
	
		'201'=>'修改  %s  基本信息',
		'202'=>'修改  %s  详细资料',
		'203'=>'上传  %s  头像',
		
		'211'=>'修改  %s  积分',
		'222'=>'修改  %s  未读消息',
	
		'301'=>'删除  %s',

	);
	
	public function run() {
		$perPage = 10;
		$uids = $appids = $nids = array();
		list($clientid, $complete, $page) = $this->getInput(array('clientid', 'complete', 'page'));
		$page =  $page > 1 ? $page : 1;
		$complete = ($complete === '') ? null : $complete;
		list($start, $limit) = Pw::page2limit($page, $perPage);
		$list = $this->_getLogDs()->getList($clientid, 0, $limit,  $start, $complete);
		$count =  $this->_getLogDs()->countList($clientid, 0, $complete);
		foreach ($list AS $k=>$v) {
			$appids[] = $v['appid'];
			$nids[] = $v['nid'];
		}
		$apps = $this->_getAppDs()->getList();
		$notifys = $this->_getNotifyDs()->fetchNotify(array_unique($nids));
		foreach ($notifys AS $v) {
			$uids[] = $v['param'];
		}
		$users = $this->_getUserDs()->fetchUserByUid($uids);
		foreach ($list AS $k=>$v) {
			$list[$k]['client'] = $apps[$v['appid']]['name'];
			$list[$k]['fromclient'] = $notifys[$v['nid']]['appid'] == 0 ? 'server' : $apps[$notifys[$v['nid']]['appid']]['name'];
			$operation = $notifys[$v['nid']]['operation'];
			$uid = $notifys[$v['nid']]['param'];
			$username = $users[$uid]['username'];
			$operation = isset($this->notify[$operation]) ? $this->notify[$operation] : '';
			$operation = sprintf($operation, $username);
			$list[$k]['operation'] = $operation;
			$list[$k]['time'] = $notifys[$v['nid']]['timestamp'];
		}
		$totalPage = ceil($count/$perPage);
		$page > $totalPage && $page = $totalPage;
		$clientid && $args['clientid'] = $clientid;
		isset($complete) && $args['complete'] = $complete;
		$this->setOutput($args, 'args');
		$this->setOutput($page, 'page');
		$this->setOutput($perPage, 'perPage');
		$this->setOutput($count, 'count');
		$this->setOutput($list, 'list');
		$this->setOutput($apps, 'apps');
	}
	
	
	public function clearAction() {
		$perPage = 100;
		$count =  $this->_getLogDs()->countList(0, 0, 0);
		$totalPage = ceil($count/$perPage);
		$logDs = $this->_getLogDs();
		$nDs = $this->_getNotifyDs();
		$nids = array();
		for ($page = 1; $page <= $totalPage; $page++  ) {
			list($start, $limit) = Pw::page2limit($page, $perPage);
			$list = $logDs->getList(0, 0, $limit,  $start, 0);
			foreach ($list AS $k=>$v) {
				$nids[] = $v['nid'];
			}
		}
		$this->_getLogDs()->deleteComplete();
		$this->_getNotifyDs()->batchNotDelete($nids);
		$this->showMessage('WINDID:success');
	}
	
	public function resendAction() {
		$logid = (int)$this->getInput('logid', 'get');
		if ($this->_getNotifyService()->logSend($logid)) $this->showMessage('ADMIN:success');
		$this->showError('ADMIN:fail');
	}
	
	private function _getAppDs() {
		return Windid::load('app.WindidApp');
	}
	
	private function _getUserDs() {
		return Windid::load('user.WindidUser');
	}
	
	private function _getLogDs() {
		return Windid::load('notify.WindidNotifyLog');
	}
	
	private function _getNotifyDs() {
		return Windid::load('notify.WindidNotify');
	}
	
	private function _getNotifyService() {
		return Windid::load('notify.srv.WindidNotifyServer');
	}
}
?>