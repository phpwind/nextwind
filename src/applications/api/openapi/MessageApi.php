<?php
Wind::import('WINDID:api.open.OpenBaseApi');
Wind::import('WINDID:api.local.WindidMessageApi');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: MessageApi.php 21657 2012-12-12 06:59:25Z gao.wanggao $ 
 * @package 
 */

class MessageApi extends OpenBaseApi {
	
	public function getUnRead() {
		return $this->getApi()->getUnRead($this->getInput('uid'));
	}
	
	public function send() {
		$uids = explode('_', $this->getInput('uids'));
		$content = $this->getInput('content');
		$fromUid = $this->getInput('fromUid');
		return $this->getApi()->send($uids,$content,$fromUid);
	}
	
	public function read() {
		$messageIds = explode('_', $this->getInput('messageIds'));
		$dialogId = $this->getInput('dialogId');
		$uid = $this->getInput('uid');
		return $this->getApi()->read($uid, $dialogId, $messageIds);
	}
	
	public function readDialog() {
		return $this->getApi()->readDialog(explode('_', $this->getInput('dialogIds')));
	}
	
	public function delete() {
		$messageIds = explode('_', $this->getInput('messageIds'));
		$dialogId = $this->getInput('dialogId');
		$uid = $this->getInput('uid');
		return $this->getApi()->delete($uid, $dialogId, $messageIds);
	}
	
	public function batchDeleteDialog() {
		$dialogIds = explode('_', $this->getInput('dialogIds'));
		$uid = $this->getInput('uid');
		return $this->getApi()->batchDeleteDialog($uid, $dialogIds);
	}

	
	public function countMessage() {
		return $this->getApi()->countMessage($this->getInput('dialogId'));
	}
	
	public function getMessageList() {
		$dialogId = $this->getInput('dialogId');
		$start = $this->getInput('start');
		$limit = $this->getInput('limit');
		return $this->getApi()->getMessageList($dialogId, $start,$limit);
	}
	
	public function getDialog() {
		return $this->_getMessageDs()->getDialog($this->getInput('dialogId'));
	}
	
	public function fetchDialog() {
		return $this->_getMessageDs()->fetchDialog(explode('_', $this->getInput('dialogIds')));
	}
	
	public function getDialogByUser() {
		$uid = $this->getInput('uid');
		$dialogUid = $this->getInput('dialogUid');
		return $this->_getMessageDs()->getDialogByUid($uid, $dialogUid);
	}
	
	public function getDialogByUsers() {
		$uid = $this->getInput('uid');
		$dialogUids = explode('_', $this->getInput('dialogUids'));
		return $this->_getMessageDs()->getDialogByUids($uid, $dialogUids);
	}
	
	public function getDialogList() {
		$uid = $this->getInput('uid');
		$start = $this->getInput('start');
		$limit = $this->getInput('limit');
		return $this->_getMessageDs()->getDialogs($uid,$start,$limit);
	}
	
	public function countDialog() {
		$uid = $this->getInput('uid');
		return $this->_getMessageDs()->countDialogs($uid);
	}
	
	/**
	 * 搜索消息
	 * 
	 * @return array(count, list)
	 */
	public function searchMessage() {
		$start = $this->getInput('start');
		$limit = $this->getInput('limit');
		$search['fromuid'] = $this->getInput('fromuid');
		$search['keyword'] = $this->getInput('keyword');
		$search['username'] = $this->getInput('username');
		$search['starttime'] = $this->getInput('starttime');
		$search['endtime']= $this->getInput('endtime');
		return $this->getApi()->searchMessage($search, $start, $limit);
	}
	
	public function getMessageById() {
		return $this->_getMessageDs()->getMessageById($this->getInput('messageId'));
	}
	
	public function deleteByMessageIds() {
		return (int)$this->_getMessageService()->deleteByMessageIds(explode('_', $this->getInput('messageIds')));
	}
	
	public function deleteUserMessages() {
		return (int)$this->_getMessageService()->deleteUserMessages($this->getInput('uid'));
	}
	
	private function _getMessageDs() {
		return Windid::load('message.WindidMessage');
	}
	
	private function _getMessageService() {
		return Windid::load('message.srv.WindidMessageService');
	}
	
	protected function getApi() {
		return new WindidMessageApi();
	}
}
?>