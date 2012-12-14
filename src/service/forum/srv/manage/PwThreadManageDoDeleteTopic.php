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
 * @version $Id: PwThreadManageDoDeleteTopic.php 21170 2012-11-29 12:05:09Z xiaoxia.xuxx $
 * @package forum
 */

class PwThreadManageDoDeleteTopic extends PwThreadManageDo {
	
	protected $tids;
	protected $isDeductCredit = true;

	public function check($permission) {
		return (isset($permission['delete']) && $permission['delete']) ? true : false;
	}

	public function gleanData($value) {
		$this->tids[] = $value['tid'];
	}
	
	public function run() {
		if ($this->tids) {
			Wind::import('SRV:forum.srv.operation.PwDeleteTopic');
			Wind::import('SRV:forum.srv.dataSource.PwFetchTopicByTid');
			$service2 = new PwDeleteTopic(new PwFetchTopicByTid($this->tids), $this->srv->user);
			$service2->setRecycle(true)
				->setIsDeductCredit($this->isDeductCredit)
				->setReason($this->_reason)
				->execute();
			
			Wekit::load('log.srv.PwLogService')->addThreadManageLog($this->srv->user, 'delete', $this->srv->getData(), $this->_reason);
		}
	}

	public function setIsDeductCredit($isDeductCredit) {
		$this->isDeductCredit = $isDeductCredit;
		return $this;
	}
}