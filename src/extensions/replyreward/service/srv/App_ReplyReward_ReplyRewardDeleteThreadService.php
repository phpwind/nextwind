<?php
defined('WEKIT_VERSION') || exit('Forbidden');
Wind::import('LIB:process.iPwGleanDoHookProcess');
/**
 * 回帖奖励--帖子删除
 * 
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */

class App_ReplyReward_ReplyRewardDeleteThreadService extends iPwGleanDoHookProcess{


    /**
     *钩子执行方法 删除帖子
     */
    public function run($tids){  
        if(empty($tids)) return false;
        if(!is_array($tids)){
            $tids = array($tids);
        }

        $tids = array_map("intval", $tids);
        $this->_getReplyRewardDs()->batchDeleteByTids($tids);
        $this->_getReplyRewardRecordDs()->batchDeleteByTids($tids);
        return true;
    }


    private function _getReplyRewardRecordDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyRewardRecord');
    }
    
    private function _getReplyRewardDs(){
        return Wekit::load('EXT:replyreward.service.App_ReplyReward_ReplyReward');
    }
    
}