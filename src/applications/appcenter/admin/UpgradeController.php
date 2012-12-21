<?php
Wind::import('APPS:appcenter.service.srv.helper.PwSystemHelper');
Wind::import('ADMIN:library.AdminBaseController');
Wind::import('APPS:appcenter.service.srv.helper.PwFtpSave');
Wind::import('APPS:appcenter.service.srv.helper.PwSftpSave');
/**
 * 在线升级
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: UpgradeController.php 22086 2012-12-19 05:46:38Z long.shi $
 * @package appcenter
 */
class UpgradeController extends AdminBaseController {
	protected $upgrade_temp = 'DATA:upgrade.files.tmp';
	protected $fileList = array();
	protected $md5FileList = array();
	protected $localFileList = array();
	protected $version;
	/**
	 *
	 * @var PwSystemInstallation
	 */
	protected $installService = null;
	protected $status = array(
		1 => 'check', 
		2 => 'list', 
		3 => 'download', 
		4 => 'file', 
		5 => 'doupgrade',
		6 => 'db',
		7 => 'end');

	public function beforeAction($handlerAdapter) {
		$this->installService = $this->_loadInstallation();
		$this->upgrade_temp = Wind::getRealPath($this->upgrade_temp, true);
		
		$action = $handlerAdapter->getAction();
		if (!in_array($action, array('run', 'check'))) {
			$r = @include $this->upgrade_temp;
			$this->_checkLegal($action, $r);
		}
	}
	
	public function afterAction($handlerAdapter) {
		$this->installService->flushLog();
	}

	public function run() {
		$r = $this->installService->checkEnvironment();
		if ($r instanceof PwError) $this->setOutput(1, 'disable');
		$step = (int)Wekit::cache()->get('system_upgrade_step');
		// 继续上次的流程
		$action = '';
		if ($step > 1) {
			$step++;
			isset($this->status[$step]) && $action = $this->status[$step];
			$this->setOutput($step < 6, 'recheck');
		}
		$this->setOutput($action, 'action');
	}

	/**
	 * step 1: 请求升级信息，获取列表，写入upgrade.temp文件
	 */
	public function checkAction() {
		$this->_clear();
		WindFolder::mkRecur(dirname($this->upgrade_temp));
		$r = $this->installService->checkUpgrade();
		$result = array();
		if ($r) {
			$md5List = $fileList = array();
			foreach ($r['filelist'] as $v) {
				$md5List[] = key($v);
				$fileList[] = current($v);
			}
			$write_result = WindFile::savePhpData($this->upgrade_temp, 
				array('version' => $r['version'], 'filelist' => $fileList, 'md5list' => $md5List));
			if (!$write_result) $this->showError(array('APPCENTER:upgrade.write.fail', array('data')));
			Wekit::cache()->set('system_upgrade_step', 1);
			unset($r['filelist']);
			$r['oldversion'] = NEXT_VERSION;
			$r['oldrelease'] = NEXT_RELEASE;
			$r['usezip'] = function_exists('gzinflate');
			//TODO 
			$r['usezip'] = 0;
			Wekit::cache()->set('system_upgrade', $r);
			$result = array(
				'name' => $r['version'],
				'time' => Pw::time2str($r['time']),
				);
		}
		$this->setOutput($r === false, 'connect_fail');
		$this->setOutput($result, 'result');
	}

	/**
	 * step2 :确认后，列出文件
	 */
	public function listAction() {
		if (2 != Wekit::C('site', 'visit.state')) {
			$this->setOutput(1, 'error');
		} else {
			$this->setOutput($this->fileList, 'list');
			Wekit::cache()->set('system_upgrade_step', 2);
			PwSystemHelper::log('output the file list to upgrade', $this->version, true);
		}
	}

	/**
	 * step 3 : 下载
	 */
	public function downloadAction() {
		$lang = Wind::getComponent('i18n');
		$status = Wekit::cache()->get('system_upgrade');
		$this->installService->useZip = $status['usezip'];
		$success = 1;
		if ($this->installService->useZip) {
			$r = $this->installService->download($status['url'], $status['hash']);
			if ($r instanceof PwError) {
				$success = 0;
				$this->setOutput($lang->getMessage($r->getError()), 'msg');
			}
		} else {
			$step = (int) Wekit::cache()->get('system_upgrade_download_step');
			if ($step < count($this->fileList)) {
				$success = 0;
				$file = $this->fileList[$step];
				$r = $this->installService->download(substr($status['url'], 0, -4), $this->md5FileList[$step], $file);
				if ($r instanceof PwError) {
					$this->setOutput($lang->getMessage($r->getError()), 'msg');
				} else {
					Wekit::cache()->set('system_upgrade_download_step', ++$step);
				}
				$this->setOutput($this->fileList, 'fileList');
				$this->setOutput($step, 'step');
			}
		}
		if ($success) {
			$fileList = $this->installService->sortDirectory($this->fileList);
			if ($fileList instanceof PwError) $this->showError($fileList->getError());
			WindFile::savePhpData($this->upgrade_temp,
			array('version' => $this->version, 'filelist' => $this->fileList, 'newfilelist' => $fileList));
			Wekit::cache()->set('system_upgrade_step', 3);
			PwSystemHelper::log('download file success', $this->version);
			$this->showMessage('APPCENTER:upgrade.download.success', 'appcenter/upgrade/file', true);
		}
	}

