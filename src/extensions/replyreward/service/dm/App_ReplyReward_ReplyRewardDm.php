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
class App_ReplyReward_ReplyRewardDm extends PwBaseDm {
    
	protected $_data = array();
	public $tid;
	/**
	*
	*添加前预处理
	*/
	public function _beforeAdd(){
		if(empty($this->_data['tid'])) return false;
		if(empty($this->_data['credittype'])) return false;
		if(empty($this->_data['creditnum'])) return false;
		if(empty($this->_data['rewardtimes'])) return false;
		if(empty($this->_data['repeattimes'])) return false;
		if(empty($this->_data['chance'])) return false;
		if(empty($this->_data['lefttimes'])) return false;
		return true;
	}

	/**
	*
	*更新前预处理
	*/
	public function _beforeUpdate(){
		if(empty($this->tid)) return false;
		if($this->_data['lefttimes'] < 0) return false;
		return true;
	}

	/**
	 *帖子id
	 */
	public function setTid($tid){
		$this->_data['tid'] = intval($tid);
		return $this;
	}

	/**
	 *积分类型
	 */
	public function setCreditType($creditType){
		$this->_data['credittype'] = intval($creditType);
		return $this;
	}

	/**
	 *每次奖励数量
	 */
	public function setCreditNum($creditNum){
		$this->_data['creditnum'] = intval(abs($creditNum));
		return $this;
	}

	/**
	 *累计奖励次数
	 */
	public function setRewardTimes($rewardTimes){
		$this->_data['rewardtimes'] = intval(abs($rewardTimes));
		return $this;
	}

	/**
	 *每人最多奖励次数
	 */
	public function setRepeatTimes($repeatTimes){
		$this->_data['repeattimes'] = intval($repeatTimes);
		return $this;
	}

	/**
	 *中奖几率
	 */
	public function setChance($chance){
		$this->_data['chance'] = intval($chance);
		return $this;
	}

	/**
	 *剩余次数
	 */
	public function setLeftTimes($leftTimes){
		$this->_data['lefttimes'] = intval(abs($leftTimes));
		return $this;
	}

    
}