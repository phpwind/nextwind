<?php
defined('WINDID_VERSION') || exit('Forbidden');

Wind::import('WINDID:library.image.WindidImageThumb');

/**
 * image 对象
 *
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author Jianmin Chen <sky_hold@163.com>
 * @version $Id: WindidImage.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package lib.image
 */

class WindidImage {
	
	public $filename;	//文件地址
	public $ext;		//后缀名
	public $width;		//文件宽度
	public $height;		//文件高度
	public $type;		//文件类型
	
	protected $_source = null;
	protected $_exts = array('jpg', 'jpeg', 'jpe', 'jfif');

	public function __construct($filename) {
		$this->filename = $filename;
		$this->ext = $this->getExt($filename);
		$this->parse();
	}
	
	/**
	 * 分析图片
	 *
	 * return void
	 */
	public function parse() {
		/*
		if (function_exists('read_exif_data') && in_array($this->ext, $this->_exts)) {
			$datatemp = @read_exif_data($this->filename);
			$this->width = $datatemp['COMPUTED']['Width'];
			$this->height = $datatemp['COMPUTED']['Height'];
			$this->type = 2;
		}
		if (!$this->width) {
			list($this->width, $this->height, $this->type) = @getimagesize($this->filename);
		}*/
		list($this->width, $this->height, $this->type) = @getimagesize($this->filename);
		$typeMap = array(
			1 => 'gif',
			2 => 'jpeg',
			3 => 'png',
			6 => 'bmp'
		);
		$this->type = isset($typeMap[$this->type]) ? $typeMap[$this->type] : '';
	}
	
	/**
	 * 判断是否为正常的图像
	 *
	 * return bool
	 */
	public function isImage() {
		return empty($this->type) ? false : true;
	}
	
	/**
	 * 获取该图像的标识符
	 *
	 * return resource
	 */
	public function getSource() {
		if ($this->_source === null) {
			if (!$this->type || !function_exists('imagecreatefrom' . $this->type)) {
				$this->_source = false;
			} else {
				$imagecreatefromtype = 'imagecreatefrom' . $this->type;
				$this->_source = $imagecreatefromtype($this->filename);
			}
		}
		return $this->_source;
	}
	
	/**
	 * 获取文件后缀
	 *
	 * @param string $filename 文件名
	 * return string
	 */
	public function getExt($filename) {
		return strtolower(substr(strrchr($filename, '.'), 1));
	}
	
	/**
	 * 重新绘制图片(防止非法图片造成攻击)
	 */
	public function repaint() {
		if (!$source = $this->getSource()) return false;
		$imagefun = 'image' . $this->type;
		if (!function_exists($imagefun)) return false;
		if ($this->type == 'jpeg') {
			return call_user_func($imagefun, $source, $this->filename, 100);
		} else {
			return call_user_func($imagefun, $source, $this->filename);
		}
	}

	/**
	 * 生成缩略图
	 *
	 * @param string $thumbUrl 缩略图地址
	 * @param int $thumbWidth 宽度
	 * @param int $thumbHeight 高度
	 * @param int $quality 图片质量
	 * @param int $thumbType 缩略图生成方式 <1.等比缩略 2.居中截取 3.等比填充>
	 * @param int $forceMode 强制生成 <0.当文件尺寸小于缩略要求时，不生成 1.都生成>
	 * return mixed
	 */
	public function makeThumb($thumbUrl, $thumbWidth, $thumbHeight, $quality = 0, $thumbType = 0, $forceMode = 0) {
		$thumb = new WindidImageThumb($this);
		$thumb->setDstFile($thumbUrl);
		$thumb->setWidth($thumbWidth);
		$thumb->setHeight($thumbHeight);
		$thumb->setQuality($quality);
		$thumb->setType($thumbType);
		$thumb->setForceMode($forceMode);
		$result = $thumb->execute();
		return $result;
	}
}