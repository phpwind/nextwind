<?php
Wind::import('WIND:ftp.WindSocketFtp');

/**
 * 普通ftp保存，不限制文件后缀
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: PwFtpSave.php 21939 2012-12-17 07:13:16Z long.shi $
 * @package appcenter
 */
class PwFtpSave extends WindSocketFtp {

	/* (non-PHPdoc)
	 * @see WindSocketFtp::upload()
	 */
	public function upload($localfile, $remotefile, $mode = 'A') {
		if (!in_array(($savedir = dirname($remotefile)), array('.', '/'))) {
			$this->mkdirs($savedir);
		}
		$remotefile = $this->rootPath . WindSecurity::escapePath($remotefile);
		if (!($fp = fopen($localfile, 'rb'))) {
			throw new WindFtpException($localfile, WindFtpException::FILE_READ_FOBIDDEN);
		}
		// 'I' == BINARY mode
		// 'A' == ASCII mode
		$mode != 'I' && $mode = 'A';
		$this->delete($remotefile);
		if (!$this->sendcmd('TYPE', $mode)) {
			throw new WindFtpException($mode, WindFtpException::COMMUNICATE_TYPE_FAILED);
		}
		$this->openTmpConnection();
		$this->sendcmd('STOR', $remotefile);
		while (!feof($fp)) {
			fwrite($this->tmpConnection, fread($fp, 4096));
		}
		fclose($fp);
		$this->closeTmpConnection();
		
		if (!$this->checkcmd()) {
			throw new WindFtpException('PUT', WindFtpException::COMMAND_FAILED);
		} else {
			$this->sendcmd('SITE CHMOD', base_convert(0644, 10, 8) . " $remotefile");
		}
		return $this->size($remotefile);
	}
	
	private function openTmpConnection() {
		$this->sendcmd('PASV', '', false);
		if (!($ip_port = $this->checkcmd(true))) {
			throw new WindFtpException('PASV', WindFtpException::COMMAND_FAILED);
		}
		if (!preg_match('/[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]{1,3},[0-9]+,[0-9]+/', $ip_port, $temp)) {
			throw new WindFtpException($ip_port, WindFtpException::COMMAND_FAILED_PASS_PORT);
		}
		$temp = explode(',', $temp[0]);
		$server_ip = "$temp[0].$temp[1].$temp[2].$temp[3]";
		$server_port = $temp[4] * 256 + $temp[5];
		if (!$this->tmpConnection = fsockopen($server_ip, $server_port, $errno, $errstr, $this->timeout)) {
			throw new WindFtpException("{$server_ip}:{$server_port}\r\nError:{$errstr} ({$errno})", WindFtpException::OPEN_DATA_CONNECTION_FAILED);
		}
		stream_set_timeout($this->tmpConnection, $this->timeout);
		return true;
	}
	
	private function sendcmd($cmd, $args = '', $check = true) {
		!empty($args) && $cmd .= " $args";
		fputs($this->conn, "$cmd\r\n");
		if ($check === true && !$this->checkcmd()) return false;
		return true;
	}
	
	private function closeTmpConnection() {
		return fclose($this->tmpConnection);
	}
	
	private function checkcmd($return = false) {
		$resp = $rcmd = '';
		$i = 0;
		do {
			$rcmd = fgets($this->conn, 512);
			$resp .= $rcmd;
		} while (++$i < 20 && !preg_match('/^\d{3}\s/is', $rcmd));
	
		if (!preg_match('/^[123]/', $rcmd)) return false;
		return $return ? $resp : true;
	}
}

?>