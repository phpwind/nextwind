<?php
defined('WINDID_VERSION') || exit('Forbidden');
!defined('DATA_PATH') && define('DATA_PATH', WINDID_PATH.'/attachment/tmp/');
/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidStorageFtp.php 22665 2012-12-26 08:01:32Z gao.wanggao $
 * @package upload
 */

class WindidStorageFtp {

	private $_config;
	private $_ftp = null;

	public function __construct() {
		$this->_config = Windid::load('config.WindidConfig')->getValues('attachment');
	}

	public function get($path, $ifthumb) {
		$dir = dirname($path);
		$file = basename($path);
		if ($ifthumb & 2) {$dir .= '/thumb/mini';} elseif ($ifthumb & 1) {$dir .= '/thumb';}
		return $this->_config['ftp.url'] . '/' . $dir . '/' . $file;
	}
	
	/**
	 * 存储附件,如果是远程存储，记得删除本地文件
	 *
	 * @param string $source 本地源文件地址
	 * @param string $filePath 存储相对位置
	 * @return bool
	 */
	public function save($source, $filePath) {
		$this->_getFtp()->upload($source, $filePath, 'I');
		WindFile::del(WindSecurity::escapePath($source, true));
		return true;
	}
	
	/**
	 * 获取附件上传时存储在本地的文件地址
	 *
	 * @param string $filename 文件名
	 * @param string $dir 目录名
	 * @return string
	 */
	public function getAbsolutePath($filename, $dir) {
		return DATA_PATH . 'upload/' . gmdate('j', Windid::getTime()) . '/' . str_replace('/', '_', $dir) . $filename;
	}
	
	/**
	 * 删除附件
	 *
	 * @param string $path 附件地址
	 */
	public function delete($path, $ifthumb = 0) {
		$this->_getFtp()->delete($path);
		if ($ifthumb) {
			$dir = dirname($path);
			$file = basename($path);
			($ifthumb & 1) && $this->_getFtp()->delete($dir . '/thumb/' . $file);
			($ifthumb & 2) && $this->_getFtp()->delete($dir . '/thumb/mini/' . $file);
		}
		return true;
	}

	public function _getFtp() {
		if ($this->_ftp == null) {
			Wind::import('WIND:ftp.WindSocketFtp');
			$this->_ftp = new WindSocketFtp(array(
				'server' => $this->_config['ftp.server'],
				'port' => $this->_config['ftp.port'],
				'user' => $this->_config['ftp.user'],
				'pwd' => $this->_config['ftp.pwd'],
				'dir' => $this->_config['ftp.dir'],
				'timeout' => $this->_config['ftp.timeout'],
			));
		}
		return $this->_ftp;
	}
}
?>