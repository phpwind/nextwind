<?php

Wind::import('WINDID:service.message.dm.WindidMessageDm');
/**
 * 私信业务
 *
 * @author peihong <peihong.zhangph@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageService.php 21452 2012-12-07 10:18:33Z gao.wanggao $
 * @package windid.service.message.srv
 */
class WindidMessageService {
	
	private $_blackList = array();
	/**
	 * 
	 * 发送私信
	 * @param string $username
	 * @param string $content
	 * @param int $fromUid
	 */
	public function sendMessage($username,$content,$fromUid){
		$userInfo = $this->_getUserDs()->getUserByName($username);
		if (!$userInfo) return new WindidError(WindidError::USER_NOT_EXISTS);
		return $this->sendMessageByUid($userInfo['uid'], $content,$fromUid);
	}
	
	/**
	 * 按用户ID发送私信
	 * 
	 * @param int $uid
	 * @param string $content
	 * @param int $fromUid
	 * @return WindidError|boolean
	 */
	public function sendMessageByUid($uid,$content,$fromUid = 0){
		if (!$uid || !$fromUid) return false; 
		if (!isset($this->_blackList[$uid])) {
			$this->_blackList[$uid] = $this->_getUserBlackDs()->getBlacklist($uid);
		}
		
		//生成新消息
		$dm = new WindidMessageDm();
		$dm->setCreatedUserId($fromUid)->setContent($content);
		if (($result = $dm->beforeAdd()) instanceof WindidError) return $result;
		$messageId = $this->_getMessageDs()->addMessage($dm);
		$lastMessage = $this->_getLastMessage($fromUid,$uid, $content);
		
		//=========================发件人对话信息=========================
		$dm = new WindidMessageDm();
		$dm->setLastMessage($lastMessage);
		
		$dialog = $this->_getMessageDs()->getDialogByUid($fromUid,$uid);
		if ($dialog) {
			$dialogId = $dialog['dialog_id'];
			$dm->increaseMessageCount()->setModifiedTime(Windid::getTime());
			$this->_getMessageDs()->updateDialog($dialogId,$dm);
		} else {
			$dm->setToUid($fromUid)
				->setFromUid($uid)
				->setMessageCount(1);
			$dialogId = $this->_getMessageDs()->addDialog($dm);
		}
		
		//添加发件人联系
		$dm = new WindidMessageDm();
		$dm->setDialogId($dialogId)->setMessageId($messageId);
		$this->_getMessageDs()->addRelation($dm);
			
		$dm = new WindidMessageDm();
		$dm->setLastMessage($lastMessage);
		if (in_array($fromUid, $this->_blackList[$uid])) {
			return false;
		}
		
		//=========================收件人对话信息=========================
		$dialog = $this->_getMessageDs()->getDialogByUid($uid,$fromUid);
		// 分组已存在更新数量
		if ($dialog) {
			$dialogId = $dialog['dialog_id'];
			$dm->increaseUnreadCount()->increaseMessageCount()->setModifiedTime(Windid::getTime());
			$this->_getMessageDs()->updateDialog($dialogId,$dm);
		} else { 
			// 分组不存在添加一条
			$dm->setToUid($uid)
				->setFromUid($fromUid)
				->setUnreadCount(1)
				->setMessageCount(1);
			//新增私信分组记录
			$dialogId = $this->_getMessageDs()->addDialog($dm);
		}
		
		//添加收件人联系
		$dm = new WindidMessageDm();
		$dm->setDialogId($dialogId)->setMessageId($messageId);
		$this->_getMessageDs()->addRelation($dm);
		
		return true;
	}
	
	/**
	 * 按用户名群发送私信
	 * 
	 * @param array $usernames
	 * @param content $content
	 * @param int $from_uid
	 * @return PwError|boolean
	 */
	public function sendMessageByUsernames($usernames,$content,$from_uid = 0){
		$userInfos = $this->_getUserDs()->fetchUserByName($usernames);
		if (!$userInfos) {
			return new WindidError(WindidError::USER_NOT_EXISTS);
		}
		foreach ($userInfos as $userInfo) {
			$this->sendMessageByUid($userInfo['uid'],$content,$from_uid);
		}
		return true;
	}
	
	/**
	 * 根据uids群发消息
	 * 
	 * @param array $uids
	 * @param string $content
	 * @param int $from_uid
	 * @return PwError|boolean
	 */
	public function sendMessageByUids($uids,$content,$from_uid = 0){
		if (!$content) {
			return new WindidError(WindidError::CONTENT_LENGTH_ERROR);
		}
		
		$userInfos = $this->_getUserDs()->fetchUserByUid($uids);
		if (!$userInfos) {
			return new WindidError(WindidError::USER_NOT_EXISTS);
		}
		foreach ($userInfos as $userInfo) {
			$this->sendMessageByUid($userInfo['uid'],$content,$from_uid);
		}
		return true;
	}
	
	
	public function deleteMessage($uid,$dialogId, $messageIds) {
		$ds = $this->_getMessageDs();
		$ds->batchDeleteByDialogAndMessages($dialogId, $messageIds);
		$this->resetDialogMessages($dialogId);
		$this->resetUserMessages($uid);
		return true;
	}


