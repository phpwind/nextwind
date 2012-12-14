<?php
Wind::import('APPS:windid.api.OpenBaseController');
Wind::import('WINDID:api.local.WindidMessageApi');
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: MessageController.php 21722 2012-12-12 09:28:56Z gao.wanggao $ 
 * @package 
 */

class MessageController extends OpenBaseController {
	
	public function getNumAction() {
		$result = $this->getApi()->getUnRead($this->getInput('uid', 'get'));
		$this->output($result);
	}
	
	public function sendAction() {
		$uids = explode('_', $this->getInput('uids', 'post'));
		$content = $this->getInput('content', 'post');
		$fromUid = $this->getInput('fromUid', 'post');
		$result = $this->getApi()->send($uids,$content,$fromUid);
		$this->output($result);
	}
	
	public function readAction() {
		$messageIds = explode('_', $this->getInput('messageIds', 'post'));
		$dialogId = $this->getInput('dialogId', 'post');
		$uid = $this->getInput('uid', 'post');
		$result = $this->getApi()->read($uid, $dialogId, $messageIds);
		$this->output($result);
	}
	
	public function readDialogAction() {
		$result = $this->getApi()->readDialog(explode('_', $this->getInput('dialogIds', 'post')));
		$this->output($result);
	}
	
	public function deleteAction() {
		$messageIds = explode('_', $this->getInput('messageIds', 'post'));
		$dialogId = $this->getInput('dialogId', 'post');
		$uid = $this->getInput('uid', 'post');
		$result = $this->getApi()->delete($uid, $dialogId, $messageIds);
		$this->output($result);
	}
	
	public function batchDeleteDialogAction() {
		$dialogIds = explode('_', $this->getInput('dialogIds', 'post'));
		$uid = $this->getInput('uid', 'post');
		$result = $this->getApi()->batchDeleteDialog($uid, $dialogIds);
		$this->output($result);
	}

	
	public function countMessageAction() {
		$result = $this->getApi()->countMessage($this->getInput('dialogId', 'get'));
		$this->output($result);
	}
	
	public function getMessageListAction() {
		$dialogId = $this->getInput('dialogId', 'get');
		$start = (int)$this->getInput('start', 'get');
		$limit = $this->getInput('limit', 'get');
		!$limit && $limit = 10;
		$result = $this->getApi()->getMessageList($dialogId, $start,$limit);
		$this->output($result);
	}
	
	public function getDialogAction() {
		$result = $this->_getMessageDs()->getDialog($this->getInput('dialogId', 'get'));
		$this->output($result);
	}
	
	public function fetchDialogAction() {
		$result = $this->_getMessageDs()->fetchDialog(explode('_', $this->getInput('dialogIds', 'get')));
		$this->output($result);
	}
	
	public function getDialogByUserAction() {
		$uid = $this->getInput('uid', 'get');
		$dialogUid = $this->getInput('dialogUid', 'get');
		$result = $this->_getMessageDs()->getDialogByUid($uid, $dialogUid);
		$this->output($result);
	}
	
	public function getDialogByUsersAction() {
		$uid = $this->getInput('uid', 'get');
		$dialogUids = explode('_', $this->getInput('dialogUids', 'get'));
		$result = $this->_getMessageDs()->getDialogByUids($uid, $dialogUids);
		$this->output($result);
	}
	
	public function getDialogListAction() {
		$uid = $this->getInput('uid', 'get');
		$start = (int)$this->getInput('start', 'get');
		$limit = (int)$this->getInput('limit', 'get');
		!$limit && $limit = 10;
		$result = $this->_getMessageDs()->getDialogs($uid,$start,$limit);
		$this->output($result);
	}
	
	public function countDialogAction() {
		$uid = (int)$this->getInput('uid', 'get');
		$result = $this->_getMessageDs()->countDialogs($uid);
		$this->output($result);
	}
	
	/**
	 * 搜索消息
	 * 
	 * @return array(count, list)
	 */
	public function searchMessageAction() {
		$start = (int)$this->getInput('start' , 'get');
		$limit = (int)$this->getInput('limit', 'get');
		$search['fromuid'] = $this->getInput('fromuid', 'get');
		$search['keyword'] = $this->getInput('keyword', 'get');
		$search['username'] = $this->getInput('username', 'get');
		$search['starttime'] = $this->getInput('starttime', 'get');
		$search['endtime']= $this->getInput('endtime', 'get');
		!$limit && $limit = 10;
		$result = $this->getApi()->searchMessage($search, $start, $limit);
		$this->output($result);
	}
	
	public function getMessageByIdAction() {
		$result = $this->_getMessageDs()->getMessageById($this->getInput('messageId', 'get'));
		$this->output($result);
	}
	
	public function deleteByMessageIdsAction() {
		$result = (int)$this->_getMessageService()->deleteByMessageIds(explode('_', $this->getInput('messageIds', 'post')));
		$this->output($result);
	}
	
	public function deleteUserMessagesAction() {
		$uid = (int)$this->getInput('uid');
		$result = (int)$this->_getMessageService()->deleteUserMessages($uid);
		$this->_getNotifyClient()->send('editMessageNum', $uid);
		$this->output($result);
	}
	
	public function editNumAction() {
		$uid = (int)$this->getInput('uid');
		$num = (int)$this->getInput('num');
		$result = $this->getApi()->editMessageNum($uid, $num);
		$this->_getNotifyClient()->send('editMessageNum', $uid);

		$this->output($result);
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
	
	private function _getNotifyClient() {
		return Windid::load('notify.srv.WindidNotifyClient');
	}
}
?>