<?php
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('SRV:user.dm.PwUserInfoDm');
/**
 * 后台创始人管理相关操作类
 * 
 * 创始人管理相关操作<code>
 * 1. run 创始人管理首页
 * 2. add 添加创始人
 * 3. del 删除创始人
 * </code>
 * @author Qiong Wu <papa0924@gmail.com> 2011-11-10
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: FounderController.php 21837 2012-12-13 11:22:27Z long.shi $
 * @package admin
 * @subpackage library
 */
class FounderController extends AdminBaseController {
	
	private $_founders;

	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		$this->_getFounder();
	}

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$this->setOutput(array_keys($this->_founders), 'list');
	}

	/**
	 * 添加创始人
	 * 
	 * @return void
	 */
	public function addAction() {
		$username = $this->_getUsername();
		$user = $this->_getUserDs()->getUserByName($username);
		
		$exist = $user ? 1 : 0;
		$args = array('exist' => $exist, 'username' => $username);
		if ($exist) {
			$args['email'] = $user['email'];
			$args['uid'] = $user['uid'];
		}
		$this->showMessage('success', 'founder/show?' . http_build_query($args));
	}
	
	/**
	 * 展示创始人添加页
	 *
	 */
	public function showAction() {
		list($exist, $email, $uid, $username) = $this->getInput(array('exist', 'email', 'uid', 'username'));
		$this->setOutput(array(
			'exist' => $exist,
			'email' => urldecode($email),
			'uid' => $uid,
			'username' => urldecode($username)
			));
		$this->setTemplate('founder_add');
	}

	/**
	 * 添加创始人
	 *
	 */
	public function doAddAction() {
		$username = $this->_getUsername();
		list($password, $email) = $this->getInput(array('password', 'email'), 'post');
		if (!$password || !$email) $this->showError('ADMIN:founder.add.fail.password.empty');
		
		$exist = $this->getInput('exist');
		
		$userDm = new PwUserInfoDm($exist ? $this->getInput('uid') : 0);
		$userDm->setUsername($username)->setPassword($password)->setEmail($email)->setGroupid(3);
		
		//新增创始人，用户名已存在，则修改原有密码。用户名不存在，则添加一个用户
		if ($exist) {
			$r = $this->_getUserDs()->editUser($userDm);
		} else {
			$r = $this->_getUserDs()->addUser($userDm);
		}
		if ($r instanceof PwError) $this->showError($r->getError());
		
		$this->_founders[$username] = $this->_buildPwd($password);
		$this->_updateConfig();
		$this->showMessage('success', 'founder/run');
	}

	/**
	 * 编辑创始人
	 *
	 */
	public function editAction() {
		$username = $this->getInput('username', 'get');
		if (!isset($this->_founders[$username])) $this->showError('ADMIN:founder.edit.fail');
		$user = $this->_getUserDs()->getUserByName($username);
		
		$exist = $user ? 1 : 0;
		if ($exist) {
			$this->setOutput($user['email'], 'email');
			$this->setOutput($user['uid'], 'uid');
		}
		$this->setOutput($exist, 'exist');
		$this->setOutput($username, 'username');
	}

	/**
	 * 修改创始人
	 *
	 */
	public function doEditAction() {
		$username = $this->getInput('username', 'post');
		if (!isset($this->_founders[$username])) $this->showError('ADMIN:founder.edit.fail');
		list($password, $email) = $this->getInput(array('password', 'email'), 'post');
		if (!$password || !$email) $this->showError('ADMIN:founder.edit.fail.password.empty');
		$exist = $this->getInput('exist');
		
		//如果用户不存在，则插入用户
		$userDm = new PwUserInfoDm($exist ? $this->getInput('uid') : 0);
		$userDm->setUsername($username)->setPassword($password)->setEmail($email)->setGroupid(3);
		if (!$exist) {
			$r = $this->_getUserDs()->addUser($userDm);
		} else {
			$r = $this->_getUserDs()->editUser($userDm);
		}
		if ($r instanceof PwError) $this->showError($r->getError());
		
		$this->_founders[$username] = $this->_buildPwd($password);
		$this->_updateConfig();
		$this->showMessage('success', 'founder/run');
	}

	/**
	 * 删除创始人
	 */
	public function delAction() {
		$username = $this->getInput('username', 'get');
		if (!isset($this->_founders[$username])) $this->showError('ADMIN:founder.del.fail');
		if ($this->adminUser->username == $username) $this->showError('ADMIN:founder.del.fail.self');
		unset($this->_founders[$username]);
		if (empty($this->_founders)) $this->showError('founder.del.fail.all');
		$this->_updateConfig();
		$this->showMessage('success');
	}

	/**
	 * 读取创始人配置文件
	 *
	 * @return PwError|array
	 */
	private function _getFounder() {
		$this->_founders = Wekit::load('ADMIN:service.srv.AdminUserService')->getFounders();
		$this->setOutput(is_writeable(CONF_PATH . 'founder.php'), 'is_writeable');
		return $this->_founders;
	}

	/**
	 * 将创始人写入配置文件
	 *
	 * @return Ambigous <number, boolean>
	 */
	private function _updateConfig() {
		$file = Wind::getRealPath('CONF:founder.php', true);
		$r = WindFile::write($file, 
			"<?php\r\nreturn\n" . var_export($this->_founders, true) . ";\r\n?>");
		if (!$r) $this->showError('ADMIN:founder.file.write.fail');
	}

	/**
	 * 获取用户名
	 *
	 * @return string
	 */
	private function _getUsername() {
		$username = $this->getInput('username', 'post');
		if (empty($username)) $this->showError('ADMIN:founder.add.fail');
		if (isset($this->_founders[$username])) $this->showError(
			'ADMIN:founder.add.fail.username.duplicate');
		return $username;
	}

	/**
	 * @return PwUser
	 */
	private function _getUserDs() {
		return Wekit::load('user.PwUser');
	}

	/**
	 * 设置密码
	 *
	 * @param string  $password
	 * @return string
	 */
	private function _buildPwd($password) {
		$salt = WindUtility::generateRandStr(6);
		return md5($password . $salt) . '|' . $salt;
	}
}
?>