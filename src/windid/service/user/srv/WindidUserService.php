<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserService.php 22500 2012-12-25 03:54:47Z gao.wanggao $ 
 * @package 
 */
class WindidUserService {
	
	public function defaultAvatar($uid, $type = 'face') {
		Wind::import('WINDID:service.upload.WindidUpload');
		$_avatar = array('.jpg' => '_big.jpg', '_middle.jpg' => '_middle.jpg', '_small.jpg' => '_small.jpg');
		$defaultBanDir = Wind::getRealDir('PUBLIC:') . 'res/images/face/';
		Wind::import('WINDID:service.config.srv.WindidStoreService');
		$srv = new WindidStoreService();
		$store = $srv->getStore();
		$fileDir =  '/avatar/' . Windid::getUserDir($uid) . '/';
		foreach ($_avatar as $des => $org) {
			$toPath = $store->getAbsolutePath($uid . $des, $fileDir);
			$fromPath = $defaultBanDir . $type . $org;
			WindidUpload::createFolder(dirname($toPath));
			WindidUpload::copyFile($fromPath, $toPath);
			$store->save($toPath, $fileDir . $uid . $des);
		}
		return true;
	}
}
?>