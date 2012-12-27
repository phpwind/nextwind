<?php
define('WINDID_IS_NOTIFY', 1);

Wind::import('APPS:inform.service.PwWindidInform');
Wind::import('LIB:utility.PwWindidStd');
Wind::import('WINDID:service.client.bo.WindidClientBo');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: IndexController.php 22632 2012-12-26 05:27:26Z gao.wanggao $ 
 * @package 
 */
class IndexController extends PwBaseController {
	
	public $notify = array(
		'999'=>'test',   		//通讯测试接口
		'101'=>'addUser',		//注册用户
		'111'=>'synLogin',		//同步登录
		'112'=>'synLogout',		//同步登出
		'201'=>'editUser',		//编辑用户基本信息(用户名，密码，邮箱，安全问题)
		'202'=>'editUserInfo',  //编辑用户详细资料
		'203'=>'uploadAvatar',  //上传头像
		'211'=>'editCredit', 	//编辑用户积分
		'222'=>'editMessageNum', //同步用户未读私信
		'301'=>'deleteUser',	//删除用户
	);
	
	public  function beforeAction($handlerAdapter) {	
		parent::beforeAction($handlerAdapter);
		$_windidkey = $this->getInput('windidkey', 'get');
		$_time = (int)$this->getInput('time', 'get');
		$_clentid = (int)$this->getInput('clientid', 'get');
		WindidClientBo::getInstance();
		$client = Windid::client();
		if (WindidUtility::appKey($client->clientId, $_time, $client->clientKey) != $_windidkey)  $this->showError('fail');
		$time = Windid::getTime();
		if ($time - $_time > 120) $this->showError('timeout');
	}
	
	public function run() {
		$operation = (int)$this->getInput('operation', 'get');
		$uid = (int)$this->getInput('uid', 'get');
		if (!$uid) $this->showError('fail');
		if (!isset($this->notify[$operation])) $this->showError('fail');
		$method = $this->notify[$operation];
		$srv = new PwWindidInform();
		if(!method_exists($srv, $method)) $this->showError('fail');
		$result = $srv->$method($uid);
		if ($result == true) $this->showMessage('seccess');
		$this->showError('fail');
	}
	
	protected function showError($message = '', $referer = '', $refresh = false) {
		echo $message;
		exit();
	}

	protected function showMessage($message = '', $referer = '', $refresh = false) {
		echo $message;
		exit();
	}
	
	private function _getNotifyService() {
		return Windid::load('notify.srv.WindidNotifyServer');
	}
}

?>