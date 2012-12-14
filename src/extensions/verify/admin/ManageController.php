<?php
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('SRV:config.srv.PwConfigSet');
Wind::import('EXT:verify.service.AppVerify_Verify');

/**
 * 本地搜索后台
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id$
 * @package wind
 */
class ManageController extends AdminBaseController {
	protected $perpage = 20;

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$restMessage = Wekit::load('SRV:mobile.srv.PwMobileService')->getRestMobileMessage();
		$this->setOutput($restMessage, 'restMessage');
		$filePath = Wind::getRealPath('APPS:admin.conf.openplatformurl.php', true);
		$openPlatformUrl = Wind::getComponent('configParser')->parse($filePath);
		$appMobileUrl = $openPlatformUrl.'index.php?m=appcenter&c=SmsManage';
		
		$this->setOutput(Wekit::C('register', 'active.phone'), 'activePhone');
		$this->setOutput($appMobileUrl, 'appMobileUrl');
		$this->_setOutConfig();
	}

	/**
	 * 保存认证设置
	 *
	 */
	public function doRunAction() {
		$verifyType = $this->getInput('verifyType', 'post');
		$verifyTypeBit = $this->_getService()->buildVerifyBit($verifyType);
		$config = new PwConfigSet('appVerify');
		$config->set('verify.isopen', $this->getInput('verifyOpen', 'post'))
			->set('verify.type', $verifyTypeBit)
			->flush();
		$this->showMessage('success');
	}

	/**
	 * 认证邮箱设置
	 *
	 */
	public function emailAction() {
		$this->_setOutConfig();
	}

	/**
	 * 保存认证邮箱设置
	 *
	 */
	public function doEmailAction() {
		$config = new PwConfigSet('appVerify');
		$config->set('verify.email.title', $this->getInput('emailTitle', 'post'))
			->set('verify.email.content', $this->getInput('emailContent', 'post'))
			->flush();
		$this->showMessage('success');
	}

	/**
	 * 认证权限设置
	 *
	 */
	public function rightsAction() {
		$this->_setOutConfig();
		$openTypes = $this->_getDs()->getOpenVerifyType();
		$rightType = $this->_getDs()->getRightType();
		$this->setOutput($rightType, 'rightType');
		$this->setOutput($openTypes, 'openTypes');
	}

	/**
	 * 保存认证权限设置
	 *
	 */
	public function doRightsAction() {
		$config = new PwConfigSet('appVerify');
		$rights = $this->getInput('rights', 'post');
		$array = array();
		foreach ($rights as $key => $value) {
			$array[$key] = $this->_getService()->buildVerifyBit($value);
		}
		
		$config->set('verify.rights', $array)
			->flush();
		$this->showMessage('success');
	}

	/**
	 * 认证会员管理
	 *
	 */
	public function usersAction() {
		list($page, $perpage, $username, $type) = $this->getInput(array('page', 'perpage', 'username', 'type'));
		$page = $page ? $page : 1;
		$perpage = $perpage ? $perpage : $this->perpage;
		list($start, $limit) = Pw::page2limit($page, $perpage);
		if ($username) {
			Wind::import('SRV:user.vo.PwUserSo');
			$vo = new PwUserSo();
			$vo->setUsername($username);
			$searchDs = Wekit::load('SRV:user.PwUserSearch');
			$userInfos = $searchDs->searchUser($vo, $perpage);
		}
		Wind::import('EXT:verify.service.vo.AppVerify_VerifySo');
		$so = new AppVerify_VerifySo();
		$userInfos && $so->setUid(array_keys($userInfos));
		if ($type) {
			$bitType = 1 << $type - 1;
			$so->setType($bitType);
		}
		$count = $this->_getDs()->countSearchVerify($so);
		if ($count) {
			$list = $this->_getDs()->searchVerify($so, $limit, $start);
			$list = $this->_buildData($list);
		}
		
		$verifyTypes = $this->_getDs()->getVerifyType();
		$this->setOutput($verifyTypes, 'verifyTypes');
		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		$this->setOutput($list, 'list');
		$this->setOutput(array('username' => $username, 'type' => $type), 'args');
		
	}

	/**
	 * 认证会员管理
	 *
	 */
	public function usersetAction() {
		$uid = $this->getInput('uid', 'get');
		$info = $this->_getDs()->getVerify($uid);
		$data = $this->_buildData(array($info['uid'] => $info));

		$this->setOutput($data[$info['uid']], 'info');
	}

	/**
	 * 取消认证
	 *
	 */
	public function deleteVerifyAction() {
		list($uid, $type) = $this->getInput(array('uid', 'type'), 'get');
		$this->_getService()->updateVerifyInfo($uid, $type, false);
		$this->showMessage('success');
	}

	/**
	 * 认证审核
	 *
	 */
	public function checkAction() {
		list($page, $perpage, $type) = $this->getInput(array('page', 'perpage', 'type'));
		$page = $page ? $page : 1;
		$perpage = $perpage ? $perpage : $this->perpage;
		list($start, $limit) = Pw::page2limit($page, $perpage);

		$count = $this->_getCheckDs()->countVerifyCheckByType($type);
		if ($count) {
			$list = $this->_getCheckDs()->getVerifyCheckByType($type, $limit, $start);
			$list = $this->_getService()->buildDetail($list);
		}
		
		$verifyTypes = $this->_getDs()->getCheckVerifyType();
		$this->setOutput($verifyTypes, 'verifyTypes');
		$this->setOutput($page, 'page');
		$this->setOutput($perpage, 'perpage');
		$this->setOutput($count, 'count');
		$this->setOutput($list, 'list');
		$this->setOutput(array('type' => $type), 'args');
	}

	/**
	 * do认证审核操作
	 *
	 */
	public function doCheckAction() {
		list($ids, $action) = $this->getInput(array('ids', 'action'));
		!is_array($ids) && $ids = array($ids);
		if ($action == 'pass') {
			$this->_getService()->checkVerify($ids);
		}
		$this->_getCheckDs()->batchDeleteVerifyCheck($ids);
		$this->showMessage('success');
	}
	
	private function _setOutConfig() {
		$conf = Wekit::C('appVerify');
		$this->setOutput($conf, 'conf');
	}

	private function _buildData($data) {
		$users = $this->_getUserDs()->fetchUserByUid(array_keys($data),PwUser::FETCH_MAIN + PwUser::FETCH_INFO);
		$list = array();
		foreach ($users as $k => $v) {
			$_tmp['uid'] = $v['uid'];
			$_tmp['username'] = $v['username'];
			Pw::getstatus($data[$k]['type'], AppVerify_Verify::VERIFY_REALNAME) && $_tmp['realname'] = $v['realname'];
			Pw::getstatus($data[$k]['type'], AppVerify_Verify::VERIFY_EMAIL) && $_tmp['email'] = $v['email'];
			Pw::getstatus($data[$k]['type'], AppVerify_Verify::VERIFY_ALIPAY) && $_tmp['alipay'] = $v['alipay'];
			Pw::getstatus($data[$k]['type'], AppVerify_Verify::VERIFY_MOBILE) && $_tmp['mobile'] = $v['mobile'];
			Pw::getstatus($data[$k]['type'], AppVerify_Verify::VERIFY_AVATAR) && $_tmp['avatar'] = 1;
			$_tmp['passVerify'] = $this->_getPassVerify($data[$k]['type']) ;
			$list[$k] = $_tmp;
		}
		return $list;
	}
	
	private function _getPassVerify($type) {
		$types = $this->_getDs()->getVerifyType();
		$array = array();
		foreach ($types as $k => $v) {
			Pw::getstatus($type, $k) && $array[] = $v;
		}
		
		return implode('、', $array);
	}
	
	/**
	 * @return PwUser
	 */
	protected function _getUserDs() {
		return Wekit::load('user.PwUser');
	}
	
	/**
	 * @return AppVerify_Verify
	 */
	protected function _getDs() {
		return Wekit::load('EXT:verify.service.AppVerify_Verify');
	}
	
	/**
	 * @return AppVerify_VerifyCheck
	 */
	protected function _getCheckDs() {
		return Wekit::load('EXT:verify.service.AppVerify_VerifyCheck');
	}
	
	/**
	 * @return AppVerify_VerifyService
	 */
	protected function _getService() {
		return Wekit::load('EXT:verify.service.srv.AppVerify_VerifyService');
	}
}

?>