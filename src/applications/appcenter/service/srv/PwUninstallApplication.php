<?php
Wind::import('APPS:appcenter.service.srv.helper.PwApplicationHelper');
Wind::import('APPS:appcenter.service.srv.helper.PwManifest');
/**
 * 卸载应用
 *
 * @author Qiong Wu <papa0924@gmail.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwUninstallApplication.php 18098 2012-09-11 07:12:59Z long.shi $
 * @package products
 * @subpackage appcenter.service.srv
 */
class PwUninstallApplication {
	protected $_appId = '';
	private $_log = array();

	/**
	 * @param string $appId
	 */
	public function uninstall($appId) {
		$this->_appId = $appId;
		$log = $this->_loadInstallLog()->findByAppId($this->_appId);
		foreach ($log as $value) {
			$this->_log[$value['log_type']] = $value['data'];
		}
		$service = $this->getInstallLog('service');
		foreach ($service as $key => $var) {
			if (!isset($var['class'])) continue;
			$_install = Wekit::load($var['class']);
			if (!$_install instanceof iPwInstall) return new PwError('APPCENTER:install.classtype');
			$r = $_install->unInstall($this);
			if ($r instanceof PwError) return $r;
		}
		$this->_loadInstallLog()->delByAppId($this->_appId);
		return true;
	}

	/**
	 * $key 值:
	 * service 安装服务
	 * appId 应用ID
	 * hook	 已安装的hook
	 * inject 已注册的inject
	 * table 已安装的数据表
	 * 
	 * @param string $key
	 */
	public function getInstallLog($key) {
		return isset($this->_log[$key]) ? $this->_log[$key] : array();
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return $this->_hash;
	}

	/**
	 * @return string
	 */
	public function getAppId() {
		return $this->_appId;
	}

	/**
	 * @return PwApplicationLog
	 */
	private function _loadInstallLog() {
		return Wekit::load('APPS:appcenter.service.PwApplicationLog');
	}
}

?>