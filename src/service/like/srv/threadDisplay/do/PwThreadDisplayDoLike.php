<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');
Wind::import('SRV:like.srv.PwLikeService');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwThreadDisplayDoLike.php 20454 2012-10-30 04:08:41Z gao.wanggao $ 
 * @package 
 */
class PwThreadDisplayDoLike extends PwThreadDisplayDoBase {
	
	public function bulidRead($read) { 
		$info = array();
		if (!$read['pid']) {
			$info = Wekit::load('like.PwLikeContent')->getInfoByTypeidFromid(PwLikeContent::THREAD, $read['tid']);
		}
		if ($read['pid'] == 0 && $info['users']){
			$uids =  explode(',', $info['users']);
			if (count($uids) > 10 ) $uids = array_slice($uids, 0, 10);
			$users = Wekit::load('user.PwUser')->fetchUserByUid($uids);
			foreach ($uids AS $uid) {
				if (!$uid) continue;
				$read['lastLikeUsers'][$uid]['uid'] = $uid;
				$read['lastLikeUsers'][$uid]['username'] = $users[$uid]['username'];
				$read['lastLikeUsers'][$uid]['avatar'] = Pw::getAvatar($uid);
			}
		}
		return $read;
	}
}
?>