	/**
	 * step 4 :--文件比对--
	 * 文件目录可写
	 * 文件md5比对
	 */
	public function fileAction() {
		$success = 1;
		$useFtp = $this->getInput('ftp', 'post');
		if (!$useFtp) {
			$r = PwSystemHelper::checkFolder($this->localFileList);
			if ($r !== true) {
				list(, $folder) = $r;
				PwSystemHelper::log('folder write fail!' . $folder, $this->version);
				$success = 0;
				$lang = Wind::getComponent('i18n');
				$msg = $lang->getMessage('APPCENTER:upgrade.write.fail', array($folder));
				$this->setOutput($msg, 'msg');
			}
		} else {
			try {
				$config = $this->getInput(array('server', 'port', 'user', 'pwd', 'dir', 'sftp'), 'post', true);
				$ftp = $config['sftp'] ? new PwSftpSave($config) : new PwFtpSave($config);
			} catch (WindFtpException $e) {
				$this->showError(array('APPCENTER:upgrade.ftp.fail', array($e->getMessage())), 'appcenter/upgrade/file', true);
			}
			$ftp->close();
			Wekit::cache()->set('system_upgrade_ftp', $config);
		}
		if ($success) {
			list($change, $unchange, $new) = $this->installService->validateLocalFiles($this->localFileList);
			$this->setOutput(array('change' => $change, 'unchange' => $unchange, 'new' => $new));
			Wekit::cache()->set('system_upgrade_step', 4);
			PwSystemHelper::log('file md5 check success', $this->version);
		}
	}

	/**
	 * step 5 : 开始升级
	 * 备份源文件
	 * 覆盖文件
	 */
	public function doupgradeAction() {
		$r = $this->installService->backUp($this->localFileList);
		if ($r instanceof PwError) $this->showError($r->getError());
		$useFtp = Wekit::cache()->get('system_upgrade_ftp');
		$r = $this->installService->doUpgrade($this->localFileList, $useFtp);
		if ($r instanceof PwError) $this->showError($r->getError());
		Wekit::cache()->set('system_upgrade_step', 5);
		PwSystemHelper::log('file upgrade success', $this->version);
	}

	/**
	 * step 6 : 数据库更新操作
	 * 
	 * 先执行update.sql,再跳转到update.php
	 *
	 */
	public function dbAction() {
		$sqlFile = Wind::getRealPath('PUBLIC:update.sql', true);
		$success = 1;
		if (!file_exists($sqlFile)) {
			$this->_executePhp();
			Wekit::cache()->set('system_upgrade_step', 6);
			PwSystemHelper::log('no db update', $this->version);
			$this->forwardRedirect(WindUrlHelper::createUrl('appcenter/upgrade/end'));
		}
		$step = (int) Wekit::cache()->get('system_upgrade_db_step');
		$lang = Wind::getComponent('i18n');
		try {
			/* @var $db WindConnection */
			$db = Wind::getComponent('db');
			$sqlArray = PwSystemHelper::sqlParser(WindFile::read($sqlFile),
				$db->getConfig('charset', '', 'utf8'), $db->getTablePrefix(),
				$db->getConfig('engine', '', 'MYISAM'));
			end($sqlArray);
			if ($step > key($sqlArray)) {
				$this->_executePhp();
				Wekit::cache()->set('system_upgrade_step', 6);
				PwSystemHelper::log('db update success', $this->version);
				$this->forwardRedirect(WindUrlHelper::createUrl('appcenter/upgrade/end'));
			}
			$sql = $sqlArray[$step];
			if ($sql) {
				foreach ($sql as $v) {
					PwSystemHelper::log('execute sql ' . $v, $this->version);
					$db->execute($v);
				}
			}
		} catch (Exception $e) {
			if ($e instanceof WindForwardException) throw $e;
			$success = 0;
			$this->setOutput(1, 'error');
			PwSystemHelper::log('execute sql failed' . $e->getMessage(), $this->version);
			$this->setOutput($lang->getMessage('APPCENTER:upgrade.db.error', array(implode(';', $sql))), 'msg');
		}
		if ($success) {
			$this->setOutput($lang->getMessage('APPCENTER:upgrade.db.update', array($step, key($sqlArray))), 'msg');
		}
		Wekit::cache()->set('system_upgrade_db_step', ++$step);
	}
	
