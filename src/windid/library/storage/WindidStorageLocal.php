<?php
defined('WINDID_VERSION') || exit('Forbidden');
!defined('ATTACH_PATH') && define('ATTACH_PATH', WINDID_PATH.'/../../attachment/');
/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidStorageLocal.php 22908 2012-12-28 08:59:54Z gao.wanggao $
 * @package upload
 */

class WindidStorageLocal {

	public function get($path, $ifthumb) {
		$dir = dirname($path);
		$file = basename($path);
		if ($ifthumb & 2) {$dir .= '/thumb/mini';} elseif ($ifthumb & 1) {$dir .= '/thumb';}
		return Windid::attachUrl() . '/' . $dir . '/' . $file;
	}
	
	/**
	 * 存储附件,如果是远程存储，记得删除本地文件
	 *
	 * @param string $source 本地源文件地址
	 * @param string $filePath 存储相对位置
	 * @return bool
	 */
	public function save($source, $filePath) {
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
		return ATTACH_PATH . $dir . $filename;
	}
	
	/**
	 * 删除附件
	 *
	 * @param string $path 附件地址
	 */
	public function delete($path, $ifthumb = 0) {
		$this->deleteFile(ATTACH_PATH . $path);
		if ($ifthumb) {
			$dir = dirname($path);
			$file = basename($path);
			($ifthumb & 1) && $this->deleteFile(ATTACH_PATH . $dir . '/thumb/' . $file);
			($ifthumb & 2) && $this->deleteFile(ATTACH_PATH . $dir . '/thumb/mini/' . $file);
		}
		return true;
	}
	
	protected  function deleteFile($filename) {
		return WindFile::del(WindSecurity::escapePath($filename, true));
	}
	
	protected function createFolder($path) {
		if (!is_dir($path)) {
			$this->createFolder(dirname($path));
			@mkdir($path);
			@chmod($path, 0777);
			@fclose(@fopen($path . '/index.html', 'w'));
			@chmod($path . '/index.html', 0777);
		}
	}
}
?>