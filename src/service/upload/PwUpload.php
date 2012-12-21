<?php

defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:image.PwImage');

/**
 * 上传组件
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwUpload.php 22380 2012-12-21 14:54:07Z jieyin $
 * @package upload
 */

class PwUpload {

	protected $bhv;		// 上传行为配置
	protected $store;	// 附件存储器

	public function __construct(PwUploadAction $bhv) {
		$this->bhv = $bhv;
		$this->setStore();
	}

	public function getBehavior() {
		return $this->bhv;
	}
	
	/**
	 * 检查是否可以上传
	 *
	 * @return bool|PwError
	 */
	public function check() {
		return $this->bhv->check();
	}
	
	/**
	 * 获取已上传附件个数
	 *
	 * @return int
	 */
	public function getUploadNum() {
		return $this->bhv->getUploadNum();
	}
	
	/**
	 * 检查上传文件是否符合规定
	 *
	 * @param PwUploadFile $file
	 * @return bool|PwError
	 */
	public function checkFile($file) {
		if (!$file->ext || !isset($this->bhv->ftype[$file->ext])) {
			return new PwError(array('upload.ext.error', array('{ext}' => '.' . $file->ext)));
		}
		if ($file->size < 1) {
			return new PwError('upload.size.less');
		}
		if ($file->size > $this->bhv->ftype[$file->ext] * 1024) {
			return new PwError(array('upload.size.over', array('{size}' => $this->bhv->ftype[$file->ext])));
		}
		return true;
	}
	
	/**
	 * 设置附件存储对象
	 */
	public function setStore() {
		$this->store = Wind::getComponent($this->bhv->isLocal ? 'localStorage' : 'storage');
	}
	
	/**
	 * 过滤文件名
	 *
	 * @param string $filename
	 * @return string
	 */
	public function filterFileName($filename) {
		return preg_replace('/\.(php|asp|jsp|cgi|fcgi|exe|pl|phtml|dll|asa|com|scr|inf)$/i', ".scp_\\1" , $filename);
	}
	
	/**
	 * 上传附件主流程
	 *
	 * @return mixed
	 */
	public function execute() {
		$uploaddb = array();
		foreach ($_FILES as $key => $value) {
			if (!self::isUploadedFile($value['tmp_name']) || !$this->bhv->allowType($key)) {
				continue;
			}
			$file = new PwUploadFile($key, $value);
			if (($result = $this->checkFile($file)) !== true) {
				return $result;
			}
			$file->filename = $this->filterFileName($this->bhv->getSaveName($file));
			$file->savedir = $this->bhv->getSaveDir($file);
			$file->source = $this->store->getAbsolutePath($file->filename, $file->savedir);

			if (!self::moveUploadedFile($value['tmp_name'], $file->source)) {
				return new PwError('upload.fail');
			}
			if (($result = $file->operate($this->bhv, $this->store)) !== true) {
				return $result;
			}
			if (($result = $this->saveFile($file)) !== true) {
				return $result;
			}
			$uploaddb[] = $file->getInfo();
		}
		return $this->bhv->update($uploaddb);
	}
	
	/**
	 * 保存文件
	 *
	 * @param PwUploadFile $file
	 * @return bool|PwError
	 */
	public function saveFile($file) {
		if (($result = $this->store->save($file->source, $file->fileuploadurl)) !== true) {
			return $result;
		}
		if ($thumb = $file->getThumb()) {
			foreach ($thumb as $key => $value) {
				$this->store->save($value[0], $value[1]);
			}
		}
		return true;
	}
	
	/**
	 * 统计待上传附件个数
	 *
	 * @return int
	 */
	public static function countUploadedFile() {
		$i = 0;
		foreach ($_FILES as $key => $value) {
			if (self::isUploadedFile($value['tmp_name'])) $i++;
		}
		return $i;
	}
	
