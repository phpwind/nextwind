<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidUserService.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package 
 */
class WindidUserService {
	
	public function defaultAvatar($uid, $type = 'face') {
		Wind::import('WINDID:service.upload.WindidUpload');
		$_avatar = array('.jpg' => '_big.jpg', '_middle.jpg' => '_middle.jpg', '_small.jpg' => '_small.jpg');
		$defaultBanDir = Wind::getRealDir('PUBLIC:') . 'res/images/face/';
		$fileDir = ATTACH_PATH . '/avatar/' . Windid::getUserDir($uid) . '/';
		foreach ($_avatar as $des => $org) {
			$toPath = $fileDir . $uid . $des;
			$fromPath = $defaultBanDir . $type . $org;
			WindidUpload::createFolder(dirname($toPath));
			WindidUpload::copyFile($fromPath, $toPath);
		}
		return true;
	}
}
?>