<?php

Wind::import('SRV:forum.srv.manage.PwThreadManageDo');
Wind::import('SRV:forum.dm.PwPostsToppedDm');

/**
 * 帖子发布流程
 *
 * -> 1.check 检查帖子发布运行环境
 * -> 2.appendDo(*) 增加帖子发布时的行为动作,例:投票、附件等(可选)
 * -> 3.execute 发布
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwThreadManageDoTopped.php 14442 2012-07-20 09:10:11Z jieyin $
 * @package forum
 */

class PwThreadManageDoToppedReply extends PwThreadManageDo {
	
	public $topped = 0;
	protected $pid;
	protected $lou;
	protected $tid;
	
	public function check($permission) {
		return (isset($permission['toppedreply']) && $permission['toppedreply']) ? true : false;
	}
	
	public function setTopped($topped) {
		$this->topped = intval($topped); 
	}
	
	public function setLou($lou) {
		$this->lou = $lou;
		return $this;
	}
	
	public function gleanData($value) {
		$this->pid = $value['pid'];
		$this->tid = $value['tid'];
	}
	
	public function run() {
		Wind::import('SRV:forum.dm.PwReplyDm');
		Wind::import('SRV:forum.dm.PwTopicDm');
		$replyDm = new PwReplyDm($this->pid);
		$replyDm->setTopped($this->topped);
		$this->_getThreadDs()->updatePost($replyDm);
		$topicDm = new PwTopicDm($this->tid);
		if ($this->topped) {
			$toppedDm = new PwPostsToppedDm();
			$toppedDm->setPid($this->pid)
					->setTid($this->tid)
					->setCreatedUserid($this->srv->user->uid)
					->setFloor($this->lou);
			$this->_getPostsToppedDs()->addTopped($toppedDm);
			$topicDm->addReplyTopped(1);
		} else {
			$topicDm->addReplyTopped(-1);
			$this->_getPostsToppedDs()->deleteTopped($this->pid);
		}
		$this->_getThreadDs()->updateThread($topicDm, PwThread::FETCH_MAIN);
		if ($this->topped == 1) {
			$type = 'threadtopped';
		} else {
			$type = 'untopped';
		}
		Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, $type, $this->srv->getData(), $this->_reason, '', true);
		return true;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return PwThread
	 */
	protected function _getThreadDs() {
		return Wekit::load('forum.PwThread');
	}
	
	/**
	 * PwPostsTopped
	 *
	 * @return PwPostsTopped
	 */
	protected function _getPostsToppedDs() {
		return Wekit::load('forum.PwPostsTopped');
	}
}