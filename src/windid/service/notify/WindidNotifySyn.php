<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidNotifySyn.php 21452 2012-12-07 10:18:33Z gao.wanggao $ 
 * @package 
 */

class WindidNotifySyn {
	
	public function getNotify($id) {
		$id = (int)$id;
		return $this->_getDao()->get($id);
	}


	public function addNotify($appid, $uid, $syntype = 1, $createdtime = 0) {
		 $data['appid'] = intval($appid);
		 $data['uid'] = intval($uid);
		 $data['syntype'] = intval($syntype);
		 $data['createdtime'] = intval($createdtime);
		return $this->_getDao()->add($data);
	}

	
	public function deleteNotify($id) {
		$id= (int)$id;
		return $this->_getDao()->delete($id);
	}

	public function deleteByTime($createdtime) {
		return $this->_getDao()->deleteByTime($createdtime);
	}

	private function _getDao() {
		return Windid::loadDao('notify.dao.WindidNotifyDao');
	}
}
?>