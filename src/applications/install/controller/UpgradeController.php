<?php
define('WIND_SETUP', 'update');
Wind::import('WIND:ftp.WindSocketFtp');

/**
 * 87to90升级流程
 *
 * @author xiaoxia.xu<xiaoxia.xuxx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: UpgradeController.php 22845 2012-12-27 13:11:22Z xiaoxia.xuxx $
 * @package applications.install.controller
 */
class UpgradeController extends WindController {
	private $_tmpconfig = array();
	
	/* (non-PHPdoc)
	 * @see WindSimpleController::beforeAction()
	 */
	public function beforeAction($handlerAdapter) {
		$_consts = include (Wind::getRealPath('CONF:publish.php', true));
		foreach ($_consts as $const => $value) {
			if (defined($const)) continue;
			if ($const === 'PUBLIC_URL' && !$value) {
				$value = Wind::getApp()->getRequest()->getBaseUrl(true);
			}
			define($const, $value);
		}
		$url = array();
		$url['base'] = PUBLIC_URL;
		$url['res'] = WindUrlHelper::checkUrl(PUBLIC_RES, PUBLIC_URL);
		$url['css'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/css/', PUBLIC_URL);
		$url['images'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/images/', PUBLIC_URL);
		$url['js'] = WindUrlHelper::checkUrl(PUBLIC_RES . '/js/dev/', PUBLIC_URL);
		$url['attach'] = WindUrlHelper::checkUrl(PUBLIC_ATTACH, PUBLIC_URL);
		Wekit::setGlobal($url, 'url');
		$this->setOutput('phpwind 8.7 to 9.0', 'wind_version');
		
		//ajax递交编码转换
		$token = $this->getInput('token', 'get');
		$lockFile = Wind::getRealPath('DATA:setup.setup.lock', true);
		if (file_exists($lockFile) && !$token) {
			$this->showError('升级程序已被锁定, 如需重新运行，请先删除setup.lock');
		}
		$encryptToken = trim(file_get_contents($lockFile));
		if (md5($token) != $encryptToken) {
			$this->showError('升级程序访问异常! 重新安装请先删除setup.lock');
		}
	}

	/**
	 * 87升级到9更新缓存
	 */
	public function run() {
		//$db = $this->_checkDatabase();
		Wekit::createapp('phpwind');
		//更新HOOK配置数据
		Wekit::load('hook.srv.PwHookRefresh')->refresh();
		
		//初始化站点config
		$site_hash = WindUtility::generateRandStr(8);
		$cookie_pre = WindUtility::generateRandStr(3);
		Wekit::load('config.PwConfig')->setConfig('site', 'hash', $site_hash);
		Wekit::load('config.PwConfig')->setConfig('site', 'cookie.pre', $cookie_pre);
		Wekit::load('config.PwConfig')->setConfig('site', 'info.url', PUBLIC_URL);
		Wekit::load('nav.srv.PwNavService')->updateConfig();
		
		//风格默认数据
		Wekit::load('APPS:appcenter.service.srv.PwStyleInit')->init();
		
		//计划任务默认数据
		Wekit::load('cron.srv.PwCronService')->updateSysCron();
		//版块的统计数据更新
		/* @var $forumMisc PwForumMiscService */
		$forumMisc = Wekit::load('forum.srv.PwForumMiscService');
		$forumMisc->correctData();
		$forumMisc->countAllForumStatistics();
		
		//更新数据缓存
		/* @var $usergroup PwUserGroupsService */
		$usergroup = Wekit::load('SRV:usergroup.srv.PwUserGroupsService');
		$usergroup->updateLevelCache();
		$usergroup->updateGroupCache();
		$usergroup->updateGroupRightCache();
		/* @var $emotion PwEmotionService */
		$emotion = Wekit::load('SRV:emotion.srv.PwEmotionService');
		$emotion->updateCache();

		//门户演示数据
		Wekit::load('SRV:design.srv.PwDesignDefaultService')->likeModule();
		Wekit::load('SRV:design.srv.PwDesignDefaultService')->tagModule();
		Wekit::load('SRV:design.srv.PwDesignDefaultService')->reviseDefaultData();
		
		//全局缓存更新
		Wekit::load('SRV:cache.srv.PwCacheUpdateService')->updateConfig();
		Wekit::load('SRV:cache.srv.PwCacheUpdateService')->updateMedal();
		$this->_writeWindid();
		
		//清理升级过程的文件
//		WindFile::del(Wind::getRealPath('DATA:setup.setup_config.php', true));
// 		WindFile::del(Wind::getRealPath('DATA:setup.tmp_dbsql.php', true));
		header('Location: index.php');
	}

	/**
	 * 头像转移
	 */
	public function avatarAction() {
		$db_config = $this->_getConfig('db_config');
		$db = new DB($db_config['src_host'], $db_config['src_port'], $db_config['src_username'], $db_config['src_password'], $db_config['src_dbname'], $db_config['src_dbpre']);
		$end_uid = $db->get_value("SELECT MAX(uid) FROM pw_members");
		if (!$end_uid) {
			$this->showMessage('没有用户头像需要转换');
		}
		ini_set('max_execution_time', 0);
		$time_start = microtime(true);

		list($ftp, $attachDir) = $this->_getFtp();
		$defauleDir = rtrim(Wind::getRealDir('PUBLIC:res.images.face', true), '/');
		
		list($start_uid, $end) = $this->_getStartAndLimit(intval($this->getInput('uid', 'get')), $end_uid, $ftp ? true : false);
		while ($start_uid < $end) {
			$res = $this->_getOldAvatarPath($attachDir, $start_uid);
			$big = $res['big'];
			$middle = $res['middle'];
			$small = $res['small'];
			if (!$this->checkFile($middle)) {
				$big = $defauleDir . '/face_big.jpg';
				$middle = $defauleDir . '/face_middle.jpg';
				$small = $defauleDir . '/face_small.jpg';
			}
			$_toPath = '/avatar/' . Pw::getUserDir($start_uid) . '/';
			$to_big = $_toPath . $start_uid . '.jpg';
			$to_middle = $_toPath . $start_uid . '_middle.jpg';
			$to_small = $_toPath . $start_uid . '_small.jpg';
			
			if ($ftp) {
				$ftp->mkdirs($_toPath);
				$ftp->upload($big, $to_big);
				$ftp->upload($middle, $to_middle);
				$ftp->upload($small, $to_small);
			} else {
				WindFolder::mkRecur($attachDir . $_toPath);
				copy($big, $attachDir . $to_big);
				copy($middle, $attachDir . $to_middle);
				copy($small, $attachDir . $to_small);
			}
			$start_uid++;
		}
		if ($end < $end_uid) {
			$this->setOutput($end, 'uid');
			$this->setOutput($this->getInput('token', 'get'), 'token');
			$this->setTemplate('upgrade_avatar');
		} else {
			$this->showMessage('升级成功！');
		}
	}
	
	/**
	 * 获得ftp对象
	 *
	 * @return WindSocketFtp|NULL
	 */
	private function _getFtp() {
		$db_ftp = $this->_getConfig('db_ftp');
		$ftp = $attachDir = null;
		if ($db_ftp['db_ifftp']) {
			Wind::import('WIND:ftp.WindSocketFtp');
			$ftp = new WindSocketFtp(array(
				'server' => $db_ftp['ftp_server'],
				'port' => $db_ftp['ftp_port'],
				'user' => $db_ftp['ftp_user'],
				'pwd' => $db_ftp['ftp_pass'],
				'dir' => $db_ftp['ftp_dir'],
				'timeout' => $db_ftp['ftp_timeout'],
			));
			$attachDir = $db_ftp['db_ftpweb'];
		} else {
			$attachDir = Wind::getRealDir('ATTACH:', true);
		}
		$attachDir = rtrim($attachDir, '/');
		return array($ftp, $attachDir);
	}
	
	/**
	 * 获得开始结束的ID
	 *
	 * @param int $start
	 * @param int $max
	 * @param boolean $isFtp
	 * @return array
	 */
	private function _getStartAndLimit($start_uid, $end_uid, $isFtp = true) {
		$limit = 10;
		if (!isFtp) {
			$_lt = $this->_getConfig('limit');
			$_lt = is_numeric($_lt) ? abs($_lt) : 1;
			$_lt == 0 && $_lt = 1;
			$limit = 1000 * $_lt;
		}
		
		if ($start_uid < 1) $start_uid = 1;
		if ($start_uid >= $end_uid) {
			$this->showMessage('头像升级成功');
		}
		$end = ($start_uid + $limit) > $end_uid ? $end_uid : ($start_uid + $limit);
		return array($start_uid, $end);
	}
	
	/**
	 * 检查文件
	 *
	 * @param string $filename
	 * @return boolean
	 */
	private function checkFile($filename) {
		if (!@fopen($filename, 'r')) return false;
		return true;
		// $res = get_headers($file);
		// $r = trim($res['0']);
		// if(strpos($r,'OK')) return true;
		// return false;
	}

	/**
	 * 获取ftp配置文件
	 *
	 * @return array
	 */
	private function _getConfig($name = '') {
		if ($this->_tmpconfig) return isset($this->_tmpconfig[$name]) ? $this->_tmpconfig[$name] : $this->_tmpconfig;
		$file = Wind::getRealPath('DATA:setup.setup_config.php', true);
		if (!is_file($file)) {
			$this->showError('配置文件不存在');
		}
		require $file;
		$this->_tmpconfig = $setupConfig;
		return isset($this->_tmpconfig[$name]) ? $this->_tmpconfig[$name] : $this->_tmpconfig;
	}

	/**
	 * 获得用户87中的头像
	 *
	 * @param string $attachDir
	 * @param int $tempuid
	 * @return string
	 */
	private function _getOldAvatarPath($attachDir, $tempuid) {
		$udir = str_pad(substr($tempuid, -2), 2, '0', STR_PAD_LEFT);
		$user_a = $udir . '/' . $tempuid . '.jpg';
		$faceurl = array();
		$faceurl['middle'] = $attachDir . "/upload/middle/$user_a";
		$faceurl['big'] = $attachDir . "/upload/middle/$user_a";
		$faceurl['small'] = $attachDir . "/upload/small/$user_a";
		return $faceurl;
	}

	/**
	 * windid更新
	 * 
	 * @return boolean
	 */
	private function _writeWindid() {
		$baseUrl = Wind::getApp()->getRequest()->getBaseUrl(true);
		$key = md5(WindUtility::generateRandStr(10));
		$charset = Wind::getApp()->getResponse()->getCharset();
		$charset = str_replace('-', '', strtolower($charset));
		if (!in_array($charset, array('gbk', 'utf8', 'big5'))) $charset = 'utf8';
		
		Wind::import('WINDID:service.app.dm.WindidAppDm');
		$dm = new WindidAppDm();
		$dm->setApiFile('windid.php')
			->setIsNotify('1')
			->setIsSyn('1')
			->setAppName('phpwind9.0')
			->setSecretkey($key)
			->setAppUrl($baseUrl)
			->setCharset($charset)
			->setAppIp('');
		$result = Windid::load('app.WindidApp')->addApp($dm);
		if ($result instanceof WindidError) $this->showError('INSTALL:windid.init.fail');
		$config = array(
			'windid'  => 'local',
			'serverUrl' => $baseUrl,
			'clientId'  => (int)$result,
			'clientKey'  => $key,
			'clientDb'  => 'mysql',
			'clientCharser'  => $charset,
		);
		WindFile::savePhpData($this->_getWindidFile(),$config);
		return true;
	}
	
	/* (non-PHPdoc)
	 * @see WindSimpleController::setDefaultTemplateName()
	 */
	protected function setDefaultTemplateName($handlerAdapter) {
		$template = $handlerAdapter->getController() . '_' . $handlerAdapter->getAction();
		$this->setTemplate(strtolower($template));
	}

	/**
	 * 显示信息
	 *
	 * @param string $message 消息信息
	 * @param string $referer 跳转地址
	 * @param boolean $referer 是否刷新页面
	 * @param string $action 处理句柄
	 * @see WindSimpleController::showMessage()
	 */
	protected function showMessage($message = '', $lang = true, $referer = '', $refresh = false) {
		$this->addMessage('success', 'state');
		$this->addMessage($this->forward->getVars('data'), 'data');
		$this->showError($message, $lang, $referer, $refresh);
	}

	/**
	 * 显示错误
	 *
	 * @param array $error array('',array())
	 */
	protected function showError($error = '', $lang = true, $referer = '', $refresh = false) {
// 		$referer && $referer = WindUrlHelper::createUrl($referer);
		$this->addMessage('up87to90.php?step=init&action=end&seprator=1&token=' . $this->getInput('token', 'get'), 'referer');
		$this->addMessage($refresh, 'refresh');
		if ($lang) {
			$lang = Wind::getComponent('i18n');
			$error = $lang->getMessage($error);
		}
		parent::showMessage($error);
	}
}
class DB {
	var $dbpre;
	var $link;

	function __construct($host, $port, $user, $password, $db, $pre = 'pw_') {
		$this->link = mysql_connect("$host:$port", $user, $password, true);
		$pre && $this->dbpre = $pre;
		if ($this->link) {
			mysql_select_db($db, $this->link);
		} else {
			showError("Access denied for user '{$user}'@'{$host}' (using password: YES)");
		}
	}

	function query($sql) {
		$originalSQL = $sql;
		if ($this->dbpre != 'pw_') {
			$sql = str_replace(array(' pw_', '`pw_', " 'pw_"), 
				array(" $this->dbpre", "`$this->dbpre", " '$this->dbpre"), $sql);
		}
		return mysql_query($sql, $this->link);
	}

	function affected_rows() {
		return mysql_affected_rows($this->link);
	}

	function fetch_array($query, $result_type = MYSQL_ASSOC) {
		return mysql_fetch_array($query, $result_type);
	}

	function get_one($sql, $result_type = MYSQL_ASSOC) {
		$query = $this->query($sql);
		return mysql_fetch_array($query, $result_type);
	}

	function insert_id() {
		return $this->get_value('SELECT LAST_INSERT_ID()');
	}

	function get_value($sql, $result_type = MYSQL_NUM, $field = 0) {
		$query = $this->query($sql);
		$rt = mysql_fetch_array($query, $result_type);
		return isset($rt[$field]) ? $rt[$field] : false;
	}

	function get_all($sql, $result_type = MYSQL_ASSOC, $index = '') {
		$query = $this->query($sql);
		$data = array();
		while ($row = mysql_fetch_array($query, $result_type)) {
			if (isset($row[$index])) {
				$data[$row[$index]] = $row;
			} else {
				$data[] = $row;
			}
		}
		return $data;
	}

	function escape_string($str) {
		return addslashes($str);
	}
}