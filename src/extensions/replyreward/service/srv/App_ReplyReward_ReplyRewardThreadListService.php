<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('SRV:forum.srv.threadList.do.PwThreadListDoBase');

/**
 * 回帖奖励--帖子列表页
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */
class App_ReplyReward_ReplyRewardThreadListService extends PwThreadListDoBase{

    protected $tid;
    protected $creditPoolInfo = array();

    public function getCreditPoolNum(){
        return $this->creditPoolInfo[$this->tid];
    }

    /**
     * 钩子 初始化数据
     */
    public function initData($threaddb){
        if(!is_array($threaddb) || empty($threaddb)) return false;
        foreach($threaddb as $v){
            $tids[] = intval($v['tid']);
        }

        foreach($this->_getReplyRewardDs()->fetchRewardByTids($tids) as $val){
            $this->creditPoolInfo[$val['tid']] = intval($val['lefttimes'] * $val['creditnum']);
        }
    }

    /**
     *
     *钩子执行方法
     */
    
    public function createHtmlAfterSubject($thread){
        if(!is_array($thread)) return false;
        $this->tid = intval($thread['tid']);

        if($this->creditPoolInfo[$this->tid])
            PwHook::template('displayThreadListReplyRewardHtml', 'EXT:replyreward.template.threadlist_replyreward', true, $this);
    }

    private function _getReplyRewardDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyReward');
    }

    private function _getReplyRewardService(){
        return Wekit::load('EXT:replyreward.service.srv.App_ReplyReward_ReplyRewardService');
    }
    
}