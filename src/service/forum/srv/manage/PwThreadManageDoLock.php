<?php

Wind::import('SRV:forum.srv.manage.PwThreadManageDo');
Wind::import('SRV:forum.dm.PwTopicDm');

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
 * @version $Id: PwThreadManageDoLock.php 21170 2012-11-29 12:05:09Z xiaoxia.xuxx $
 * @package forum
 */

class PwThreadManageDoLock extends PwThreadManageDo {
	
	public $locked;

	protected $tids;

	public function check($permission) {
		return (isset($permission['lock']) && $permission['lock']) ? true : false;
	}
	
	public function setLocked($locked) {
		$this->locked = $locked;
		return $this;
	}

	public function gleanData($value) {
		$this->tids[] = $value['tid'];
	}
	
	public function run() {
		$dm = new PwTopicDm(true);
		$type = '';
		if ($this->locked == 2) {
			$dm->setClosed(1)->setLocked(0);
			$type = 'closed';
		} elseif ($this->locked == 1) {
			$dm->setClosed(0)->setLocked(1);
			$type = 'lock';
		} else {
			$dm->setClosed(0)->setLocked(0);
			$type = 'unlock';
		}
		Wekit::load('forum.PwThread')->batchUpdateThread($this->tids, $dm, PwThread::FETCH_MAIN);
		
		Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, $type, $this->srv->getData(), $this->_reason);
	}
}