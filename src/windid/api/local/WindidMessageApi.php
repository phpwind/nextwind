<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageApi.php 23444 2013-01-09 11:48:37Z gao.wanggao $ 
 * @package 
 */

class WindidMessageApi {
	
	public function getUnRead($uid) {
		$data = $this->_getUserDs()->getUserByUid($uid, WindidUser::FETCH_DATA);
		return intval($data['messages']);
	}
	
	/**
	 * 更新消息数
	 * Enter description here ...
	 * @param int $uid
	 * @param int $num
	 */
	public function editMessageNum($uid, $num) {
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		$dm = new WindidUserDm($uid);
		$dm->addMessages($num);
		$result = $this->_getUserDs()->editUser($dm);
		if ($result instanceof WindidError) return $result->getCode();
		$this->_getNotifyClient()->send('editMessageNum', $uid);
		return (int)$result;
	}
	
	public function send($uids,$content,$fromUid = 0) {
		if (!is_array($uids)) $uids = array($uids);
		$result = $this->_getMessageService()->sendMessageByUids($uids,$content,$fromUid);
		if ($result instanceof WindidError) return $result->getCode();
		$srv = $this->_getNotifyClient();
		foreach ($uids AS $uid) {
			$srv->send('editMessageNum', $uid);
		}
		return (int)$result;
	}
	
	/**
	 * 标记已读
	 * Enter description here ...
	 * @param unknown_type $uid
	 * @param unknown_type $dialogId
	 * @param unknown_type $messageIds
	 * @return 标记成功的条数    
	 */
	public function read($uid, $dialogId, $messageIds = array()) {
		$dialog = $this->_getMessageDs()->getDialog($dialogId);
		if (!$dialog || $dialog['to_uid'] != $uid) return 0;
		if ($messageIds) {
			$result = $this->_getMessageDs()->readMessages($dialogId,$messageIds);
		} else {
			$result =  $this->_getMessageDs()->readDialogMessages($dialogId);
		}
		$this->_getMessageService()->resetDialogMessages($dialogId);
		$this->_getMessageService()->resetUserMessages($uid);
		$this->_getNotifyClient()->send('editMessageNum', $uid);
		return $result;
	}
	
	public function readDialog($dialogIds) {
		if (!is_array($dialogIds)) $dialogIds = array($dialogIds);
		Wind::import('WINDID:service.message.dm.WindidMessageDm');
		$ds = $this->_getMessageDs();
		foreach ($dialogIds AS $id) {
			$dialog = $ds->getDialog($id);
			$ds->readDialogMessages($id);
			$dm = new WindidMessageDm();
			$dm->setUnreadCount(0);
			$ds->updateDialog($id, $dm);
			$this->_getMessageService()->resetUserMessages($dialog['to_uid']);
			$this->_getNotifyClient()->send('editMessageNum', $dialog['to_uid']);
		}
		return WindidError::SUCCESS;
	}
	
	public function delete($uid, $dialogId, $messageIds = '') {
		if (!is_array($messageIds)) $messageIds = array($messageIds);
		$dialog = $this->_getMessageDs()->getDialog($dialogId);
		if (!$dialog || $dialog['to_uid'] != $uid) return WindidError::FAIL;
		$result = $this->_getMessageService()->deleteMessage($uid, $dialogId, $messageIds);
		$this->_getNotifyClient()->send('editMessageNum', $uid);
		return (int)$result;
	}
	
	public function batchDeleteDialog($uid, $dialogIds) {
		$result =$this->_getMessageService()->batchDeleteDialog($uid,$dialogIds);
		$this->_getNotifyClient()->send('editMessageNum', $uid);
		return (int)$result;
	}

	
	public function countMessage($dialogId) {
		return $this->_getMessageDs()->countRelation($dialogId);
	}
	
	public function getMessageList($dialogId, $start,$limit) {
		return $this->_getMessageDs()->getDialogMessages($dialogId,$limit,$start);
	}
	
	public function getDialog($dialogId) {
		return $this->_getMessageDs()->getDialog($dialogId);
	}
	
	public function fetchDialog($dialogIds) {
		return $this->_getMessageDs()->fetchDialog($dialogIds);
	}
	
