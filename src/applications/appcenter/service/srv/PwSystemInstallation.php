<?php
Wind::import('APPS:appcenter.service.srv.PwInstallApplication');
Wind::import('APPS:appcenter.service.srv.helper.PwFtpSave');
Wind::import('APPS:appcenter.service.srv.helper.PwSftpSave');
/**
 * 系统升级服务
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwSystemInstallation.php 22736 2012-12-26 13:58:39Z long.shi $
 * @package wind
 */
class PwSystemInstallation extends PwInstallApplication {
	protected $local;
	public $target;
	protected $tmpPath = 'DATA:upgrade';
	public $useZip = 1;
	
	public function __construct() {
		$this->local = 'phpwind_' . str_replace('.', '', NEXT_VERSION) . '_' . NEXT_RELEASE;
		$this->tmpPath = Wind::getRealDir($this->tmpPath, true);
		WindFolder::mkRecur($this->tmpPackage);
	}

	/**
	 * 根据当前版本+release获取升级信息(patch包地址) ,hash值
	 * 获取升级文件列表
	 */
	public function checkUpgrade() {
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'getUpdateInfo', 'pwversion' => $this->local));

		$r = PwApplicationHelper::requestAcloudData($url);
		return is_array($r) ? ($r['code'] === '0' ? $r['info'] : $r['msg']) : '无法连接云平台!';
	}
	
	public function getNotice($adminUser) {
		$notice = 0;
		if (Wekit::load('ADMIN:service.srv.AdminFounderService')->isFounder($adminUser->username)) {
			$ck = Pw::getCookie('checkupgrade');
			if (!$ck) {
				$upgradeInfo = $this->checkUpgrade();
				$upgradeInfo && $notice |= 1;
				Pw::setCookie('checkupgrade', $upgradeInfo ? 1 : -1, 7200);
			} else {
				$ck === '1' && $notice |= 1;
			}
			$ck = Pw::getCookie('checkpatch');
			if (!$ck) {
				$patchInfo = Wekit::load('APPS:appcenter.service.srv.PwPatchUpdate')->checkUpgrade();
				$patchInfo && $notice |= 2;
			} else {
				$ck === '1' && $notice |= 2;
			}
		}
		$url = '';
		switch ($notice) {
			case 1 :
				$notice = '您正在使用旧版本的phpwind，为了获得更好的体验，请升级至最新版。';
				$url = WindUrlHelper::createUrl('appcenter/upgrade/run');
				break;
			case 2 :
				$notice = '您正在使用的版本有更新补丁，请安装补丁。';
				$url = WindUrlHelper::createUrl('appcenter/fixup/run');
				break;
			case 3 : 
				$notice = '您正在使用的版本有新增升级包和更新补丁。';
				$url = WindUrlHelper::createUrl('appcenter/upgrade/run');
				break;
			default :
				$notice = '';
		}
		return array('notice' => $notice, 'url' => $url);
	}
	
	/**
	 * 检查环境
	 *
	 * @return PwError|boolean
	 */
	public function checkEnvironment() {
		if (!function_exists('curl_init')) return new PwError('APPCENTER:upgrade.curl');
		//if (!function_exists('gzinflate')) return new PwError('APPCENTER:upgrade.gzinflate');
		return true;
	}

	/**
	 * 下载
	 * 1、下载zip包
	 * 2、单个文件下载
	 */
	public function download($downloadUrl, $hash, $file = '') {
		if ($this->useZip) {
			list($bool, $package) = PwSystemHelper::download($downloadUrl, $this->tmpPath . '/' . basename($downloadUrl));
			if (!$bool) return new PwError($package);
			if ($hash !== md5_file($package)) {
				$this->_log('check Package fail. expected hash:' . $hash . 'real hash :' . md5_file($package));
				return new PwError('APPCENTER:install.checkpackage.fail');
			}
			$this->_log('download zip success, dir:' . $package);
			$this->tmpPackage = $this->tmpPath . DIRECTORY_SEPARATOR . $this->target;
			$r = PwApplicationHelper::extract($package, $this->tmpPath);
			if ($r === false) {
				$this->_log('extract Package fail. ');
				return new PwError(
				'APPCENTER:install.checkpackage.format.fail', array('{{error}}' => $r));
			}
			if ($r != realpath($this->tmpPackage)) {
				PwApplicationHelper::copyRecursive($r, $this->tmpPackage);
			}
			$this->_log('extract zip success, dir:' . $this->tmpPackage);
			return true;
		} else {
			$_file = $this->tmpPath . DIRECTORY_SEPARATOR . $this->target . DIRECTORY_SEPARATOR . $file;
			$dir = dirname($_file);
			list($bool, $_file) = PwSystemHelper::download($downloadUrl . '/' . $file . 'wind', $_file);
			if (!$bool) return new PwError($_file);
			if ($hash !== md5_file($_file)) {
				$this->_log('check file fail. expected hash:' . $hash . 'real hash :' . md5_file($_file));
				return new PwError('APPCENTER:upgrade.check.file.fail');
			}
			$this->_log('download file success, file:' . $_file);
			return true;
		}
	}
	
	/**
	 * 根据现有目录结构调整升级包目录
	 *
	 * @param array $fileList
	 */
	public function sortDirectory($fileList) {
		$this->_log('start to sort the directory');
		$sourceDir = $this->tmpPath . DIRECTORY_SEPARATOR . $this->target;
		$directoryFile = $sourceDir . DIRECTORY_SEPARATOR . 'conf/directory.php';
		$directory = @include $directoryFile;
		if (!is_array($directory)) {
			return new PwError('APPCENTER:upgrade.directory.fail');
		}
		$this->_log('the remote directory is:' . var_export($directory, true));
		$root = Wind::getRealDir('ROOT:');
		
		$sort = array('HTML', 'ATTACH', 'TPL', 'THEMES', 'ACLOUD', 'WINDID', 'REP', 'SRV', 'LIB', 'HOOK', 'EXT', 'APPS', 'CONF', 'DATA', 'SRC', 'PUBLIC');
		$strtr = array();
		$localDirectory = @include Wind::getRealPath('CONF:directory.php', true);
		foreach ($sort as $v) {
			if ($directory[$v] == $localDirectory[$v]) continue;
			$search = PwSystemHelper::relative(WEKIT_PATH . $directory[$v]);
			$strtr[$search] = Wind::getRootPath($v);
		}
		$this->_log('way of moving directory' . var_export($strtr, true));
		$moveList = $newFileList = array();
		
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'getVersionHash', 'pwversion' => $this->local));
		/* 从线上获取当前版本的所有文件md5 */
		/* $result = PwApplicationHelper::requestAcloudData($url);
		if ($result['code'] !== '0' || !$result['info']) return new PwError(array('APPCENTER:upgrade.version.hash.fail', array($result['msg']))); */
		if (!$tmp = WindFile::read(CONF_PATH . 'md5sum')) {
			return new PwError('APPCENTER:upgrade.hash.fail');
		}
		$md5List = array();
		foreach (explode("\n", $tmp) as $v) {
			list($_k, $_v) = explode("\t", $v);
			if ($_k && $_v) {
				$md5List[$_v] = $_k;
			}
		}
		$this->_log('obtain the md5 list of current version');
		
		foreach ($fileList as $v) {
			$v = trim($v, '/');
			$_v = $root . $v;
			$file = $v;
			foreach ($strtr as $search => $replace) {
				if (0 === strpos($_v, $search)) {
					$file = str_replace($root, '', $replace . substr($_v, strlen($search)));
					$moveList[$v] = $file;
					break;
				}
			}
			$newFileList[$file] = $md5List[$file];
		}
		$this->_log('files need to move ' . var_export($moveList, true));
		foreach ($moveList as $old => $new) {
			WindFolder::mkRecur(dirname($sourceDir . DIRECTORY_SEPARATOR . $new));
			copy($sourceDir . DIRECTORY_SEPARATOR . $old, $sourceDir . DIRECTORY_SEPARATOR . $new);
			WindFile::del($sourceDir . DIRECTORY_SEPARATOR . $old);
			if ($old == 'www/update.php') {
				$content = WindFile::read($sourceDir . DIRECTORY_SEPARATOR . $new);
				$content = str_replace('../src/wekit.php', Wind::getRealPath('SRC:wekit'), $content);
				WindFile::write($sourceDir . DIRECTORY_SEPARATOR . $new, $content);
			}
		}
		if ($directory != $localDirectory) {
			$directory = array_merge($directory, $localDirectory);
			$temp = "<?php\r\n defined('WEKIT_VERSION') or exit(403);\r\n return ";
			$temp .= WindString::varToString($directory) . ";\r\n?>";
			WindFile::write($directoryFile, $temp);
		}
		return $newFileList;
	}
	
	/**
	 * 验证本地文件
	 *
	 * @param array $fileList
	 * @return array 
	 */
	public function validateLocalFiles($fileList) {
		$this->_log('start to validate local files' . var_export($fileList, true));
		list($change, $unchange, $new) = PwSystemHelper::validateMd5($fileList);
		$source = DATA_PATH . 'upgrade' . DIRECTORY_SEPARATOR . $this->target . DIRECTORY_SEPARATOR;
		foreach ($change as $k => $v) {
			if (md5_file($source . $v) == md5_file(ROOT_PATH . $v)) {
				unset($change[$k]);
				$unchange[] = $v;
			}
		}
		return array($change, $unchange, $new);
	}

	/**
	 * 进行升级
	 */
	public function doUpgrade($fileList, $useFtp = 0) {
		$source = Wind::getRealDir('DATA:upgrade') . DIRECTORY_SEPARATOR . $this->target;
		if ($useFtp) {
			try {
				$ftp = $useFtp['sftp'] ? new PwSftpSave($useFtp) : new PwFtpSave($useFtp);
			} catch (WindFtpException $e) {
				return new PwError(array('APPCENTER:upgrade.ftp.fail', array($e->getMessage())));
			}
		}
		
		foreach ($fileList as $v => $hash) {
			if (!is_file($source . DIRECTORY_SEPARATOR . $v)) {
				return new PwError('APPCENTER:upgrade.file.lost');
			}
			if ($useFtp) {
				try {
					$r = $ftp->upload($source . DIRECTORY_SEPARATOR . $v, $v);
					if ($useFtp['sftp'] && !$r && $e = $ftp->getError()) {
						return new PwError('APPCENTER:upgrade.upload.fail', array($v . var_export($e, true)));
					}
				} catch (WindFtpException $e) {
					return new PwError('APPCENTER:upgrade.upload.fail', array($v));
				}
			} else {
				WindFolder::mkRecur(dirname(ROOT_PATH . $v));
				$r = @copy($source . DIRECTORY_SEPARATOR . $v, ROOT_PATH . $v);
				if (!$r) return new PwError('APPCENTER:upgrade.copy.fail', array($v));
			}
			$this->_log('copy file ' . $v);
		}
		if ($useFtp) $ftp->close();
		return true;
	}

	/**
	 * 备份
	 */
	public function backUp($fileList) {
		$root = Wind::getRealDir('ROOT:', true);
		$dest = Wind::getRealDir('DATA:backup', true) . DIRECTORY_SEPARATOR . $this->local;
		foreach ($fileList as $v => $hash) {
			if (is_file($root . DIRECTORY_SEPARATOR . $v)) {
				WindFolder::mkRecur(dirname($dest . DIRECTORY_SEPARATOR . $v));
				$r = @copy($root . DIRECTORY_SEPARATOR . $v, $dest . DIRECTORY_SEPARATOR . $v);
				$this->_log('back up file ' . $v);
				if (!$r) return new PwError('APPCENTER:upgrade.backup.fail');
			}
		}
		return true;
	}
	
	public function flushLog() {
		if ($this->target) {
			$logFile = Wind::getRealDir('DATA:upgrade.log', true) . '/' . $this->target . '.log';
			if (file_exists($logFile) && $log = WindFile::read($logFile)) {
				Wekit::load('patch.PwUpgradeLog')->addLog($this->target, $log);
			}
		}
	}
	
	private function _log($msg) {
		PwSystemHelper::log($msg, $this->target);
	}
}

?>