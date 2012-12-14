<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 回帖奖励日志设置获取
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardLogService {

	protected $configKey = 'app_replyreward';
    
    /**
	 *
	 *获取积分日志格式
	 */
    public function getCreditOperationConfig($config){
    	if(!is_array($config) || empty($config)) return false;
    	$config[$this->configKey] = $this->getReplyRewardLogBaseConfig();
    	return $config;
    }

    public function getConfigKey(){
        return $this->configKey;
    }

    private function getReplyRewardLogBaseConfig(){
        return array('回帖奖励', 'bbs', '{$username}在版块{$forumname}{$action}"回帖奖励"{$type};积分变化【{$cname}:{$affect}】', true);
    }

}