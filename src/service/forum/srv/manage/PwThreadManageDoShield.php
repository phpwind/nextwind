<?php

Wind::import('SRV:forum.srv.manage.PwThreadManageDo');

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
 * @version $Id: PwThreadManageDoShield.php 14354 2012-07-19 10:36:06Z jieyin $
 * @package forum
 */

class PwThreadManageDoShield extends PwThreadManageDo {
	
	protected $tids;
	protected $pids;
	protected $ifShield;
	protected $threads = array('t' => array(), 'p' => array());

	public function check($permission) {
		return (isset($permission['shield']) && $permission['shield']) ? true : false;
	}

	public function gleanData($value) {
		if ($value['pid']) {
			$this->pids[] = $value['pid'];
			$this->threads['p'][] = $value;
		} else {
			$this->tids[] = $value['tid'];
			$this->threads['t'][] = $value;
		}
	}
	
	public function run() {
		if (1 == $this->ifShield) {
			$type = 'shield';
		} else {
			$type = 'unshield';
		}
		if ($this->pids) {
			Wind::import('SRV:forum.dm.PwReplyDm');
			$topicDm = new PwReplyDm(true);
			$topicDm->setIfshield($this->ifShield);
			$this->_getThreadDs()->batchUpdatePost($this->pids, $topicDm);
			
			//回复的屏蔽处理：被站内置顶的回复也会在屏蔽范围内，排除
			if (!$this->tids) {
				Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, $type, $this->threads['p'], $this->_reason, '' , true);
			}
		}
		
		if ($this->tids) {
			Wind::import('SRV:forum.dm.PwTopicDm');
			$topicDm = new PwTopicDm(true);
			$topicDm->setIfshield($this->ifShield);
			$this->_getThreadDs()->batchUpdateThread($this->tids, $topicDm, PwThread::FETCH_MAIN);
			//帖子的屏蔽处理
			Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, $type, $this->threads['t'], $this->_reason);
		}
	}

	public function setIfShield($ifShield) {
		$this->ifShield = intval($ifShield);
		return $this;
	}
	
	/**
	 * Enter description here ...
	 *
	 * @return PwThread
	 */
	public function _getThreadDs() {
		return Wekit::load('forum.PwThread');
	}
	
}