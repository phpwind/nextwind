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
 * @version $Id: PwThreadManageDoUp.php 21170 2012-11-29 12:05:09Z xiaoxia.xuxx $
 * @package forum
 */

class PwThreadManageDoUp extends PwThreadManageDo {
	
	public $uptime;
	protected $tids;

	public function check($permission) {
		if (!isset($permission['up']) || !$permission['up']) {
			return false;
		}
		if ($permission['up_time'] > 0 && ($this->uptime-Pw::getTime()) > $permission['up_time'] * 3600) {
			return new PwError('BBS:manage.operate.up.uptime.exceed', array('{uptime}' => $permission['up_time']));
		}
		return true;
	}
	
	public function setUptime($uptime) {
		if ($uptime) {
			$this->uptime = Pw::getTime() + intval($uptime) * 3600;
		} else {
			$this->uptime = Pw::getTime();
		}
		return $this;
	}

	public function gleanData($value) {
		$this->tids[] = $value['tid'];
	}
	
	public function run() {
		$topicDm = new PwTopicDm(true);
		$topicDm->setLastposttime($this->uptime);
		Wekit::load('forum.PwThread')->batchUpdateThread($this->tids, $topicDm, PwThread::FETCH_MAIN);
		//管理日志添加
		Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, 'up', $this->srv->getData(), $this->_reason, $this->uptime);
	}
}