	/**
	 * 结束
	 *
	 */
	public function endAction() {
		list($upgrade, $back) = $this->_backSuccess();
		Wekit::load('hook.srv.PwHookRefresh')->refresh();
		Wekit::load('SRV:cache.srv.PwCacheUpdateService')->updateAll();
		Wekit::load('domain.srv.PwDomainService')->refreshTplCache();
		
		PwSystemHelper::log('upgrade success, current version: ' . 'phpwind ' . NEXT_VERSION . ' release ' . NEXT_RELEASE, $this->version);
		$this->_clear();
		Pw::setCookie('checkupgrade', '', -1);
		$this->setOutput(
			array(
				'systeminfo' => 'phpwind ' . NEXT_VERSION . ' release ' . NEXT_RELEASE,
				'back' => str_replace(ROOT_PATH, '', $back),
				'upgrade' => str_replace(ROOT_PATH, '', $upgrade)));
	}

	private function _clear() {
		WindFolder::clearRecur(dirname($this->upgrade_temp), true);
		$useFtp = Wekit::cache()->get('system_upgrade_ftp');
		$updateFile = Wind::getRealPath('PUBLIC:update');
		$updateSql = Wind::getRealPath('PUBLIC:update.sql', true);
		if ($useFtp) {
			$updateFile = str_replace(ROOT_PATH, '', $updateFile);
			$updateSql = str_replace(ROOT_PATH, '', $updateSql);
			try {
				$ftp = $useFtp['sftp'] ? new PwSftpSave($useFtp) : new PwFtpSave($useFtp);
				foreach (array($updateFile, $updateSql) as $v) {
					$ftp->delete($v);
				}
			} catch (WindFtpException $e) {
				
			}
			$ftp->close();
		} else {
			WindFile::del($updateFile);
			WindFile::del($updateSql);
		}
		Wekit::cache()->batchDelete(array('system_upgrade', 'system_upgrade_step', 'system_upgrade_db_step', 'system_upgrade_ftp', 'system_upgrade_download_step'));
	}

	/**
	 * 更新成功后备份
	 *
	 * @return multitype:string 
	 */
	private function _backSuccess() {
		$data_dir = Wind::getRealDir('DATA:');
		$suffix = '[' . Pw::time2str(WEKIT_TIMESTAMP, 'Y-m-d H:i') . ']';
		$up_source = $data_dir . 'upgrade';
		$up_target = $data_dir . 'upgrade' . $suffix;
		PwApplicationHelper::copyRecursive($up_source, $up_target);
		$status = Wekit::cache()->get('system_upgrade');
		$ba_source = $data_dir . 'backup' . DIRECTORY_SEPARATOR . $status['oldversion'] . '_' . $status['oldrelease'];
		$ba_target = $data_dir . 'backup' . $suffix;
		PwApplicationHelper::copyRecursive($ba_source, $ba_target);
		WindFolder::clearRecur($up_source, true);
		WindFolder::clearRecur($ba_source, true);
		return array($up_target, $ba_target);
	}

	/**
	 *
	 * @return PwSystemInstallation
	 */
	private function _loadInstallation() {
		return Wekit::load('APPS:appcenter.service.srv.PwSystemInstallation');
	}

	/**
	 * 检查是否合法请求
	 *
	 * @param unknown_type $action        	
	 * @param unknown_type $r        	
	 */
	private function _checkLegal($action, $r) {
		$step = Wekit::cache()->get('system_upgrade_step');
		$status = Wekit::cache()->get('system_upgrade');
		$legal = true;
		if (!$step || !$status) $legal = false;
		if ($action != $this->status[++$step]) $legal = false;
		if ($status['version'] != $r['version']) $legal = false;
		$this->version = $status['version'];
		if (!is_array($r['filelist'])) $legal = false;
		if (!$legal) {
			$this->_clear();
			$this->showError('APPCENTER:upgrade.illegal.request', 'appcenter/upgrade/check');
		}
		$this->installService->target = $this->version;
		$this->fileList = $r['filelist'];
		$this->localFileList = isset($r['newfilelist']) ? $r['newfilelist'] : array();
		$this->md5FileList = $r['md5list'];
		$this->setOutput($this->version, 'version');
	}
	
	private function _executePhp() {
		//update.php
		$sqlPhp = Wind::getRealPath('PUBLIC:update');
		if (file_exists($sqlPhp)) {
			Wekit::cache()->set('system_upgrade_step', 6);
			$this->forwardRedirect(Wekit::app()->baseUrl . '/update.php?from=' . urlencode(WindUrlHelper::createUrl('appcenter/upgrade/end')));
		}
	}
}

?>