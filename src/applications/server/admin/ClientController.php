<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('APPS:server.admin.WindidBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ClientController.php 21596 2012-12-11 09:29:57Z gao.wanggao $ 
 * @package 
 */
class ClientController extends WindidBaseController { 
	
	public function run() {
		$list = $this->_getAppDs()->getList();
		$data = $urls = array();
		$time = Pw::getTime();
		foreach ($list AS $k=>$v) {
			$urls[$k] = $v['siteurl'].$v['apifile'];
			$windidkey = WindidUtility::appKey($v['id'], $time, $v['secretkey']);
			$urls[$k] = $v['siteurl'].$v['apifile'] . '?windidkey='.$windidkey.'&clentid=' . $v['id'].'&time='.$time;
			$data[$k]['operation'] = '999';
			$data[$k]['params'] =  serialize(array('test'=>$time));
		}
		/*$result = WindidUtility::buildMultiRequest($urls, $data);
		foreach ($list AS $k=>$v) {
			$list[$k]['iscomm'] = ($result[$k] === 'seccess') ? true : false;
		}*/
		$this->setOutput($list, 'list');
	}
	
	public function clientTestAction() {
		$clientid = $this->getInput('clientid');
		$client = $this->_getAppDs()->getApp($clientid);
		if (!$client) $this->showError('WINDID:fail');
		$time = Pw::getTime();
		$params['params'] =  serialize(array('test'=>$time));
		$windidkey = WindidUtility::appKey($client['id'], $time, $client['secretkey']);
		$url = WindidUtility::buildClientUrl($client['siteurl'], $client['apifile']) . 'operation=999&windidkey='.$windidkey.'&clentid=' . $client['id'].'&time='.$time;
		$result = WindidUtility::buildRequest($url, $params);
		if ($result === 'seccess')$this->showMessage('ADMIN:success');
		$this->showError('ADMIN:fail');
	}
	
	public function addAction() {
		$rand = WindUtility::generateRandStr(10);
		$this->setOutput(md5($rand), 'rand');
		$this->setOutput('windid.php' , 'apifile');
	}
	
	public function doaddAction() {
		$apifile = $this->getInput('apifile', 'post');
		if (!$apifile) $apifile = 'windid.php';
		Wind::import('WINDID:service.app.dm.WindidAppDm');
		$dm = new WindidAppDm();
		$dm->setApiFile($apifile)
			->setIsNotify($this->getInput('isnotify', 'post'))
			->setIsSyn($this->getInput('issyn', 'post'))
			->setAppName($this->getInput('appname', 'post'))
			->setSecretkey($this->getInput('appkey', 'post'))
			->setAppUrl($this->getInput('appurl', 'post'))
			->setAppIp($this->getInput('appip', 'post'));
		$result = $this->_getAppDs()->addApp($dm);
		if ($result instanceof WindidError) $this->showError('ADMIN:fail');
		$this->showMessage('ADMIN:success');
	}
	
	public function editAction() {
		$app = $this->_getAppDs()->getApp(intval($this->getInput('id', 'get')));
		if (!$app) $this->showMessage('ADMIN:fail');
		$this->setOutput($app, 'app');
	}
	
	public function doeditAction() {
		Wind::import('WINDID:service.app.dm.WindidAppDm');
		$dm = new WindidAppDm(intval($this->getInput('id', 'post')));
		$dm->setApiFile($this->getInput('apifile', 'post'))
			->setIsNotify($this->getInput('isnotify', 'post'))
			->setIsSyn($this->getInput('issyn', 'post'))
			->setAppName($this->getInput('appname', 'post'))
			->setSecretkey($this->getInput('appkey', 'post'))
			->setAppUrl($this->getInput('appurl', 'post'))
			->setAppIp($this->getInput('appip', 'post'));
		$result = $this->_getAppDs()->editApp($dm);
		if ($result instanceof WindidError) $this->showError('ADMIN:fail');
		$this->showMessage('ADMIN:success');
	}
	
	public function deleteAction() {
		$result = $this->_getAppDs()->delApp(intval($this->getInput('id', 'get')));
		if ($result instanceof WindidError) $this->showError('ADMIN:fail');
		$this->showMessage('ADMIN:success');
	}
	
	private function _getAppDs() {
		return Windid::load('app.WindidApp');
	}
}

?>