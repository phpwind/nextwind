<?php
defined('WEKIT_VERSION') || exit('Forbidden');

Wind::import('SRC:library.engine.hook.PwBaseHookInjector');
Wind::import('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardRecordService');
Wind::import('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardThreadListService');
Wind::import('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardAddPostService');
Wind::import('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardService');

/**
 * 回帖奖励 注入服务
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */

class App_ReplyReward_ReplyRewardInjector extends PwBaseHookInjector {

	/**
 	 *
	 * 注入器处理回帖奖励--发帖页展示
	 */
	public function run() {
		return new App_ReplyReward_ReplyRewardService($this->bp);
	}

	/**
	 *
	 *注入器处理回帖奖励--帖子提交
	 */
	public function doadd(){
		$reward = $this->getInput('replyReward','post');
		$service = new App_ReplyReward_ReplyRewardService($this->bp);
		$service->setRewardInput($reward);
		return $service;
	}


	/**
	 *注入器处理回帖奖励 -- 编辑帖子
	 *
	 */
	public function getUpdateHtmlContent(){
		$tid = $this->getInput('tid');
		return new App_ReplyReward_ReplyRewardService($this->bp,$tid);
	}


	/**
	 *
	 *注入器处理回帖奖励--中奖
	 */
	public function reward(){
		return new App_ReplyReward_ReplyRewardAddPostService($this->bp);
	}

	/**
	 *
	 *注入器处理帖子列表页展示 
	 */
	public function getThreadListHtmlContent(){
		return new App_ReplyReward_ReplyRewardThreadListService();
	}

	/**
	 *注入器处理帖子阅读页
	 */
	public function getThreadReadFloorHtmlContent(){
		return new App_ReplyReward_ReplyRewardRecordService();
	}


}