<?php
Wind::import('ADMIN:library.AdminBaseController');

/**
 * @author Qiong Wu <papa0924@gmail.com> 2011-12-15
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package admin
 * @subpackage controller.config
 */
class WatermarkController extends AdminBaseController {

	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$service = $this->_loadConfigService();
		$config = $service->getValues('attachment');
		$this->setOutput($config, 'config');
		$this->setOutput($this->getFontList(), 'fontList');
		$this->setOutput($this->getWaterMarkList(), 'markList');
		
	}

	/**
	 * 后台设置-水印管理
	 */
	public function dorunAction() {
		$config = new PwConfigSet('attachment');
		$config->set('mark.limitwidth', abs(intval($this->getInput('markLimitwidth', 'post'))))
			->set('mark.limitheight', abs(intval($this->getInput('markLimitheight', 'post'))))
			->set('mark.position', $this->getInput('markPosition', 'post'))
			->set('mark.gif', $this->getInput('markGif', 'post'))
			->set('mark.type', $this->getInput('markType', 'post'))
			->set('mark.text', $this->getInput('markText', 'post'))
			->set('mark.fontfamily', $this->getInput('markFontfamily', 'post'))
			->set('mark.fontsize', $this->getInput('markFontsize', 'post'))
			->set('mark.fontcolor', $this->getInput('markFontcolor', 'post'))
			->set('mark.quality', abs(intval($this->getInput('markQuality', 'post'))))
			->set('mark.file', $this->getInput('markFile', 'post'))
			->set('mark.transparency', abs(intval($this->getInput('markTransparency', 'post'))))
			->set('mark.quality', abs(intval($this->getInput('markQuality', 'post'))))
			->flush();
		$this->showMessage('ADMIN:success');
	}
	
	/**
	 * 水印预览
	 */
	public function viewAction() {
		$config = array('mark.limitwidth'=>abs(intval($this->getInput('markLimitwidth', 'post'))),
			'mark.limitheight'=>abs(intval($this->getInput('markLimitheight', 'post'))),
			'mark.position'=>$this->getInput('markPosition', 'post'),
			'mark.gif'=>$this->getInput('markGif', 'post'),
			'mark.type'=>$this->getInput('markType', 'post'),
			'mark.text'=>$this->getInput('markText', 'post'),
			'mark.fontfamily'=>$this->getInput('markFontfamily', 'post'),
			'mark.fontsize'=>$this->getInput('markFontsize', 'post'),
			'mark.fontcolor'=>$this->getInput('markFontcolor', 'post'),
			'mark.quality'=>abs(intval($this->getInput('markQuality', 'post'))),
			'mark.file'=>$this->getInput('markFile', 'post'),
			'mark.transparency'=>abs(intval($this->getInput('markTransparency', 'post'))),
			'mark.quality'=>abs(intval($this->getInput('markQuality', 'post')))
		);

		Wind::import('LIB:image.PwImage');
		Wind::import('LIB:image.PwImageWatermark');
		
		$image = new PwImage(Wind::getRealDir('REP:demo', false) . '/demo.jpg');
		$watermark = new PwImageWatermark($image);
		$watermark->setPosition($config['mark.position'])
			->setType($config['mark.type'])
			->setTransparency($config['mark.transparency'])
			->setQuality($config['mark.quality'])
			->setDstfile(Wind::getRealDir('PUBLIC:attachment',false) . '/demo.jpg');

		if ($config['mark.type'] == 1) {
			$watermark->setFile($config['mark.file']);
		} else {
			$watermark->setText($config['mark.text'])
				->setFontfamily($config['mark.fontfamily'])
				->setFontsize($config['mark.fontsize'])
				->setFontcolor($config['mark.fontcolor']);
		}
		$watermark->execute();

		$this->setOutput(Wekit::app()->attach . '/demo.jpg?' . time(), 'data');
		$this->showMessage('ADMIN:success');
	}

	/**
	 * 后台设置-水印策略设置
	 */
	public function setAction() {
		$service = $this->_loadConfigService();
		$config = $service->getValues('attachment');
		$this->setOutput($config, 'config');
	}
	

	/**
	 * 后台设置-水印策略设置
	 */
	public function dosetAction() {
		$config = new PwConfigSet('attachment');
		$config->set('mark.markset', $this->getInput('markset', 'post'))->flush();
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

	/**
	 * 获取字体列表
	 *
	 * @return array
	 */
	protected static function getFontList() {
		$_path = Wind::getRealDir('REP:font.');
		return WindFolder::read($_path);
	}

	/**
	 * 获取水印文件列表
	 *
	 * @return array
	 */
	protected static function getWaterMarkList() {
		$_path = Wind::getRealDir('REP:mark.');
		return WindFolder::read($_path);
	}

}
?>