<?php

Wind::import('ADMIN:library.AdminBaseController');

/**
 * @author JianMin Chen <sky_hold@163.com> 2012-7-17
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: EditorController.php 20869 2012-11-14 07:43:49Z xiaoxia.xuxx $
 * @package admin
 * @subpackage controller.config
 */
class EditorController extends AdminBaseController {

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$configDs = Wekit::load('config.PwConfig');
		$config = $configDs->getValues('bbs');
		$this->setOutput($config, 'config');
	}

	/**
	 * 后台设置-编辑器设置
	 */
	public function dorunAction() {
		list($style, $contentDuplicate, $cvtimes, $imgOpen, $imgWidth, $imgHeight, $sizeMax, $flashOpen, $mediaOpen, $iframeOpen) = $this->getInput(array('style', 'content_duplicate', 'cvtimes', 'img_open', 'img_width', 'img_height', 'size_max', 'flash_open', 'media_open', 'iframe_open'), 'post');
		
		$config = new PwConfigSet('bbs');
		$config->set('editor.style', $style ? 1 : 0)
			->set('content.duplicate', $contentDuplicate ? 1 : 0)
			->set('ubb.cvtimes', abs(intval($cvtimes)))
			->set('ubb.img.open', $imgOpen ? 1 : 0)
			->set('ubb.img.width', abs(intval($imgWidth)))
			->set('ubb.img.height', abs(intval($imgHeight)))
			->set('ubb.size.max', abs(intval($sizeMax)))
			->set('ubb.flash.open', $flashOpen ? 1 : 0)
			->set('ubb.media.open', $mediaOpen ? 1 : 0)
			->set('ubb.iframe.open', $iframeOpen ? 1 : 0)
			->flush();
		$this->showMessage('ADMIN:success');
	}
}

?>