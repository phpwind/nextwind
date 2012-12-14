<?php

Wind::import('ADMIN:library.AdminBaseController');

/**
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-15
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: AttachmentController.php 3284 2011-12-15 08:38:49Z yishuo $
 * @package admin
 * @subpackage controller.config
 */
class AttachmentController extends AdminBaseController {
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$service = $this->_loadConfigService();
		$config = $service->getValues('attachment');
		!($post_max_size = ini_get('post_max_size')) && $post_max_size = '2M';
		!($upload_max_filesize = ini_get('upload_max_filesize')) && $upload_max_filesize = '2M';
		$maxSize = min($post_max_size, $upload_max_filesize);

		$this->setOutput($maxSize, 'maxSize');
		$this->setOutput($config, 'config');
	}

	/**
	 * 后台设置-附件设置
	 */
	public function dorunAction() {
		list($pathsize, $attachnum, $extsize) = $this->getInput(array('pathsize', 'attachnum', 'extsize'), 'post');
		$_extsize = array();
		foreach ($extsize as $key => $value) {
			if (!empty($value['ext'])) $_extsize[$value['ext']] = abs(intval($value['size']));
		}
		$config = new PwConfigSet('attachment');
		$config->set('pathsize', abs(intval($pathsize)))->set('attachnum', abs(intval($attachnum)))->set('extsize', 
			$_extsize)->flush();
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 附件存储方式设置列表页
	 */
	public function storageAction() {
		/* @var $attService PwAttacmentService */
		$attService = Wekit::load('APPS:config.service.srv.PwAttacmentService');
		$storages = $attService->getStorages();
		$service = $this->_loadConfigService();
		$config = $service->getValues('attachment');
		$storageType = 'local';
		if (isset($config['storage.type']) && isset($storages[$config['storage.type']])) {
			$storageType = $config['storage.type'];
		}
		$this->setOutput($storages, 'storages');
		$this->setOutput($storageType, 'storageType');
	}

	/**
	 * 附件存储方式设置列表页
	 */
	public function dostroageAction() {
		$att_storage = $this->getInput('att_storage', 'post');
		/* @var $attService PwAttacmentService */
		$attService = Wekit::load('APPS:config.service.srv.PwAttacmentService');
		$_r = $attService->setStoragesComponents($att_storage);
		
		if ($_r === true) $this->showMessage('ADMIN:success');
		/* @var $_r PwError  */
		$this->showError($_r->getError());
	}

	/**
	 * 后台设置-ftp设置
	 */
	public function ftpAction() {
		$service = $this->_loadConfigService();
		$config = $service->getValues('attachment');
		$this->setOutput($config, 'config');
	}

	/**
	 * 后台设置-ftp设置
	 */
	public function doftpAction() {
		$config = new PwConfigSet('attachment');
		$config->set('ftp.url', $this->getInput('ftpUrl', 'post'))
			->set('ftp.server', $this->getInput('ftpServer', 'post'))
			->set('ftp.port', $this->getInput('ftpPort', 'post'))
			->set('ftp.dir', $this->getInput('ftpDir', 'post'))
			->set('ftp.user', $this->getInput('ftpUser', 'post'))
			->set('ftp.pwd', $this->getInput('ftpPwd', 'post'))
			->set('ftp.timeout', abs(intval($this->getInput('ftpTimeout', 'post'))))
			->flush();
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 后台设置-附件缩略设置
	 */
	public function thumbAction() {
		$config = $this->_loadConfigService()->getValues('attachment');
		$this->setOutput($config, 'config');
// 		$this->setOutput(Wekit::C('attachment'), 'config');
	}

	/**
	 * 后台设置-附件缩略设置
	 */
	public function dothumbAction() {
		list($thumb, $thumbsize_width, $thumbsize_height, $quality) = $this->getInput(
			array('thumb', 'thumbsize_width', 'thumbsize_height', 'quality'), 'post');
		$config = new PwConfigSet('attachment');
		$config->set('thumb', intval($thumb))->set('thumb.size.width', $thumbsize_width)->set('thumb.size.height', 
			$thumbsize_height)->set('thumb.quality', $quality)->flush();
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 后台设置-附件缩略预览
	 */
	public function viewAction() {
		list($thumb, $thumbsize_width, $thumbsize_height, $quality) = $this->getInput(
			array('thumb', 'thumbsize_width', 'thumbsize_height', 'quality'), 'post');
		
		Wind::import('LIB:image.PwImage');
		$image = new PwImage(Wind::getRealDir('REP:demo', false) . '/demo.jpg');
		$thumburl = Wind::getRealDir('PUBLIC:attachment', false) . '/demo_thumb.jpg';
		$image->makeThumb($thumburl, $thumbsize_width, $thumbsize_height, $quality, $thumb);
		
		$data = array('img' => Wekit::app()->attach . '/demo_thumb.jpg?' . time());
		$this->setOutput($data, 'data');
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 加载Config DS 服务
	 * 
	 * @return PwConfig
	 */
	private function _loadConfigService() {
		return Wekit::load('config.PwConfig');
	}
}

?>