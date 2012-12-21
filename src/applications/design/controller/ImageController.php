<?php
Wind::import('LIB:base.PwBaseController');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: ImageController.php 22295 2012-12-21 05:39:44Z gao.wanggao $ 
 * @package 
 */
class ImageController extends PwBaseController {
	
	public function run() {
		$id = (int)$this->getInput('id', 'get');
		if(!$id) exit();
		$image = $this->_getImageService()->get($id);
		header("Cache-control: max-age=600");
		header('Location: ' . $image);
		exit;
	}
	
	
	
	
	private function _getImageService() {
		return Wekit::load('design.srv.PwDesignAsynImageService');
	}

}
?>