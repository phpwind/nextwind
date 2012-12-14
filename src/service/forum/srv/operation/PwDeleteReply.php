<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('LIB:process.PwGleanDoProcess');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoVirtualDelete');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoDirectDelete');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoFreshDelete');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoAttachDelete');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoPostUpdate');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoForumUpdate');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoUserUpdate');
Wind::import('HOOK:PwDeleteReply.PwDeleteReplyDoToppedDelete');
//Wind::import('HOOK:PwDeleteTopic.PwDeleteTopicDoFreshDelete');
//Wind::import('HOOK:PwDeleteTopic.PwDeleteTopicDoSpecialDelete');

/**
 * 删除帖子及其关联操作(扩展)
 *
 * @author Jianmin Chen <sky_hold@163.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwDeleteReply.php 17853 2012-09-10 05:59:46Z jinlong.panjl $
 * @package forum
 */

class PwDeleteReply extends PwGleanDoProcess {
	
	public $data = array();
	public $pids = array();
	public $user;

	public $isRecycle = false;
	public $isDeductCredit = false;
	public $isDeleteFresh = true;
	public $reason;
	
	public function __construct(iPwDataSource $ds, PwUserBo $user) {
		$this->data = $ds->getData();
		$this->user = $user;
		parent::__construct();
	}
	
	/**
	 * setting - 是否删除到回收站
	 *
	 * @param bool $recycle 是否删除到回收站
	 * @return object $this
	 */
	public function setRecycle($recycle) {
		$this->isRecycle = $recycle;
		return $this;
	}
	
	/**
	 * setting - 是否扣除积分
	 *
	 * @param bool $isDeductCredit 是否扣除积分
	 * @return object $this
	 */
	public function setIsDeductCredit($isDeductCredit) {
		$this->isDeductCredit = $isDeductCredit;
		return $this;
	}

	/**
	 * setting - 是否同步删除新鲜事
	 *
	 * @param bool $isDeleteFresh 是否同步删除新鲜事
	 * @return object $this
	 */
	public function setIsDeleteFresh($isDeleteFresh) {
		$this->isDeleteFresh = $isDeleteFresh;
		return $this;
	}

	/**
	 * setting - 删除理由
	 *
	 * @param string $reason
	 * @return object $this
	 */
	public function setReason($reason) {
		$this->reason = $reason;
		return $this;
	}

	public function getData() {
		return $this->data;
	}

	protected function init() {
		if ($this->isRecycle) {
			$this->appendDo(new PwDeleteReplyDoVirtualDelete($this));
		} else {
			$this->appendDo(new PwDeleteReplyDoDirectDelete($this));
		}
		
		$this->appendDo(new PwDeleteReplyDoUserUpdate($this));
		if ($this->isDeleteFresh) {
			$this->appendDo(new PwDeleteReplyDoFreshDelete($this));
		}
		$this->appendDo(new PwDeleteReplyDoAttachDelete($this));
		$this->appendDo(new PwDeleteReplyDoPostUpdate($this));
		$this->appendDo(new PwDeleteReplyDoForumUpdate($this));
		$this->appendDo(new PwDeleteReplyDoToppedDelete($this));
		//$this->appendDo(new PwDeleteTopicDoFreshDelete($this));
		//$this->appendDo(new PwDeleteTopicDoSpecialDelete($this));
	}

	protected function gleanData($value) {
		$this->pids[] = $value['pid'];
	}

	public function getIds() {
		return $this->pids;
	}

	protected function run() {
		return true;
	}
}