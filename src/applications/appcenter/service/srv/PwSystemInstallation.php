<?php
Wind::import('APPS:appcenter.service.srv.PwInstallApplication');
/**
 * 系统升级服务
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwSystemInstallation.php 21843 2012-12-14 02:50:41Z long.shi $
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
		return $r['code'] === '0' ? $r['info'] : false;
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
			PwApplicationHelper::copyRecursive($r, $this->tmpPackage);
			WindFolder::clearRecur($r, true);
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
			$directory[$v] = $localDirectory[$v];
		}
		$this->_log('way of moving directory' . var_export($strtr, true));
		$moveList = $newFileList = array();
		
		$url = PwApplicationHelper::acloudUrl(
			array('a' => 'forward', 'do' => 'getVersionHash', 'pwversion' => $this->local));
		/* 从线上获取当前版本的所有文件md5 */
		$result = PwApplicationHelper::requestAcloudData($url);
		if ($result['code'] !== '0' || !$result['info']) return new PwError(array('APPCENTER:upgrade.version.hash.fail', array($result['msg'])));
		$md5List = array();
		foreach ($result['info'] as $v) {
			$md5List[current($v)] = key($v);
		}
		$this->_log('obtain the md5 list of current version' . var_export($md5List, true));
		
		foreach ($fileList as $v) {
			$_v = $root . $v;
			$file = $v;
			foreach ($strtr as $search => $replace) {
				if (0 === strpos($_v, $search)) {
					$file = str_replace($root, '', $replace . substr($_v, strlen($search)));
					$moveList[$v] = $file;
					break;
				}
			}
			$newFileList[$file] = $md5List[$v];
		}
		$this->_log('files need to move ' . var_export($moveList, true));
		foreach ($moveList as $old => $new) {
			copy($sourceDir . DIRECTORY_SEPARATOR . $old, $sourceDir . DIRECTORY_SEPARATOR . $new);
			WindFile::del($sourceDir . DIRECTORY_SEPARATOR . $old);
			if ($old == 'www/update.php') {
				$content = WindFile::read($sourceDir . DIRECTORY_SEPARATOR . $new);
				$content = str_replace('../src/wekit.php', Wind::getRealPath('SRC:wekit'), $content);
				WindFile::write($sourceDir . DIRECTORY_SEPARATOR . $new, $content);
			}
		}
		if ($strtr) {
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
		return PwSystemHelper::validateMd5($fileList);
	}

	/**
	 * 进行升级
	 */
	public function doUpgrade($fileList, $useFtp = 0) {
		$root = Wind::getRealDir('ROOT:', true);
		$source = Wind::getRealDir('DATA:upgrade') . DIRECTORY_SEPARATOR . $this->target;
		Wind::import('APPS:appcenter.service.srv.helper.PwFtpSave');
		if ($useFtp) {
			try {
				$ftp = new PwFtpSave($useFtp);
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
					$ftp->upload($source . DIRECTORY_SEPARATOR . $v, $v);
				} catch (WindFtpException $e) {
					return new PwError('APPCENTER:upgrade.upload.fail', array($v));
				}
			} else {
				$r = @copy($source . DIRECTORY_SEPARATOR . $v, $root . DIRECTORY_SEPARATOR . $v);
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