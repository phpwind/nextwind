<?php
defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 回复奖励DS基础服务
 *
 * @author Feng Xiao <xiao.fengx@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 * @version $Id$
 * @package replyreward
 */

 class App_ReplyReward_ReplyReward {

     /**
      * 根据tid获取回帖奖励信息
      * @param type $tid
      * @return boolean
      */
    public function getRewardByTid($tid) {
        $tid = (int) $tid;
        if($tid < 1) return false;
        return $this->_getReplyRewardDao()->getRewardByTid($tid);
    }

    public function fetchRewardByTids($tids) {
        if(!is_array($tids) || empty($tids)) return false;
        $tids = array_map("intval", $tids);
        return $this->_getReplyRewardDao()->fetchRewardByTids($tids);
    }

    
    public function updateByTid(App_ReplyReward_ReplyRewardDm $data){
        if (($result = $data->_beforeUpdate ()) !== true)
            return false;
        return $this->_getReplyRewardDao()->updateByTid($data->tid,$data->getData());
    }


    public function addReward(App_ReplyReward_ReplyRewardDm $data){
        if (($result = $data->beforeAdd ()) !== true)
            return false;
        return $this->_getReplyRewardDao()->addReward($data->getData());
    }
    
    
    public function deleteByTid($tid){
        $tid = intval($tid);
        if($tid < 1) return false;
        return $this->_getReplyRewardDao()->deleteByTid($tid);
    }

    public function batchDeleteByTids($tids){
        if(!is_array($tids) || empty($tids)) return false;
        return $this->_getReplyRewardDao()->batchDeleteByTids($tids);
    } 
    
    protected function _getReplyRewardDao() {
        return Wekit::loadDao('EXT:replyreward.service.dao.App_ReplyReward_ReplyRewardDao');
    }
    

 }