	public function deleteByMessageIds($messageIds){
		$relations = $this->_getMessageDs()->getRelationsByMessageIds($messageIds);
		if (!$relations) return true;
		$dialogIds = $relationIds = array();
		foreach ($relations as $v) {
			$dialogIds[] = $v['dialog_id'];
			$relationIds[$v['dialog_id']][] = $v['id'];
		}
		if (!$dialogIds) return true;
		$dialogIds = array_unique($dialogIds);
		foreach ($dialogIds as $dialogId) {
			$dialog = $this->_getMessageDs()->getDialog($dialogId);
			if (!$dialog || !$relationIds[$dialogId]) continue;
			$this->_getMessageDs()->batchDeleteRelation($relationIds[$dialogId]);
			$this->resetDialogMessages($dialogId);
			$this->resetUserMessages($dialog['to_uid']);
			$dialog_relation = $this->_getMessageDs()->getDialogMessages($dialogId,1);
			if (!$dialog_relation) {
				$this->_getMessageDs()->batchDeleteDialog(array($dialogId));
			}
		}
		$this->_getMessageDs()->batchDeleteMessage($messageIds);
		return true;
	}
	
	public function batchDeleteDialog($uid, $dialogIds) {
		$this->_getMessageDs()->batchDeleteRelationByDialogIds($dialogIds);
		$this->_getMessageDs()->batchDeleteDialog($dialogIds);
		$this->resetUserMessages($uid);
		return true;
	}
	
	public function deleteUserMessages($uid){
		$dialogIds = $this->_getMessageDs()->getDialogIds($uid);
		$dialogIds && $this->batchDeleteDialog($dialogIds);
		return true;
	}
	
	/*
	public function deleteDialog($dialogId) {
		$this->_getMessageDs()->batchDeleteRelationByDialogIds($dialogId);
		$this->_getMessageDs()->batchDeleteDialog($dialogId);
	}
	
	public function batchDeleteDialog($dialogIds){
		if (!is_array($dialogIds) || !$dialogIds) return false;
		$data = array();
		$dialogs = $this->_getMessageDs()->fetchDialogByDialogIds($dialogIds);
		foreach ($dialogs as $dialog) {
			$uid = $dialog['to_uid'];
			$data[$uid]['dialogIds'][] = $dialog['dialog_id'];
		}
		if (!$data) return true;
		foreach ($data as $uid => $v) {
			if (!$v['dialogIds']) continue;
			$this->_getMessageDs()->batchDeleteRelationByDialogIds($v['dialogIds']);
			$this->_getMessageDs()->batchDeleteDialog($v['dialogIds']);
		}
		return true;
	}*/
	
	/**
	 * 
	 * 重新统计某会话的统计数
	 * @param int $dialogId
	 */
	public function resetDialogMessages($dialogId){
		list($messages,$reads) = $this->_getMessageDs()->countDialogMessages($dialogId);
		$unreads = $reads > $messages ? 0 : $messages - $reads;
		if ($messages < 1) return $this->_getMessageDs()->deleteDialog($dialogId);
		
		$dm = new WindidMessageDm();
		$dm->setMessageCount($messages);
		$dm->setUnreadCount($unreads);
		return $this->_getMessageDs()->updateDialog($dialogId, $dm);
	}
	
	/**
	 * 
	 * 重新计算用户私信数
	 * @param int $uid
	 */
	public function resetUserMessages($uid){
		$uid = intval($uid);
		if ($uid < 1) return false;
		list($total,$unreads) = $this->_getMessageDs()->countUserMessages($uid);
		return $this->updateUser($uid, $unreads);
	}
	
	protected function updateUser($uid, $unreads) {
		$uid = intval($uid);
		if ($uid < 1) return false;
		Wind::import('WINDID:service.user.dm.WindidUserDm');
		$dm = new WindidUserDm($uid);
		$dm->setMessageCount($unreads);
		return $this->_getUserDs()->editUser($dm);
	}
	
	/**
	 * 
	 * 组装最近一条消息的信息
	 * @param int $fromUid
	 * @param int $toUid
	 * @param string $messageContent
	 */
	private function _getLastMessage($fromUid,$toUid,$messageContent){
		$lastMessage = array();
		$userInfos = $this->_getUserDs()->fetchUserByUid(array($fromUid,$toUid));
		if (!$userInfos) return $lastMessage;
		$lastMessage['from_uid'] = $fromUid;
		$lastMessage['from_username'] = $userInfos[$fromUid]['username'];
		$lastMessage['to_uid'] = $toUid;
		$lastMessage['to_username'] = $userInfos[$toUid]['username'];
		$lastMessage['content'] = $messageContent;
		return $lastMessage;
	}
	
	/**
	 * 
	 * @return WindidMessage
	 */
	private function _getMessageDs(){
		return Windid::load('message.WindidMessage');
	}
	
	/**
	 * 
	 * @return WindidUser
	 */
	private function _getUserDs(){
		return Windid::load('user.WindidUser');
	}
	
	private function _getUserBlackDs(){
		return Windid::load('user.WindidUserBlack');
	}
	

}