	public function getDialogByUser($uid, $dialogUid) {
		return $this->_getMessageDs()->getDialogByUid($uid, $dialogUid);
	}
	
	public function getDialogByUsers($uid, $dialogUids) {
		return $this->_getMessageDs()->getDialogByUids($uid, $dialogUids);
	}
	
	public function getDialogList($uid, $start,$limit) {
		return $this->_getMessageDs()->getDialogs($uid,$start,$limit);
	}
	
	public function countDialog($uid) {
		return $this->_getMessageDs()->countDialogs($uid);
	}
	
	public function getUnreadDialogsByUid($uid, $limit) {
		return $this->_getMessageDs()->getUnreadDialogsByUid($uid, $limit);
	}
	
	/**
	 * 搜索消息
	 * 
	 * @param array $search array('fromuid', 'keyword', 'username', 'starttime', 'endtime')
	 * @param int $start
	 * @param int $limit
	 * @return array(count, list)
	 */
	public function searchMessage($search, $start = 0, $limit = 10) {
		if (!is_array($search)) return array(0, array());
		$array = array('fromuid', 'keyword', 'username', 'starttime', 'endtime');
		Wind::import('WINDID:service.message.srv.vo.WindidMessageSo');
		$vo = new WindidMessageSo();
		foreach ($search AS $k=>$v) {
			if (!in_array($k, $array)) continue;
			if ($k == 'username') {
				$user = $this->_getUserDs()->getUserByName($v);
				if (!$user['uid']) $user['uid'] = 0;
				$vo->setFromUid($user['uid']);
			}
			$method = 'set'.ucfirst($k);
			$vo->$method($v);
		}
		$count = $this->_getMessageDs()->countMessage($vo);
		if ($count < 1) return array(0,array());
		$messages = $this->_getMessageDs()->searchMessage($vo, $start,$limit);
		$uids = $array = array();
		foreach ($messages as $v) {
			$uids[] = $v['from_uid'];
		}
		// 组装用户数据
		$userInfos = $this->_getUserDs()->fetchUserByUid($uids);
		if (!$userInfos) return array(0,array());
		foreach ($messages as $v) {
			$v['username'] = $userInfos[$v['from_uid']]['username'];
			$array[] = $v;
		}
		return array($count,$array);
	}
	
	public function getMessageById($messageId) {
		return $this->_getMessageDs()->getMessageById($messageId);
	}
	
	public function deleteByMessageIds($messageIds) {
		$result = $this->_getMessageService()->deleteByMessageIds($messageIds);
		return (int)$result;
	}
	
	
	public function deleteUserMessages($uid) {
		$result = $this->_getMessageService()->deleteUserMessages($uid);
		$this->_getNotifyClient()->send('editMessageNum', $uid);
		return (int)$result;
	}
	
	//传统收件箱，发件箱接口start
	
	/**
	 * 发件箱
	 * @return array
	 */
	public function fromBox($fromUid, $start = 0, $limit = 10) {
		return $this->_getBoxMessage()->fromBox($fromUid, $start, $limit);
	}
	
	/**
	 * 收件箱
	 * @return array
	 */
	public function toBox($toUid, $start = 0, $limit = 10) {
		return $this->_getBoxMessage()->toBox($toUid, $start, $limit);
	}
	
	public function readMessages($uid, $messageIds) {
		if (!is_array($messageIds)) $messageIds = array($messageIds);
		$result = $this->_getBoxMessage()->readMessages($uid, $messageIds);
		return (int)$result;
	}
	
	public function deleteMessages($uid, $messageIds) {
		if (!is_array($messageIds)) $messageIds = array($messageIds);
		$result = $this->_getBoxMessage()->deleteMessages($uid, $messageIds);
		return (int)$result;
	}
	
	//传统收件箱，发件箱接口end
	
	private function _getMessageDs() {
		return Windid::load('message.WindidMessage');
	}
	
	private function _getMessageService() {
		return Windid::load('message.srv.WindidMessageService');
	}
	
	private function _getBoxMessage() {
		return Windid::load('message.srv.WindidBoxMessage');
	}
	
	private function _getUserDs(){
		return Windid::load('user.WindidUser');
	}
	
	private function _getNotifyClient() {
		return Windid::load('notify.srv.WindidNotifyClient');
	}
}
?>