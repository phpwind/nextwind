<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:upload.action.PwUploadAction');
Wind::import('COM:utility.WindUtility');

/**
 * the last known user to change this file in the repository  <$LastChangedBy: xiaoxia.xuxx $>
 * @author $Author: xiaoxia.xuxx $ Foxsee@aliyun.com
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwSpaceUpload.php 18058 2012-09-11 05:07:19Z xiaoxia.xuxx $ 
 * @package 
 */

class PwSpaceUpload extends PwUploadAction {
	public $dir;
	public $filename;

	
	public function __construct() {
		$this->ftype = array('jpeg'=>2000, 'jpg' => 2000, 'png' => '2000', 'gif' => 2000);
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
		$prename  = substr(md5(Pw::getTime() . WindUtility::generateRandStr(8)), 10, 15);
		$this->filename = $prename . '.' .$file->ext;
		return $this->filename;
	}
	
	/**
	 * @see PwUploadAction.getSaveDir
	 */
	public function getSaveDir(PwUploadFile $file) {
		list($y, $m, $d) = explode('-', date('Y-m-d', Pw::getTime()));
		$this->dir = "space/$y/$m/";
		return  $this->dir;
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
		return array(
			array()
		);
	}
	
	/**
	 * @see PwUploadAction.allowWaterMark
	 */
	public function allowWaterMark() {
		return false;
	}
	
	public function transfer() {
		return true;
	}

	/**
	 * @see PwUploadAction.update
	 */
	public function update($uploaddb) {
		foreach ($uploaddb as $key => $value) {
			$this->attachs = array(
				'name'      => $value['name'],
				'type'      => $value['type'],
				'path'		=> $this->dir . $this->filename,
				'size'      => $value['size'],
				'width'		=> $this->width,
				'height'	=> $this->height
			);
		}
		return true;
	}

	public function getAids() {
		return array_keys($this->attachs);
	}

	public function getAttachInfo() {
		return $this->attachs;
	}
}
?>