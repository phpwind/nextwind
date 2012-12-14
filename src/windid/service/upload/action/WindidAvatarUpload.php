<?php
defined('WINDID_VERSION') || exit('Forbidden');

Wind::import('WINDID:service.upload.action.WindidUploadAction');

/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidAvatarUpload.php 21612 2012-12-11 12:00:42Z gao.wanggao $
 * @package upload
 */

class WindidAvatarUpload extends WindidUploadAction {
	
	public $isLocal = false;
	public $uid;
	public $udir;

	public function __construct($uid) {
		$this->ftype = array('jpg' => 2000, 'png' => 2000, 'jpeg' => 2000);
		$this->uid = $uid;
		$this->udir = Windid::getUserDir($this->uid);
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
	public function getSaveName(WindidUploadFile $file) {
		return $this->uid . '.jpg';
	}
	
	/**
	 * @see PwUploadAction.getSaveDir
	 */
	public function getSaveDir(WindidUploadFile $file) {
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
			array($this->uid . '.jpg', $dir, 200, 200, 2),
			array($this->uid . '_middle.jpg', $dir, 120, 120, 0),
			array($this->uid . '_small.jpg', $dir, 50, 50, 0)
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
		$path  = Windid::attachUrl() . '/' . $array['path'];
		//list($path) = geturl($array['attachurl'], 'lf', $array['ifthumb']&1);
		return array('aid' => $array['aid'], 'path' => $path);
	}
}
?>