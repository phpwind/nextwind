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
 * @version $Id: PwThreadManageDoDown.php 21170 2012-11-29 12:05:09Z xiaoxia.xuxx $
 * @package forum
 */

class PwThreadManageDoDown extends PwThreadManageDo {
	
	public $downtime;
	public $downed;

	protected $tids;

	public function check($permission) {
		return (isset($permission['down']) && $permission['down']) ? true : false;
	}
	
	public function setDowntime($time) {
		$this->downtime = abs(intval($time)) * 3600;
		return $this;
	}

	public function setDowned($bool) {
		$this->downed = $bool;
		return $this;
	}

	public function gleanData($value) {
		$this->tids[] = $value['tid'];
	}
	
	public function run() {
		$dm = new PwTopicDm(true);
		$dm->addLastposttime(-$this->downtime)->setDowned($this->downed);
		Wekit::load('forum.PwThread')->batchUpdateThread($this->tids, $dm, PwThread::FETCH_MAIN);
		
		Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, 'down', $this->srv->getData(), $this->_reason, $this->downtime);
	}
}