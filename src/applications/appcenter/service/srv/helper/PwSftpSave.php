<?php
Wind::import("WIND:ftp.AbstractWindFtp");
@set_time_limit(1000);
require_once Wind::getRealPath('LIB:utility.phpseclib.Net.SFTP');
/**
 * sftp 保存
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwSftpSave.php 21939 2012-12-17 07:13:16Z long.shi $
 * @package wind
 */
class PwSftpSave extends AbstractWindFtp {

	protected $port = 22;
	
	public function __construct($config = array()) {
		$this->initConfig($config);
		$this->conn = new Net_SFTP($this->server, $this->port, $this->timeout);
		if (!$this->conn->login($this->user, $this->pwd)) {
			throw new WindFtpException($this->user, WindFtpException::LOGIN_FAILED);
		}
		$this->dir && $this->changeDir($this->dir);
	}

	public function upload($localfile, $remotefile, $mode = null) {
		return $this->conn->put($remotefile, $localfile);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::rename()
	 */
	public function rename($oldName, $newName) {
		return $this->conn->rename($oldName, $newName);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::delete()
	 */
	public function delete($filename) {
		return $this->conn->delete($filename);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::download()
	 */
	public function download($localfile, $remotefile = '', $mode = 'A') {
		return $this->conn->get($remotefile, $localfile);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::fileList()
	 */
	public function fileList($dir = '') {
		return $this->conn->nlist($dir);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::close()
	 */
	public function close() {
		return $this->conn->disconnect();
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::mkdir()
	 */
	public function mkdir($dir) {
		return $this->conn->mkdir($dir);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::changeDir()
	 */
	public function changeDir($dir) {
		return $this->conn->chdir($dir);
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::size()
	 */
	public function size($file) {
		return true;
	}
	
	/*
	 * (non-PHPdoc) @see AbstractWindFtp::pwd()
	 */
	protected function pwd() {
		return $this->conn->pwd();
	}
}

?>