<?php
/**
 * the last known user to change this file in the repository  <$LastChangedBy: gao.wanggao $>
 * @author $Author: gao.wanggao $ Foxsee@aliyun.com
 * @copyright ?2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: WindidMessageApi.php 23072 2013-01-06 02:12:11Z gao.wanggao $ 
 * @package 
 */

class WindidMessageApi {
	
	public function getUnRead($uid) {
		$params = array(
			'uid'=>$uid
		);
		return WindidApi::open('message/getNum', $params);
	}
	
	/**
	 * 获取用户未读消息条数
	 * Enter description here ...
	 * @param unknown_type $uid
	 */
	/*public function getMessageNum($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('user/getMessageNum', $params);
	}*/
	
	/**
	 * 更新消息数
	 * Enter description here ...
	 * @param int $uid
	 * @param int $num
	 */
	public function editMessageNum($uid, $num) {
		$params = array(
			'uid'=>$uid,
			'num'=>$num,
		);
		return WindidApi::open('message/editNum', array(), $params);
	}
	
	public function send($uids,$content,$fromUid = 0) {
		$params = array(
			'uids'=>implode('_', $uids),
			'content'=>$content,
			'fromUid'=>$fromUid,
		);
		return WindidApi::open('message/send', array(),$params);
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
		$params = array(
			'uid'=>$uid,
			'dialogId'=>$dialogId,
			'messageIds'=>implode('_', $messageIds),
		);
		return WindidApi::open('message/read', array(),$params);
	}
	
	public function readDialog($dialogIds) {
		$params = array(
			'dialogIds'=>implode('_', $dialogIds),
		);
		return WindidApi::open('message/readDialog', array(),$params);
	}
	
	public function delete($uid, $dialogId, $messageIds = '') {
		$params = array(
			'uid'=>$uid,
			'dialogId'=>$dialogId,
			'messageIds'=>implode('_', $messageIds),
		);
		return WindidApi::open('message/delete', array(),$params);
	}
	
	public function batchDeleteDialog($uid, $dialogIds) {
		$params = array(
			'uid'=>$uid,
			'dialogIds'=>implode('_', $dialogIds),
		);
		return WindidApi::open('message/batchDeleteDialog', array(),$params);
	}

	
	public function countMessage($dialogId) {
		$params = array(
			'dialogId'=>$dialogId,
		);
		return WindidApi::open('message/countMessage', $params);
	}
	
	public function getMessageList($dialogId, $start,$limit) {
		$params = array(
			'dialogId'=>$dialogId,
			'start'=>$start,
			'limit'=>$limit,
		);
		return WindidApi::open('message/getMessageList', $params);
	}
	
	public function getDialog($dialogId) {
		$params = array(
			'dialogId'=>$dialogId,
		);
		return WindidApi::open('message/getDialog', $params);
	}
	
	public function fetchDialog($dialogIds) {
		$params = array(
			'dialogIds'=>implode('_', $dialogIds),
		);
		return WindidApi::open('message/fetchDialog', $params);
	}
	
	public function getDialogByUser($uid, $dialogUid) {
		$params = array(
			'uid'=>$uid,
			'dialogUid'=>$dialogUid,
		);
		return WindidApi::open('message/getDialogByUser', $params);
	}
	
	public function getDialogByUsers($uid, $dialogUids) {
		$params = array(
			'uid'=>$uid,
			'dialogUids'=>implode('_', $dialogUids),
		);
		return WindidApi::open('message/getDialogByUsers', $params);
	}
	
	public function getDialogList($uid, $start,$limit) {
		$params = array(
			'uid'=>$uid,
			'start'=>$start,
			'limit'=>$limit,
		);
		return WindidApi::open('message/getDialogList', $params);
	}
	
	public function countDialog($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('message/countDialog', $params);
	}
	
	public function getUnreadDialogsByUid($uid, $limit) {
		$params = array(
			'uid'=>$uid,
			'limit'=>$limit,
		);
		return WindidApi::open('message/getUnreadDialogsByUid', $params);
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
		$params = array(
			'start'=>$start,
			'limit'=>$limit,
		);
		$array = array('fromuid', 'keyword', 'username', 'starttime', 'endtime');
		foreach ($search AS $k=>$v) {
			if (!in_array($k, $array)) continue;
			$params[$k] = $v;
		}
		
		return WindidApi::open('message/searchMessage', $params);
	}
	
	public function getMessageById($messageId) {
		$params = array(
			'messageId'=>$messageId,
		);
		return WindidApi::open('message/getMessageById', $params);
	}
	
	public function deleteByMessageIds($messageIds) {
		$params = array(
			'messageIds'=>implode('_', $messageIds),
		);
		return WindidApi::open('message/deleteByMessageIds', array(), $params);
	}
	
	public function deleteUserMessages($uid) {
		$params = array(
			'uid'=>$uid,
		);
		return WindidApi::open('message/deleteUserMessages', array(), $params);
	}
	
	//传统收件箱，发件箱接口start
	
	/**
	 * 发件箱
	 * @return array
	 */
	public function fromBox($fromUid, $start = 0, $limit = 10) {
		$params = array(
			'uid'=>$fromUid,
			'start'=>$start,
			'limit'=>$limit
		);
		return WindidApi::open('message/fromBox', $params);
	}
	
	/**
	 * 收件箱
	 * @return array
	 */
	public function toBox($toUid, $start = 0, $limit = 10) {
		$params = array(
			'uid'=>$uid,
			'start'=>$start,
			'limit'=>$limit
		);
		return WindidApi::open('message/toBox', $params);
	}
	
	public function readMessages($uid, $messageIds) {
		$params = array(
			'uid'=>$uid,
			'messageIds'=>$messageIds,
		);
		return WindidApi::open('message/readMessages', array(), $params);
	}
	
	public function deleteMessages($uid, $messageIds) {
		$params = array(
			'uid'=>$uid,
			'messageIds'=>$messageIds,
		);
		return WindidApi::open('message/deleteMessages', array(), $params);
	}
	
	//传统收件箱，发件箱接口end

}
?>