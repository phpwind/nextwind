<?php
defined('WEKIT_VERSION') or exit(403);
Wind::import('APPS:admin.library.AdminBaseController');
/**
 * 应用的后台配置
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id: ManageController.php 21256 2012-12-03 09:20:14Z long.shi $
 * @package admin
 */
class ManageController extends AdminBaseController {
	
	private $file = 'EXT:demo.conf';
	private $default = array();
	
	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		$this->file = Wind::getRealPath($this->file, false);
	}
	
	/* (non-PHPdoc)
	 * @see WindController::run()
	 */
	public function run() {
		$conf = @include $this->file;
		$conf || $conf = $this->default;
		$this->setOutput($conf, 'conf');
	}
	
	/**
	 * 应用的设置提交
	 *
	 */
	public function doRunAction() {
		$conf = $this->getInput('conf', 'post');
		WindFile::savePhpData($this->file, $conf);
		$this->showMessage('success');
	}
}

?>