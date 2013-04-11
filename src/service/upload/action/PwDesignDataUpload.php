<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:upload.PwUploadAction');
Wind::import('COM:utility.WindUtility');

/**
 * the last known user to change this file in the repository  <$LastChangedBy: jieyin $>
 * @author $Author: jieyin $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDesignDataUpload.php 23975 2013-01-17 10:20:11Z jieyin $ 
 * @package 
 */

class PwDesignDataUpload extends PwUploadAction {
	
	public $moduleid = 0;
	public $key = 0;
	
	public function __construct($key, $moduleid) {
		$this->ftype = array('jpg' => 2000, 'jpeg' => 2000, 'png' => 2000, 'gif' => 2000);
		$this->ifftp = 0;
		$this->moduleid = (int)$moduleid;
		$this->key = $key;
	}
	
	/**
	 * @see PwUploadAction.check
	 */
	public function check() {
		foreach ($_FILES as $key => $value) {
			if (!$_FILES[$key]['size']) return new PwError('upload.fail');
		}
		
		return true;
	}
	
	/**
	 * @see PwUploadAction.allowType
	 */
	public function allowType($key) {
		if ($key == $this->key) return true;
	}
	
	/**
	 * @see PwUploadAction.getSaveName
	 */
	public function getSaveName(PwUploadFile $file) {
		$prename  = substr(md5(Pw::getTime() . WindUtility::generateRandStr(8)), 10, 15);
		$this->filename = $prename . '.' .$file->ext;
		return $this->filename;
	}
	
	/**
	 * @see PwUploadAction.getSaveDir
	 */
	public function getSaveDir(PwUploadFile $file) {
		return  $this->dir = 'module/' . $this->moduleid . '/';
	}
	
	/**
	 * @see PwUploadAction.allowThumb
	 */
	public function allowThumb() {
		return false;
	}
	
	/**
	 * @see PwUploadAction.getThumbInfo
	 */
	public function getThumbInfo($filename, $dir) {
		return array();
	}
	
	/**
	 * @see PwUploadAction.allowWaterMark
	 */
	public function allowWaterMark() {
		return false;
	}
	
	public function transfer() {
		return false;
	}

	/**
	 * @see PwUploadAction.update
	 */
	public function update($uploaddb) {
		foreach ($uploaddb as $key => $value) {
			$this->attachs = array(
				'name'      => $value['name'],
				'type'      => $value['type'],
				'path'		=> $this->dir,
				'filename'	=> $this->filename,
				'size'      => $value['size'],
				'ext'		=> $value['ext'],
			);
		}
		return true;
	}

	public function getAttachInfo() {
		return $this->attachs;
	}
}
?>