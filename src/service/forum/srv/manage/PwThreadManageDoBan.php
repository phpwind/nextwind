<?php
Wind::import('SRV:forum.srv.manage.PwThreadManageDo');

/**
 * 帖子-禁止
 *
 * @author xiaoxia.xu <xiaoxia.xuxx@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 * @version $Id: PwThreadManageDoBan.php 21170 2012-11-29 12:05:09Z xiaoxia.xuxx $
 * @package src.service.forum.srv.manage
 */
class PwThreadManageDoBan extends PwThreadManageDo {
	
	protected $fid;
	protected $tids;
	protected $delete = array();
	protected $users = array();
	protected $banInfo = array();
	protected $selectBanUids = array();
	
	/**
	 * 获得用户权限
	 * 0：没权限
	 * 1：全局
	 * 2：本版
	 * 
	 * @var int
	 */
	private $right = 0;
	
	/**
	 * 当前登录用户的Bo
	 * 
	 * @var PwUserBo
	 */
	private $loginUser = null;
	
	/**
	 * 构造方法
	 *
	 * @param PwThreadManage $srv
	 * @param PwUserBo $bo
	 */
	public function __construct(PwThreadManage $srv, PwUserBo $bo) {
		parent::__construct($srv);
		$this->loginUser = $bo;
	}

	/* (non-PHPdoc)
	 * @see PwThreadManageDo::check()
	 */
	public function check($permission) {
		return (isset($permission['ban']) && $permission['ban']) ? true : false;
	}

	/* (non-PHPdoc)
	 * @see PwThreadManageDo::gleanData()
	 */
	public function gleanData($value) {
		$this->tids[] = $value['tid'];
		$this->fid = $value['fid'];
		$this->selectBanUids[] = $value['created_userid'];
	}
	
	/* (non-PHPdoc)
	 * @see PwThreadManageDo::run()
	 */
	public function run() {
		list($banDmList, $_notice) = $this->_buildBanDm();
		/* @var $service PwUserBanService */
		$service = Wekit::load('user.srv.PwUserBanService');
		$r = $service->banUser($banDmList);
		if ($r instanceof PwError) return $r;
		if ($this->banInfo->sendNotice) {
			$service->sendNotice($_notice);
		}
		$this->_delThreads();
		Wekit::load('log.srv.PwLogService')->addBanUserLog($this->loginUser, $this->banInfo->uids, $this->banInfo->types, $this->banInfo->reason, $this->banInfo->end_time);
		return true;
	}

	/**
	 * 获得帖子的发表者
	 * 
	 * @return array
	 */
	public function getThreadUsername() {
		foreach ($this->srv->getData() as $key => $value) {
			$this->users[$value['created_userid']] = $value['created_username'];
		}
		return $this->users;
	}
	
	/**
	 * 判断是否有权限
	 * 删除全站或是本版帖子
	 * 
	 * @return int
	 */
	public function getRight() {
		if ($this->right) return $this->right;
		if ($this->loginUser->getPermission('operate_thread', false, array())) {
			$this->right = 1;
		} else {
			$this->right = 2;
		}
		return $this->right;
	}
	
	/**
	 * 设置用户禁止DM
	 *
	 * @param array $dmList
	 * @return PwThreadManageDoBan
	 */
	public function setBanInfo($banInfo) {
		$this->banInfo = $banInfo;
		return $this;
	}
	
	/**
	 * 删除操作
	 *
	 * @param array $deletes
	 * @return PwThreadManageDoBan
	 */
	public function setDeletes($deletes) {
		$this->delete = $deletes;
		return $this;	
	}
	
	/**
	 * 构建禁止的对象
	 * 
	 * @return array
	 */
	private function _buildBanDm() {
		Wind::import('SRV:user.dm.PwUserBanInfoDm');
		Wind::import('SRV:user.PwUserBan');
		$rightTypes = array(PwUserBan::BAN_AVATAR, PwUserBan::BAN_SIGN, PwUserBan::BAN_SPEAK);
		
		if ($this->banInfo->end_time > 0) $this->banInfo->end_time = Pw::str2time($this->banInfo->end_time);
		$data = $_notice = array();
		foreach ($this->banInfo->types as $type) {
			if (!in_array($type, $rightTypes)) continue;
			foreach ($this->banInfo->uids as $uid) {
				if (!$uid) continue;
				$dm = new PwUserBanInfoDm();
				$dm->setUid($uid)
					->setBanAllAccount($this->banInfo->ban_others)
					->setCreateTime(Pw::getTime())
					->setCreatedUid($this->loginUser->uid)
					->setOperator($this->loginUser->username)
					->setEndTime(intval($this->banInfo->end_time))
					->setTypeid($type)
					->setReason($this->banInfo->reason)
					->setFid(0);
				$data[] = $dm;
				
				isset($_notice[$uid]) || $_notice[$uid] = array();
				$_notice[$uid]['end_time'] = $this->banInfo->end_time;
				$_notice[$uid]['reason'] = $this->banInfo->reason;
				$_notice[$uid]['type'][] = $type;
				$_notice[$uid]['operator'] = $this->loginUser->username;
				
			}
		}
		return array($data, $_notice);
	}
	
	/**
	 * 删除帖子
	 *
	 * @param array $param
	 * @return boolean
	 */
	private function _delThreads() {
		Wind::import('SRV:forum.srv.operation.PwDeleteTopic');
		
		//【用户禁止帖子删除】
		//删除当前主题帖子  当禁止非楼主时，不能删除当前主题
		if (1 == $this->delete['current'] && !array_diff($this->selectBanUids, $this->banInfo->uids)) {
			Wind::import('SRV:forum.srv.dataSource.PwFetchTopicByTid');
			//【用户禁止帖子删除】-根据帖子ID列表删除帖子到回收站
			$service = new PwDeleteTopic(new PwFetchTopicByTid($this->tids), $this->loginUser);
			$service->setRecycle(true)->setIsDeductCredit(true)->execute();
		}
		if (1 == $this->delete['all'] && $this->getRight() == 1) {
			Wind::import('SRV:forum.srv.dataSource.PwFetchTopicByUid');
			//【用户禁止帖子删除】-并且按照用户ID列表删除帖子到回收站
			$service = new PwDeleteTopic(new PwFetchTopicByUid($this->selectBanUids), $this->loginUser);
			$service->setRecycle(true)->setIsDeductCredit(true)->execute();
		} elseif (1 == $this->delete['forum'] && $this->getRight() == 2) {
			Wind::import('SRV:forum.srv.dataSource.PwFetchTopicByFidAndUids');
			//【用户禁止帖子删除】-并且按照用户ID列表+版块ID删除帖子到回收站
			$service = new PwDeleteTopic(new PwFetchTopicByFidAndUids($this->fid, $this->selectBanUids), $this->loginUser);
			$service->setRecycle(true)->setIsDeductCredit(true)->execute();
		}
		return true;
	}
}