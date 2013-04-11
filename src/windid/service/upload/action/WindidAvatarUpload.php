<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:upload.PwUploadAction');

/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAvatarUpload.php 24829 2013-02-22 03:46:48Z jieyin $
 * @package upload
 */

class WindidAvatarUpload extends PwUploadAction {
	
	public $isLocal = false;
	public $uid;
	public $udir;

	public function __construct($uid) {
		$this->ftype = array('jpg' => 2000, 'png' => 2000, 'jpeg' => 2000);
		$this->uid = $uid;
		$this->udir = Pw::getUserDir($this->uid);
	}
	
	/**
	 * @see PwUploadAction.check
	 */
	public function check() {
		return true;
	}
	
	/**
	 * @see PwUploadAction.allowType
	 */
	public function allowType($key) {
		return true;
	}
	
	/**
	 * @see PwUploadAction.getSaveName
	 */
	public function getSaveName(PwUploadFile $file) {
		return $this->uid . '.jpg';
	}
	
	/**
	 * @see PwUploadAction.getSaveDir
	 */
	public function getSaveDir(PwUploadFile $file) {
		return 'avatar/' . $this->udir . '/';
	}
	
	/**
	 * @see PwUploadAction.allowThumb
	 */
	public function allowThumb() {
		return true;
	}
	
	/**
	 * @see PwUploadAction.getThumbInfo
	 */
	public function getThumbInfo($filename, $dir) {
		return array(
			array($this->uid . '.jpg', $dir, 200, 200, 2, 1),
			array($this->uid . '_middle.jpg', $dir, 120, 120, 2, 1),
			array($this->uid . '_small.jpg', $dir, 50, 50, 2, 1)
		);
	}
	
	/**
	 * @see PwUploadAction.allowWaterMark
	 */
	public function allowWaterMark() {
		return false;
		//return $this->forum->forumset['watermark'];
	}

	/**
	 * @see PwUploadAction.update
	 */
	public function update($uploaddb) {
		return true;
	}

	public function getAids() {
		return array_keys($this->attachs);
	}

	public function getAttachInfo() {
		$array = current($this->attachs);
		$path  = Wekit::app('windid')->url->attach . '/' . $array['path'];
		//list($path) = geturl($array['attachurl'], 'lf', $array['ifthumb']&1);
		return array('aid' => $array['aid'], 'path' => $path);
	}
}
?>