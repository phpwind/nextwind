<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('LIB:base.PwBaseDm');

/**
 * 回复奖励数据模型
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardRecordDm extends PwBaseDm {
    
    protected $_data = array();
    
    
    public function _beforeAdd(){
		if(empty($this->_data['tid'])) return false;
		if(empty($this->_data['pid'])) return false;
		if(empty($this->_data['uid'])) return false;
		if(empty($this->_data['credittype'])) return false;
		if(empty($this->_data['creditnum'])) return false;
		if(empty($this->_data['rewardtime'])) return false;
		return true;
    }
    
    public function _beforeUpdate(){
        
    }
    

	public function setTid($tid){
		$this->_data['tid'] = intval($tid);
		return $this;
	}

	public function setPid($pid){
		$this->_data['pid'] = intval($pid);
		return $this;
	}

	public function setUid($uid){
		$this->_data['uid'] = intval($uid);
		return $this;
	}

	public function setCreditType($creditType){
		$this->_data['credittype'] = intval($creditType);
		return $this;
	}

	public function setCreditNum($creditnum){
		$this->_data['creditnum'] = intval($creditnum);
		return $this;
	}

	public function setRewardTime($rewardTime){
		$this->_data['rewardtime'] = intval($rewardTime);
		return $this;
	}

}