	/**
	 * 判断是否是正常的上传文件
	 *
	 * @param string $tmp_name
	 * @return bool
	 */
	public static function isUploadedFile($tmp_name) {
		if (!$tmp_name || $tmp_name == 'none') {
			return false;
		} elseif (function_exists('is_uploaded_file') && !is_uploaded_file($tmp_name) && !is_uploaded_file(str_replace('\\\\', '\\', $tmp_name))) {
			return false;
		} else {
			return true;
		}
	}
	
	/**
	 * 移动上传文件
	 *
	 * @param string $tmp_name 源文件
	 * @param string $filename 移动后的文件地址
	 * @return bool
	 */
	public static function moveUploadedFile($tmp_name, $filename) {
		if (strpos($filename, '..') !== false || strpos($filename, '.php.') !== false || preg_match("/\.php$/i", $filename)) {
			exit('illegal file type!');
		}
		self::createFolder(dirname($filename));
		if (function_exists("move_uploaded_file") && @move_uploaded_file($tmp_name, $filename)) {
			@chmod($filename, 0777);
			return true;
		}
		if (self::copyFile($tmp_name, $filename)) {
			return true;
		}
		return false;
	}
	
	/**
	 * 复制文件
	 *
	 * @param string $srcfile 源文件
	 * @param string $dstfile 目标文件地址
	 * @return bool
	 */
	public static function copyFile($srcfile, $dstfile) {
		if (@copy($srcfile, $dstfile)) {
			@chmod($dstfile, 0777);
			return true;
		}
		if (is_readable($srcfile)) {
			file_put_contents($dstfile, file_get_contents($srcfile));
			if (file_exists($dstfile)) {
				@chmod($dstfile, 0777);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 创建目录
	 *
	 * @param string $path
	 */
	public static function createFolder($path) {
		if (!is_dir($path)) {
			self::createFolder(dirname($path));
			@mkdir($path);
			@chmod($path, 0777);
			@fclose(@fopen($path . '/index.html', 'w'));
			@chmod($path . '/index.html', 0777);
		}
	}

	public function __call($methodName, $args) {
		if (!method_exists($this->bhv, $methodName)) {
			return false;
		}
		$method = new ReflectionMethod($this->bhv, $methodName);
		if ($method->isPublic()) {
			return call_user_func_array(array(&$this->bhv, $methodName), $args);
		}
		return false;
	}
}


/**
 * 上传文件对象
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @package upload
 */
class PwUploadFile {

	public $key;
	public $id;
	public $attname;
	public $name;
	public $size;
	public $type = 'zip';
	public $ifthumb = 0;
	public $filename;
	public $savedir;
	public $fileuploadurl = '';
	public $ext;
	
	protected $_thumb = array();

	public function __construct($key, $value) {
		list($t, $i) = explode('_', $key);
		$this->id = intval($i);
		$this->attname = $t;
		$this->name = $value['name'];
		$this->size = intval($value['size']);
		$this->ext  = strtolower(substr(strrchr($this->name, '.'), 1));
	}

	public function getInfo() {
		return array(
			'id' => $this->id,
			'attname' => $this->attname,
			'name' => $this->name,
			'size' => $this->size,
			'type' => $this->type,
			'ifthumb' => $this->ifthumb,
			'fileuploadurl' => $this->fileuploadurl,
			'ext' => $this->ext,
			'thumb' => $this->_thumb
		);
	}
	
	/**
	 * 是否是图片
	 *
	 * @return bool
	 */
	public function isImage() {
		return in_array($this->ext, array('gif','jpg','jpeg','png','bmp','swf'));
	}
	
	/**
	 * 是否是文本
	 *
	 * @return bool
	 */
	public function isTxt() {
		return $this->ext == 'txt';
	}
	
	/**
	 * 附件处理
	 *
	 * @param PwUploadAction $bhv 上传行为
	 * @param object 存储对象
	 * @return bool|PwError
	 */
	public function operate($bhv, $store) {
		$this->size = ceil(filesize($this->source) / 1024);
		$this->fileuploadurl = $this->savedir . $this->filename;

		if ($this->isImage()) {
			return $this->operateImage($bhv, $store);
		}
		if ($this->isTxt()) {
			return $this->operateTxt();
		}
		return true;
	}
	
	/**
	 * 图片处理
	 *
	 * @param PwUploadAction $bhv 上传行为
	 * @param object 存储对象
	 * @return bool|PwError
	 */
	public function operateImage($bhv, $store) {
		$image = new PwImage($this->source);
		if (!$image->isImage()) {
			return new PwError('upload.content.error');
		}
		if ($image->ext != 'swf') {
			if (!$image->getSource() && $image->ext != 'bmp') {
				return new PwError('upload.content.error');
			}
			if ($bhv->allowThumb()/* && $upload['ext'] != 'gif'*/) {
				$this->makeThumb($image, $bhv->getThumbInfo($this->filename, $this->savedir), $store);
			}
			if ($bhv->allowWaterMark()) {
				$waterinfo = $bhv->getWaterMarkInfo();
				$this->watermark($image, $waterinfo);
				foreach ($this->_thumb as $value) {
					$this->watermark(new PwImage($value[0]), $waterinfo);
				}
			}
			$this->type = 'img';
		}
		return true;
	}
	
	/**
	 * 文本处理
	 *
	 * @return bool|PwError
	 */
	public function operateTxt() {
		/*
		if (preg_match('/(onload|submit|post|form)/i', readover($source))) {
			P_unlink($source);
			showUploadMsg('upload_content_error');
		}*/
		$this->type = 'txt';
		return true;
	}
	
	/**
	 * 生成缩略图
	 *
	 * @param PwImage $image 图片对象
	 * @param array $thumbInfo 缩略图配置
	 * @param object 存储对象
	 */
	public function makeThumb(PwImage $image, $thumbInfo, $store) {
		$quality = Wekit::C('attachment', 'thumb.quality');
		foreach ($thumbInfo as $key => $value) {
			$thumburl = $store->getAbsolutePath($value[0], $value[1]);
			PwUpload::createFolder(dirname($thumburl));
			$result = $image->makeThumb($thumburl, $value[2], $value[3], $quality, $value[4], $value[5]);
			if ($result === true && $image->filename != $thumburl) {
				$ts = $image->getThumb();
				$this->ifthumb |= (1 << $key);
				$this->_thumb[$key] = array($thumburl, $value[1] . $value[0], $ts->getThumbWidth(), $ts->getThumbHeight());
			}
		}
	}

	public function getThumb() {
		return $this->_thumb;
	}
	
	/**
	 * 图片生成水印
	 *
	 * @param PwImage $image 图片对象
	 * @param array $options 生成方案配置
	 */
	public static function watermark(PwImage $image, $options = array()) {
		if (!in_array($image->type, array('gif', 'jpeg', 'png'))) return;
		$config = Wekit::C('attachment');
		if ($options) {
			foreach ($options as $key => $value) {
				$config['mark.' . $key] = $value;
			}
		}
		if ($image->type == 'gif' && !$config['mark.gif']) return;
		if ($image->width < $config['mark.limitwidth'] || $image->height < $config['mark.limitheight']) return;
		
		Wind::import('LIB:image.PwImageWatermark');
		$watermark = new PwImageWatermark($image);
		$watermark->setPosition($config['mark.position'])
			->setType($config['mark.type'])
			->setTransparency($config['mark.transparency'])
			->setQuality($config['mark.quality']);

		if ($config['mark.type'] == 1) {
			$watermark->setFile($config['mark.file']);
		} else {
			$watermark->setText($config['mark.text'])
				->setFontfamily($config['mark.fontfamily'])
				->setFontsize($config['mark.fontsize'])
				->setFontcolor($config['mark.fontcolor']);
		}
		$watermark->execute();
	}
}
?>